<?php

declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use InvalidArgumentException;
use RuntimeException;

/**
 * A simple yet powerful data validator
 *
 * @template TData of array<string, mixed>
 * @template TRules of array<string, string|array>
 * @template TMessages of array<string, string>
 */
class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    /** @var array<string, array{name: string, parameters: array<int, string>}> */
    private array $parsedRules = [];

    /** @var array<string, callable(mixed, string, array<int, string>, array<string, mixed>): bool|string> */
    private array $validationMethods = [];

    /** @var array<string, string> */
    private array $messages;

    private bool $validated = false;

    /**
     * @param TData $data The data to validate
     * @param array<string, string|array<string>> $validationRules The validation rules
     * @param array<string, string> $customMessages Custom error messages
     */
    public function __construct(
        private readonly array $data,
        array $validationRules,
        array $customMessages = []
    ) {
        $this->messages = array_merge($this->getDefaultMessages(), $customMessages);
        $this->parseValidationRules($validationRules);
        $this->registerBuiltInRules();
    }

    /**
     * Create a new validator instance
     *
     * @param TData $data
     * @param array<string, string|array<string>> $rules
     * @param array<string, string> $messages
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    /**
     * Validate and return data or throw exception
     *
     * @throws RuntimeException
     * @return array<string, mixed>
     */
    public function validate(): array
    {
        if (!$this->passes()) {
            throw new RuntimeException('Validation failed: ' . $this->getErrorsAsString());
        }

        return $this->validated();
    }

    /**
     * Check if validation passes
     */
    public function passes(): bool
    {
        if (!$this->validated) {
            $this->runValidation();
        }

        return empty($this->errors);
    }

    /**
     * Check if validation fails
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get validation errors
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        if (!$this->validated) {
            $this->runValidation();
        }

        return $this->errors;
    }

    /**
     * Get validated data
     *
     * @throws RuntimeException
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->fails()) {
            throw new RuntimeException('Cannot get validated data - validation failed');
        }

        $validated = [];
        foreach (array_keys($this->parsedRules) as $field) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    /**
     * Add a custom validation rule
     *
     * @param string $name
     * @param callable(mixed, string, array<int, string>, array<string, mixed>): bool|string $callback
     * @param string|null $message
     * @return Validator
     */
    public function addRule(string $name, callable $callback, ?string $message = null): self
    {
        $this->validationMethods[$name] = $callback;
        if ($message !== null) {
            $this->messages[$name] = $message;
        }
        return $this;
    }

    /**
     * Run the validation process
     */
    private function runValidation(): void
    {
        $this->errors = [];
        $this->validated = true;

        foreach ($this->parsedRules as $field => $rules) {
            $fieldValues = $this->getFieldValues($field);
            $isNullable = $this->hasRule($field, 'nullable');

            foreach ($fieldValues as $actualField => $value) {
                foreach ($rules as $rule) {
                    if ($this->shouldSkipValidation($value, $rule, $isNullable)) {
                        continue;
                    }

                    if (!isset($this->validationMethods[$rule['name']])) {
                        throw new InvalidArgumentException("Unknown validation rule: {$rule['name']}");
                    }

                    $result = $this->validationMethods[$rule['name']]($value, $actualField, $rule['parameters'], $this->data);

                    if ($result !== true) {
                        $this->errors[$actualField][] = $this->formatMessage($result, [
                            'field' => $actualField,
                            'parameters' => $rule['parameters']
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Register all built-in validation rules
     */
    private function registerBuiltInRules(): void
    {
        $this->addRule('nullable', fn($value) => true);

        $this->addRule('required', function ($value, $field) {
            return $this->isEmpty($value)
                ? $this->messages['required']
                : true;
        });

        $this->addRule('string', function ($value, $field) {
            return is_string($value)
                ? true
                : $this->messages['string'];
        });

        $this->addRule('numeric', function ($value, $field) {
            return is_numeric($value)
                ? true
                : $this->messages['numeric'];
        });

        $this->addRule('array', function ($value, $field) {
            return is_array($value)
                ? true
                : $this->messages['array'];
        });

        $this->addRule('boolean', function ($value, $field) {
            return (is_bool($value) || in_array($value, [1, 0, '1', '0'], true))
                ? true
                : $this->messages['boolean'];
        });

        $this->addRule('min', function ($value, $field, $params) {
            $min = (int)$params[0];

            if ($this->hasRule($field, 'string')) {
                $length = strlen((string)$value);
                return $length >= $min
                    ? true
                    : $this->messages['min.string'];
            }

            if (is_numeric($value)) {
                return $value >= $min
                    ? true
                    : $this->messages['min.numeric'];
            }

            $length = strlen((string)$value);
            return $length >= $min
                ? true
                : $this->messages['min.string'];
        });

        $this->addRule('max', function ($value, $field, $params) {
            $max = (int)$params[0];

            if ($this->hasRule($field, 'string')) {
                $length = strlen((string)$value);
                return $length <= $max
                    ? true
                    : $this->messages['max.string'];
            }

            if (is_numeric($value)) {
                return $value <= $max
                    ? true
                    : $this->messages['max.numeric'];
            }

            $length = strlen((string)$value);
            return $length <= $max
                ? true
                : $this->messages['max.string'];
        });

        $this->addRule('between', function ($value, $field, $params) {
            $min = (int)$params[0];
            $max = (int)$params[1];

            if ($this->hasRule($field, 'string')) {
                $length = strlen((string)$value);
                return ($length >= $min && $length <= $max)
                    ? true
                    : $this->messages['between.string'];
            }

            if (is_numeric($value)) {
                return ($value >= $min && $value <= $max)
                    ? true
                    : $this->messages['between.numeric'];
            }

            $length = strlen((string)$value);
            return ($length >= $min && $length <= $max)
                ? true
                : $this->messages['between.string'];
        });

        $this->addRule('in', function ($value, $field, $params) {
            return in_array($value, $params, true)
                ? true
                : $this->messages['in'];
        });

        $this->addRule('required_if', function ($value, $field, $params, $data) {
            $otherField = $params[0];
            $otherValue = $params[1];

            return (isset($data[$otherField])
                && $data[$otherField] == $otherValue
                && $this->isEmpty($value))
                ? $this->messages['required_if']
                : true;
        });

        $this->addRule('required_unless', function ($value, $field, $params, $data) {
            $otherField = $params[0];
            $otherValue = $params[1];

            return (!isset($data[$otherField])
                || $data[$otherField] != $otherValue)
            && $this->isEmpty($value)
                ? $this->messages['required_unless']
                : true;
        });
    }

    /**
     * Get the default validation messages
     *
     * @return array<string, string>
     */
    private function getDefaultMessages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'required_if' => 'The :attribute field is required when :other is :value.',
            'required_unless' => 'The :attribute field is required unless :other is :value.',
            'string' => 'The :attribute field must be a string.',
            'numeric' => 'The :attribute field must be a numeric value.',
            'array' => 'The :attribute field must be an array.',
            'boolean' => 'The :attribute field must be a boolean value.',
            'min.string' => 'The :attribute field must be at least :min characters.',
            'min.numeric' => 'The :attribute field must be at least :min.',
            'max.string' => 'The :attribute field must not exceed :max characters.',
            'max.numeric' => 'The :attribute field must not exceed :max.',
            'between.numeric' => 'The :attribute field must be between :min and :max.',
            'between.string' => 'The :attribute field must be between :min and :max characters.',
            'in' => 'The selected :attribute is invalid.',
        ];
    }

    /**
     * Parse validation rules into structured format
     *
     * @param array<string, string|array<string|array<string>>> $rules
     */
    private function parseValidationRules(array $rules): void
    {
        foreach ($rules as $field => $ruleSet) {
            $this->parsedRules[$field] = [];

            // Convert string rules to array format
            if (is_string($ruleSet)) {
                $ruleSet = explode('|', $ruleSet);
            }

            foreach ($ruleSet as $rule) {
                // Handle array rule format ['rule', 'rule:param', ['rule', 'param1', 'param2']]
                if (is_array($rule)) {
                    $name = $rule[0];
                    $parameters = array_slice($rule, 1);
                } else {
                    // Handle string rule format 'rule' or 'rule:param1,param2'
                    $segments = explode(':', $rule);
                    $name = $segments[0];
                    $parameters = isset($segments[1]) ? explode(',', $segments[1]) : [];
                }

                $this->parsedRules[$field][] = [
                    'name' => $name,
                    'parameters' => $parameters
                ];
            }
        }
    }

    /**
     * Check if validation should be skipped
     *
     * @param array{name: string, parameters: array<int, string>} $rule
     */
    private function shouldSkipValidation(mixed $value, array $rule, bool $isNullable): bool
    {
        return $rule['name'] !== 'required'
            && $this->isEmpty($value)
            && $isNullable;
    }

    /**
     * Check if field has a specific rule
     */
    private function hasRule(string $field, string $ruleName): bool
    {
        return isset($this->parsedRules[$field]) &&
            in_array($ruleName, array_column($this->parsedRules[$field], 'name'));
    }

    /**
     * Format an error message with replacements
     *
     * @param string $message
     * @param array{field: string, parameters: array<int, string>} $context
     * @return string
     */
    private function formatMessage(string $message, array $context): string
    {
        $replacements = [
            ':attribute' => $context['field'],
            ':min' => $context['parameters'][0] ?? '',
            ':max' => $context['parameters'][1] ?? $context['parameters'][0] ?? '',
            ':other' => $context['parameters'][0] ?? '',
            ':value' => $context['parameters'][1] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Get field values including array notation with wildcards
     *
     * @return array<string, mixed>
     */
    private function getFieldValues(string $field): array
    {
        if (!str_contains($field, '*')) {
            return [$field => $this->getNestedValue($this->data, $field)];
        }

        $results = [];
        $this->processWildcardField($this->data, explode('.', $field), '', $results);
        return $results;
    }

    /**
     * Recursively process wildcard fields
     *
     * @param array<string, mixed> $data
     * @param array<int, string> $segments
     * @param string $currentPath
     * @param array<string, mixed> $results
     */
    private function processWildcardField(array $data, array $segments, string $currentPath, array &$results): void
    {
        $segment = array_shift($segments);

        if ($segment === '*') {
            foreach ($data as $key => $value) {
                $newPath = $currentPath ? $currentPath . '.' . $key : $key;
                if (!empty($segments)) {
                    if (is_array($value)) {
                        $this->processWildcardField($value, $segments, $newPath, $results);
                    }
                } else {
                    $results[$newPath] = $value;
                }
            }
            return;
        }

        if (!isset($data[$segment])) {
            return;
        }

        $newPath = $currentPath ? $currentPath . '.' . $segment : $segment;

        if (empty($segments)) {
            $results[$newPath] = $data[$segment];
            return;
        }

        if (is_array($data[$segment])) {
            $this->processWildcardField($data[$segment], $segments, $newPath, $results);
        }
    }

    /**
     * Get a nested value using dot notation
     */
    private function getNestedValue(array $array, string $path): mixed
    {
        $value = $array;
        foreach (explode('.', $path) as $segment) {
            if (!isset($value[$segment])) {
                return null;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    /**
     * Check if a value is considered empty
     */
    private function isEmpty(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || (is_array($value) && empty($value));
    }

    /**
     * Get all errors as a single string
     */
    private function getErrorsAsString(): string
    {
        $messages = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $messages[] = "$field: " . implode(', ', $fieldErrors);
        }
        return implode('; ', $messages);
    }
}
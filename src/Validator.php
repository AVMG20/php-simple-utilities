<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Exception;
use InvalidArgumentException;

/**
 * Class Validator
 *
 * Provides validation functionality for array data against defined rules.
 *
 * @template TData of array<string, mixed>
 * @template TRules of array<string, string|array>
 * @template TMessages of array<string, string>
 */
class Validator
{
    /** @var array<string, array<int, string>> */
    private array $errors = [];

    private bool $hasRun = false;

    /** @var array<string, callable(mixed, string, array, array):bool|string> */
    private array $validationMethods = [];

    /** @var array<string, string> */
    private array $defaultMessages = [
        'required' => 'The :attribute field is required.',
        'required_if' => 'The :attribute field is required when :anotherField is :anotherValue.',
        'required_unless' => 'The :attribute field is required unless :anotherField is :anotherValue.',
        'string' => 'The :attribute field must be a string.',
        'numeric' => 'The :attribute field must be a numeric value.',
        'array' => 'The :attribute field must be an array.',
        'boolean' => 'The :attribute field must be a boolean value.',
        'min.string' => 'The :attribute field must be at least :min characters long.',
        'min.numeric' => 'The :attribute field must be at least :min.',
        'max.string' => 'The :attribute field must not exceed :max characters.',
        'max.numeric' => 'The :attribute field must not exceed :max.',
        'between.numeric' => 'The :attribute field must be between :min and :max.',
        'between.string' => 'The :attribute field must be between :min and :max characters.',
        'in' => 'The :attribute field must be one of the following values: :values.',
    ];

    /**
     * Constructor
     *
     * @param TData $data The data to validate
     * @param TRules $rules The validation rules
     * @param TMessages $messages Custom error messages
     */
    public function __construct(
        private array $data,
        private array $rules,
        private array $messages = []
    ) {
        $this->messages = array_merge($this->defaultMessages, $messages);
        $this->registerDefaultValidationMethods();
    }

    /**
     * Creates a new validator instance
     *
     * @param TData $data
     * @param TRules $rules
     * @param TMessages $messages
     * @return static
     */
    public static function make(array $data, array $rules, array $messages = []): static
    {
        return new static($data, $rules, $messages);
    }

    /**
     * Validates the data and returns validated fields or throws exception
     *
     * @throws Exception When validation fails
     * @return array<string, mixed> Validated data
     */
    public function validate(): array
    {
        if (!$this->passes()) {
            throw new Exception('Validation failed');
        }
        return $this->validated();
    }

    /**
     * Checks if validation passes
     *
     * @return bool
     */
    public function passes(): bool
    {
        $this->runValidation();
        return empty($this->errors);
    }

    /**
     * Checks if validation fails
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Returns validated data or throws exception
     *
     * @throws Exception When validation fails
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if (!$this->hasRun) {
            $this->validate();
        }

        if ($this->fails()) {
            throw new Exception('Validation failed');
        }

        $validated = [];
        foreach ($this->rules as $field => $rule) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    /**
     * Returns validation errors
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        $this->runValidation();
        return $this->errors;
    }

    /**
     * Runs validation if not already run
     */
    private function runValidation(): void
    {
        if ($this->hasRun) {
            return;
        }

        $this->errors = [];

        foreach ($this->rules as $field => $ruleSet) {
            $rules = $this->parseRules($ruleSet);
            $isNullable = $this->hasRule($rules, 'nullable');

            foreach ($rules as $rule) {
                $this->validateRule($field, $rule, $isNullable);
            }
        }

        $this->hasRun = true;
    }

    /**
     * Validates a single rule for a field
     *
     * @param string $field Field name
     * @param array{name: string, parameters: array<int, string>} $rule Rule configuration
     * @param bool $isNullable Whether field is nullable
     * @throws InvalidArgumentException When validation rule doesn't exist
     */
    private function validateRule(string $field, array $rule, bool $isNullable): void
    {
        if (!isset($this->validationMethods[$rule['name']])) {
            throw new InvalidArgumentException("Validation rule {$rule['name']} does not exist.");
        }

        $validationMethod = $this->validationMethods[$rule['name']];
        $fieldValues = $this->getFieldValues($field);

        foreach ($fieldValues as $key => $value) {
            if ($isNullable && $this->isEmpty($value) && $rule['name'] !== 'required') {
                continue;
            }

            /** @var string|boolean $result */
            $result = $validationMethod($value, $key, $rule['parameters'], $this->data);

            if ($result !== true) {
                $this->addError($key, $result);
            }
        }
    }

    /**
     * Adds an error message for a field
     *
     * @param string $field Field name
     * @param string $message Error message
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Parses rule string or array into structured format
     *
     * @param string|array<int|string, mixed> $ruleSet
     * @return array<int, array{name: string, parameters: array<int, string>}>
     */
    private function parseRules(string|array $ruleSet): array
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
        return array_map(function ($rule) {
            $parts = explode(':', $rule);
            return [
                'name' => $parts[0],
                'parameters' => isset($parts[1]) ? explode(',', $parts[1]) : []
            ];
        }, $rules);
    }

    /**
     * Registers all default validation methods
     */
    private function registerDefaultValidationMethods(): void
    {
        $this->validationMethods['nullable'] = fn($value, $field, $params, $data) => true;

        $this->validationMethods['required'] = function ($value, $field, $params, $data) {
            if ($this->isEmpty($value)) {
                return str_replace(':attribute', $field, $this->messages['required']);
            }
            return true;
        };

        $this->validationMethods['required_if'] = function ($value, $field, $params, $data) {
            [$anotherField, $anotherValue] = $params;
            if (isset($data[$anotherField]) && $data[$anotherField] == $anotherValue && $this->isEmpty($value)) {
                return str_replace(
                    [':attribute', ':anotherField', ':anotherValue'],
                    [$field, $anotherField, $anotherValue],
                    $this->messages['required_if']
                );
            }
            return true;
        };

        $this->validationMethods['required_unless'] = function ($value, $field, $params, $data) {
            [$anotherField, $anotherValue] = $params;
            if ((!isset($data[$anotherField]) || $data[$anotherField] != $anotherValue) && $this->isEmpty($value)) {
                return str_replace(
                    [':attribute', ':anotherField', ':anotherValue'],
                    [$field, $anotherField, $anotherValue],
                    $this->messages['required_unless']
                );
            }
            return true;
        };

        $this->validationMethods['string'] = function ($value, $field, $params, $data) {
            if (!is_string($value)) {
                return str_replace(':attribute', $field, $this->messages['string']);
            }
            return true;
        };

        $this->validationMethods['numeric'] = function ($value, $field, $params, $data) {
            if (!is_numeric($value)) {
                return str_replace(':attribute', $field, $this->messages['numeric']);
            }
            return true;
        };

        $this->validationMethods['array'] = function ($value, $field, $params, $data) {
            if (!is_array($value)) {
                return str_replace(':attribute', $field, $this->messages['array']);
            }
            return true;
        };

        $this->validationMethods['boolean'] = function ($value, $field, $params, $data) {
            if (!(is_bool($value) || in_array($value, [1, 0, '1', '0'], true))) {
                return str_replace(':attribute', $field, $this->messages['boolean']);
            }
            return true;
        };

        $this->validationMethods['min'] = function ($value, $field, $params, $data) {
            if ($this->isEmpty($value)) return true;

            $min = $params[0];
            $isNumeric = $this->hasNumericRule($field);
            $isString = $this->hasStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return $value >= $min ? true : str_replace(
                    [':attribute', ':min'],
                    [$field, $min],
                    $this->messages['min.numeric']
                );
            }

            $length = strlen((string)$value);
            return $length >= $min ? true : str_replace(
                [':attribute', ':min'],
                [$field, $min],
                $this->messages['min.string']
            );
        };

        $this->validationMethods['max'] = function ($value, $field, $params, $data) {
            if ($this->isEmpty($value)) return true;

            $max = $params[0];
            $isNumeric = $this->hasNumericRule($field);
            $isString = $this->hasStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return $value <= $max ? true : str_replace(
                    [':attribute', ':max'],
                    [$field, $max],
                    $this->messages['max.numeric']
                );
            }

            $length = strlen((string)$value);
            return $length <= $max ? true : str_replace(
                [':attribute', ':max'],
                [$field, $max],
                $this->messages['max.string']
            );
        };

        $this->validationMethods['between'] = function ($value, $field, $params, $data) {
            if ($this->isEmpty($value)) return true;

            [$min, $max] = $params;
            $isNumeric = $this->hasNumericRule($field);
            $isString = $this->hasStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return ($value >= $min && $value <= $max) ? true : str_replace(
                    [':attribute', ':min', ':max'],
                    [$field, $min, $max],
                    $this->messages['between.numeric']
                );
            }

            $length = strlen((string)$value);
            return ($length >= $min && $length <= $max) ? true : str_replace(
                [':attribute', ':min', ':max'],
                [$field, $min, $max],
                $this->messages['between.string']
            );
        };

        $this->validationMethods['in'] = function ($value, $field, $params, $data) {
            return in_array($value, $params, true) ? true : str_replace(
                [':attribute', ':values'],
                [$field, implode(', ', $params)],
                $this->messages['in']
            );
        };
    }
    /**
     * Checks if field has numeric rule
     *
     * @param string $field Field name
     * @return bool
     */
    private function hasNumericRule(string $field): bool
    {
        $rules = $this->parseRules($this->rules[$field] ?? '');
        return $this->hasRule($rules, 'numeric');
    }

    /**
     * Checks if field has string rule
     *
     * @param string $field Field name
     * @return bool
     */
    private function hasStringRule(string $field): bool
    {
        $rules = $this->parseRules($this->rules[$field] ?? '');
        return $this->hasRule($rules, 'string');
    }

    /**
     * Checks if rules contain specific rule name
     *
     * @param array<int, array{name: string, parameters: array<int, string>}> $rules
     * @param string $ruleName
     * @return bool
     */
    private function hasRule(array $rules, string $ruleName): bool
    {
        foreach ($rules as $rule) {
            if ($rule['name'] === $ruleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets field values including array notation
     *
     * @param string $field Field name with possible wildcard notation
     * @return array<string, mixed>
     */
    private function getFieldValues(string $field): array
    {
        if (!str_contains($field, '.*.')) {
            return [$field => $this->getNestedValue($this->data, $field)];
        }

        $fieldValues = [];
        $segments = explode('.*.', $field);
        $baseField = array_shift($segments);
        $remainingField = implode('.*.', $segments);
        $baseArray = $this->getNestedValue($this->data, $baseField);

        if (!is_array($baseArray)) {
            return [$field => null];
        }

        foreach ($baseArray as $key => $subArray) {
            $fieldKey = "{$baseField}.{$key}";
            if ($remainingField) {
                $nestedFieldValues = $this->getFieldValues("{$fieldKey}.{$remainingField}");
                $fieldValues = array_merge($fieldValues, $nestedFieldValues);
            } else {
                $fieldValues[$fieldKey] = $subArray;
            }
        }

        return $fieldValues;
    }

    /**
     * Gets value from nested array using dot notation
     *
     * @param array<string, mixed> $array
     * @param string $field Dot notation field path
     * @return mixed
     */
    private function getNestedValue(array $array, string $field): mixed
    {
        foreach (explode('.', $field) as $key) {
            if (!isset($array[$key])) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }

    /**
     * Checks if value is empty
     *
     * @param mixed $value
     * @return bool
     */
    private function isEmpty(mixed $value): bool
    {
        return is_null($value) || $value === '' || (is_array($value) && empty($value));
    }

    /**
     * Formats error message with replacements
     *
     * @param string $message
     * @param array{name: string, field: string, parameters: array<int, string>} $rule
     * @return string
     */
    private function formatMessage(string $message, array $rule): string
    {
        $replacements = [
            ':attribute' => $rule['field'],
            ':min' => $rule['parameters'][0] ?? '',
            ':max' => $rule['name'] === 'between' ? ($rule['parameters'][1] ?? '') : ($rule['parameters'][0] ?? ''),
            ':values' => implode(', ', $rule['parameters']),
            ':anotherField' => $rule['parameters'][0] ?? '',
            ':anotherValue' => $rule['parameters'][1] ?? ''
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Adds custom validation rule
     *
     * @param string $name Rule name
     * @param callable(mixed $value, string $field, array<int, string> $parameters, array<string, mixed> $data):bool|string $callback
     *        Callback function that returns true if validation passes, or error message string if fails.
     *        Parameters passed to callback:
     *        - mixed $value: The value being validated
     *        - string $field: Field name
     *        - array $parameters: Rule parameters
     *        - array $data: All data being validated
     * @param string|null $message Custom error message
     * @return static
    */
    public function addRule(string $name, callable $callback, ?string $message = null): self
    {
        $this->validationMethods[$name] = $callback;
        if ($message !== null) {
            $this->messages[$name] = $message;
        }
        return $this;
    }
}
<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Exception;
use InvalidArgumentException;

/**
 * Validator Class
 * @template TField as string
 * @template TValue as mixed
 *
 * This class provides a Laravel-style way to validate arrays of data against a set of rules.
 */
class Validator
{
    /**
     * @var array<TField, TValue> The data to validate.
     */
    private array $data;

    /**
     * @var array<TField, string> The rules to apply to the data.
     */
    private array $rules;

    /**
     * @var array<string, string> Custom error messages.
     */
    private array $messages = [
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
     * @var array<TField, array<int, string>> The validation errors.
     */
    private array $errors = [];

    /**
     * @var array<string, callable> Registered validation methods.
     */
    private array $validationMethods = [];

    /**
     * @var bool Whether validation has been run
     */
    private bool $hasRun = false;

    /**
     * Creates a new instance of the Validator class.
     *
     * @param array<TField, TValue> $data The data to validate.
     * @param array<TField, string> $rules The rules to apply to the data.
     * @param array<TField, string> $messages Custom error messages.
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = array_merge($this->messages, $messages);

        $this->registerDefaultValidationMethods();
    }

    /**
     * Creates a new instance of the Validator class.
     *
     * @param array<TField, TValue> $data The data to validate.
     * @param array<TField, string> $rules The rules to apply to the data.
     * @param array<TField, string> $messages Custom error messages.
     * @return static
     */
    public static function make(array $data, array $rules, array $messages = []): static
    {
        return new static($data, $rules, $messages);
    }

    /**
     * Validate the data and throw an exception if validation fails.
     *
     * @throws Exception
     * @return array The validated data
     */
    public function validate(): array
    {
        if (!$this->passes()) {
            throw new Exception('Validation failed');
        }

        return $this->validated();
    }

    /**
     * Determine if the validation passes.
     *
     * @return bool
     */
    public function passes(): bool
    {
        $this->runValidation();
        return empty($this->errors);
    }

    /**
     * Determine if the validation fails.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Get the validated data.
     *
     * @return array
     * @throws Exception if validation hasn't been run or failed
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
        foreach ($this->rules as $field => $rules) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    /**
     * Get the failed validation rules.
     *
     * @return array
     */
    public function failed(): array
    {
        $this->runValidation();

        $failed = [];
        foreach ($this->errors as $field => $messages) {
            $failed[$field] = array_map(function ($message) {
                // Extract rule name from message (this is a simple implementation)
                return strtolower(explode(' ', $message)[0]);
            }, $messages);
        }

        return $failed;
    }

    /**
     * Run the validator's rules.
     *
     * @return void
     */
    private function runValidation(): void
    {
        if ($this->hasRun) {
            return;
        }

        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }

            $isNullable = in_array('nullable', $rules, true);

            foreach ($rules as $rule) {
                $parameters = [];
                if (strpos($rule, ':')) {
                    [$rule, $parameterString] = explode(':', $rule);
                    $parameters = explode(',', $parameterString);
                }

                if (isset($this->validationMethods[$rule])) {
                    $validationMethod = $this->validationMethods[$rule];
                    $fieldValues = $this->getFieldValues($field);

                    foreach ($fieldValues as $key => $value) {
                        if ($isNullable && $this->isEmpty($value)) {
                            continue;
                        }

                        $result = $validationMethod($value, $key, ...$parameters);
                        if ($result !== true) {
                            $this->addError($key, $result);
                        }
                    }
                } else {
                    throw new InvalidArgumentException("Validation rule {$rule} does not exist.");
                }
            }
        }

        $this->hasRun = true;
    }

    /**
     * Add a custom validation rule.
     *
     * @param string $name
     * @param callable $callback
     * @param string|null $message
     * @return $this
     */
    public function addRule(string $name, callable $callback, ?string $message = null): self
    {
        $this->addValidationMethod($name, $callback);
        if ($message !== null) {
            $this->messages[$name] = $message;
        }
        return $this;
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        $this->runValidation();
        return $this->errors;
    }

    /**
     * Adds a custom validation method.
     * @template TParams as mixed
     * @template TResult as bool|string
     *
     * @param string $name The name of the validation method.
     * @param (callable(TField, TValue, TParams): TResult) $callback The validation method.
     * @return void
     */
    public function addValidationMethod(string $name, callable $callback): void
    {
        $this->validationMethods[$name] = $callback;
    }

    /**
     * Registers the default validation methods.
     *
     * @return void
     */
    private function registerDefaultValidationMethods(): void
    {
        $this->addValidationMethod('nullable', function ($value) {
            return true;
        });

        $this->addValidationMethod('required', function ($value, $field) {
            if ($this->isEmpty($value)) {
                return str_replace(':attribute', $field, $this->messages['required']);
            }
            return true;
        });

        $this->addValidationMethod('required_if', function ($value, $field, $anotherField, $anotherValue) {
            if ($this->data[$anotherField] == $anotherValue) {
                if ($this->isEmpty($value)) {
                    return str_replace([
                        ':attribute',
                        ':anotherField',
                        ':anotherValue'
                    ], [
                        $field,
                        $anotherField,
                        $anotherValue
                    ], $this->messages['required_if']);
                }
            }
            return true;
        });


        $this->addValidationMethod('required_unless', function ($value, $field, $anotherField, $anotherValue) {
            if ($this->data[$anotherField] != $anotherValue) {
                if ($this->isEmpty($value)) {
                    return str_replace([
                        ':attribute',
                        ':anotherField',
                        ':anotherValue'
                    ], [
                        $field,
                        $anotherField,
                        $anotherValue
                    ], $this->messages['required_unless']);
                }
            }
            return true;
        });

        $this->addValidationMethod('string', function ($value, $field) {
            return is_string($value) ? true : str_replace(':attribute', $field, $this->messages['string']);
        });

        $this->addValidationMethod('numeric', function ($value, $field) {
            return is_numeric($value) ? true : str_replace(':attribute', $field, $this->messages['numeric']);
        });

        $this->addValidationMethod('array', function ($value, $field) {
            return is_array($value) ? true : str_replace(':attribute', $field, $this->messages['array']);
        });

        $this->addValidationMethod('min', function ($value, $field, $min) {
            if ($this->isEmpty($value)) return true;

            $isNumeric = $this->isNumericRule($field);
            $isString = $this->isStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return $value >= $min
                    ? true
                    : str_replace([':attribute', ':min'], [$field, $min], $this->messages['min.numeric']);
            }

            $length = is_string($value) ? strlen($value) : strlen((string)$value);
            return $length >= $min
                ? true
                : str_replace([':attribute', ':min'], [$field, $min], $this->messages['min.string']);
        });

        $this->addValidationMethod('max', function ($value, $field, $max) {
            if ($this->isEmpty($value)) return true;

            $isNumeric = $this->isNumericRule($field);
            $isString = $this->isStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return $value <= $max
                    ? true
                    : str_replace([':attribute', ':max'], [$field, $max], $this->messages['max.numeric']);
            }

            $length = is_string($value) ? strlen($value) : strlen((string)$value);
            return $length <= $max
                ? true
                : str_replace([':attribute', ':max'], [$field, $max], $this->messages['max.string']);
        });

        $this->addValidationMethod('between', function ($value, $field, $min, $max) {
            if ($this->isEmpty($value)) return true;

            $isNumeric = $this->isNumericRule($field);
            $isString = $this->isStringRule($field);

            if ($isNumeric || (!$isString && is_numeric($value))) {
                return $value >= $min && $value <= $max
                    ? true
                    : str_replace([':attribute', ':min', ':max'], [$field, $min, $max], $this->messages['between.numeric']);
            }

            $length = is_string($value) ? strlen($value) : strlen((string)$value);
            return $length >= $min && $length <= $max
                ? true
                : str_replace([':attribute', ':min', ':max'], [$field, $min, $max], $this->messages['between.string']);
        });

        $this->addValidationMethod('in', function ($value, $field, ...$list) {
            return in_array($value, $list)
                ? true
                : str_replace([':attribute', ':values'], [$field, implode(', ', $list)], $this->messages['in']);
        });

        $this->addValidationMethod('boolean', function ($value, $field) {
            // Check if the value is strictly true, false, 1, 0, '1', or '0'
            if (is_bool($value) || in_array($value, [1, 0, '1', '0'], true)) {
                return true;
            }

            // Return the error message if the value is not boolean
            return str_replace(':attribute', $field, $this->messages['boolean']);
        });
    }

    private function isNumericRule(string $field): bool
    {
        return $this->hasRule($field, 'numeric');
    }

    private function isStringRule(string $field): bool
    {
        return $this->hasRule($field, 'string');
    }

    private function hasRule(string $field, string $rule): bool
    {
        if (!isset($this->rules[$field])) {
            return false;
        }

        $rules = is_string($this->rules[$field]) ? explode('|', $this->rules[$field]) : $this->rules[$field];
        return in_array($rule, $rules, true);
    }

    /**
     * Retrieves the values of a field.
     *
     * @param string $field
     * @return array
     */
    private function getFieldValues(string $field): array
    {
        $fieldValues = [];

        // Handle recursive nesting
        if (str_contains($field, '.*.')) {
            $segments = explode('.*.', $field);
            $baseField = array_shift($segments);
            $remainingField = implode('.*.', $segments);

            $baseArray = $this->getNestedValue($this->data, $baseField);

            if (is_array($baseArray)) {
                foreach ($baseArray as $key => $subArray) {
                    $fieldKey = "{$baseField}.{$key}";
                    if ($remainingField) {
                        // Recursive call for deeper nesting
                        $nestedFieldValues = $this->getFieldValues("{$fieldKey}.{$remainingField}");
                        $fieldValues = array_merge($fieldValues, $nestedFieldValues);
                    } else {
                        $fieldValues[$fieldKey] = $subArray;
                    }
                }
            }
        } else {
            // Handle the case where there's no wildcard in the field
            $fieldValues[$field] = $this->getNestedValue($this->data, $field);
        }

        return $fieldValues;
    }

    /**
     * Retrieves a nested value from an array.
     *
     * @param array $array
     * @param string $field
     * @return array|mixed|null
     */
    private function getNestedValue(array $array, string $field): mixed
    {
        $keys = explode('.', $field);
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return null;
            }
        }
        return $array;
    }

    /**
     * Check if a value is empty.
     *
     * @param $value
     * @return bool
     */
    private function isEmpty($value): bool
    {
        return is_null($value) || $value === '' || (is_array($value) && empty($value));
    }

    /**
     * Adds an error message for a specific field.
     *
     * @param string $field The field name.
     * @param string $message The error message.
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
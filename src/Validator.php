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
 * This class provides a way to validate arrays of data against a set of rules.
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
        'string' => 'The :attribute field must be a string.',
        'numeric' => 'The :attribute field must be a numeric value.',
        'array' => 'The :attribute field must be an array.',
        'min.string' => 'The :attribute field must be at least :min characters long.',
        'min.numeric' => 'The :attribute field must be at least :min.',
        'max.string' => 'The :attribute field must not exceed :max characters.',
        'max.numeric' => 'The :attribute field must not exceed :max.',
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
     * @param array<TField, TValue> $data The data to validate.
     * @param array<TField, string> $rules The rules to apply to the data.
     * @param array<TField, string> $messages Custom error messages.
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = array_merge($this->messages, $messages);

        // Register default rules
        $this->registerDefaultValidationMethods();
    }

    /**
     * Validates the data against the rules.
     *
     * @return bool Returns true if validation passes, false otherwise.
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $rules) {
            foreach (explode('|', $rules) as $rule) {
                $parameters = [];
                if (strpos($rule, ':')) {
                    [
                        $rule,
                        $parameterString
                    ] = explode(':', $rule);
                    $parameters = explode(',', $parameterString);
                }

                if (isset($this->validationMethods[$rule])) {
                    $validationMethod = $this->validationMethods[$rule];
                    $result = $validationMethod($this->data[$field] ?? null, $field, ...$parameters);
                    if ($result !== true) {
                        $this->addError($field, $result);
                    }
                } else {
                    throw new InvalidArgumentException("Validation rule {$rule} does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Adds a custom validation method.
     * @template TParams as mixed
     * @template TResult as bool|string
     *
     * @param string $name The name of the validation method.
     * @param (callable(TValue, TValue, TParams): TResult) $callback The validation method.
     * @return void
     */
    public function addValidationMethod(string $name, callable $callback): void
    {
        $this->validationMethods[$name] = $callback;
    }

    /**
     * Retrieves the validation errors.
     *
     * @return array<string, array<int, string>> The validation errors.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Registers the default validation methods.
     *
     * @return void
     */
    private function registerDefaultValidationMethods(): void
    {
        $this->addValidationMethod('required', function ($value, $field) {
            if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
                return str_replace(':attribute', $field, $this->messages['required']);
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
            if (is_numeric($value)) {
                return $value >= $min
                    ? true
                    : str_replace([
                        ':attribute',
                        ':min'
                    ], [
                        $field,
                        $min
                    ], $this->messages['min.numeric']);
            }

            if (is_string($value)) {
                return strlen($value) >= $min
                    ? true
                    : str_replace([
                        ':attribute',
                        ':min'
                    ], [
                        $field,
                        $min
                    ], $this->messages['min.string']);
            }

            throw new InvalidArgumentException('The min rule only supports numeric and string values.');
        });

        $this->addValidationMethod('max', function ($value, $field, $max) {
            if (is_numeric($value)) {
                return $value <= $max
                    ? true
                    : str_replace([
                        ':attribute',
                        ':max'
                    ], [
                        $field,
                        $max
                    ], $this->messages['max.numeric']);
            }

            if (is_string($value)) {
                return strlen($value) <= $max
                    ? true
                    : str_replace([
                        ':attribute',
                        ':max'
                    ], [
                        $field,
                        $max
                    ], $this->messages['max.string']);
            }

            throw new InvalidArgumentException('The max rule only supports numeric and string values.');
        });

        $this->addValidationMethod('between', function ($value, $field, $min, $max) {
            if (is_numeric($value)) {
                return $value >= $min && $value <= $max
                    ? true
                    : str_replace([':attribute', ':min', ':max'], [$field, $min, $max], 'The :attribute field must be between :min and :max.');
            }

            if (is_string($value)) {
                $length = strlen($value);
                return $length >= $min && $length <= $max
                    ? true
                    : str_replace([':attribute', ':min', ':max'], [$field, $min, $max], 'The :attribute field must be between :min and :max characters.');
            }

            throw new InvalidArgumentException('The between rule only supports numeric and string values.');
        });

        $this->addValidationMethod('in', function ($value, $field, ...$list) {
            return in_array($value, $list)
                ? true
                : str_replace(':attribute', $field, 'The :attribute field must be one of the following values: ' . implode(', ', $list) . '.');
        });
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

$validator = new Validator(
    ['age' => 25],
    ['age' => 'iets:18']
);

$validator->addValidationMethod('iets', function($value, $field, $parameter) {
    $iets = $value;

        return 15;
});

<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

/**
 * Validator Class
 *
 * This class provides a way to validate arrays of data against a set of rules.
 */
class Validator
{
    /**
     * @var array<string, mixed> The data to validate.
     */
    private array $data;

    /**
     * @var array<string, string> The rules to apply to the data.
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
     * @var array<string, array<int, string>> The validation errors.
     */
    private array $errors = [];

    /**
     * @var array<string, callable> Registered validation methods.
     */
    private array $validationMethods = [];

    /**
     * @param array<string, mixed> $data The data to validate.
     * @param array<string, string> $rules The rules to apply to the data.
     * @param array<string, string> $messages Custom error messages.
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
     * Registers the default validation methods.
     *
     * @return void
     */
    private function registerDefaultValidationMethods(): void
    {
        $this->addValidationMethod('required', function ($value, $field) {
            return !isset($value) || $value === '' ? str_replace(':attribute', $field, $this->messages['required']) : true;
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

            throw new \InvalidArgumentException('The min rule only supports numeric and string values.');
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

            throw new \InvalidArgumentException('The max rule only supports numeric and string values.');
        });
    }

    /**
     * Validates the data against the rules.
     *
     * @return bool Returns true if validation passes, false otherwise.
     * @throws \Exception
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $rules) {
            foreach (explode('|', $rules) as $rule) {
                $parameters = [];
                if (strpos($rule, ':')) {
                    [$rule, $parameterString] = explode(':', $rule);
                    $parameters = explode(',', $parameterString);
                }

                if (isset($this->validationMethods[$rule])) {
                    $validationMethod = $this->validationMethods[$rule];
                    $result = $validationMethod($this->data[$field] ?? null, $field, ...$parameters);
                    if ($result !== true) {
                        $this->addError($field, $result);
                    }
                } else {
                    throw new \Exception("Validation rule {$rule} does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Adds a custom validation method.
     * @template TValue as mixed
     * @template TField as string
     * @template TParams as ...string
     * @template TResult as bool|string
     *
     * @param string $name The name of the validation method.
     * @param callable(TValue, TField, TParams): TResult $callback The validation method.
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

# Validator Class Documentation

The `Validator` class provides a straightforward way to validate arrays of data against a set of rules. This class allows for easy definition and execution of validation rules with custom error messages.

## All methods available in the Validator class

- [__construct()](#__construct) Constructs a new Validator instance.
- [validate()](#validate) Validates the data against the defined rules.
- [addValidationMethod()](#addValidationMethod) Adds a custom validation method.
- [errors()](#errors) Retrieves the validation errors.

### Constructing a Validator Instance

To start using the `Validator`, instantiate it with the data to validate, the rules to apply, and optionally, custom error messages.

```php
// Create a new Validator instance
$validator = new Validator([
    'name' => 'John Doe',
    'age' => 30
], [
    'name' => 'required|string|min:5|max:50',
    'age' => 'required|numeric|min:18|max:65'
]);

// or with custom messages
$validator = new Validator([
    'name' => 'John Doe',
    'age' => 30
], [
    'name' => 'required|string|min:5|max:50',
    'age' => 'required|numeric|min:18|max:65'
],
[
    'min.string' => '(:attribute) is too short',
    'max.string' => '(:attribute) is too long',
    'min.numeric' => '(:attribute) is too low',
    'max.numeric' => '(:attribute) is too high',
    'required' => '(:attribute) is required',
    'string' => '(:attribute) must be a string',
    'numeric' => '(:attribute) must be a number',
    'array' => '(:attribute) must be an array'
]);
```

### validate()

Validates the data against the defined rules. Returns `true` if validation passes, `false` otherwise.

```php
// Validate the data
$is_valid = $validator->validate();
```

Retrieve validation errors if validation fails:
```php
if (!$is_valid) {
    $errors = $validator->errors();
    print_r($errors);
}
```

### addValidationMethod()

Adds a custom validation method.

```php
// Add a custom validation method to check if a value is even
$validator->addValidationMethod('even', function ($value, $field) {
    return $value % 2 === 0 ? true : "The {$field} field must be an even number.";
});

// Usage of custom validation rule
$validator = new Validator(
    ['number' => 3],
    ['number' => 'required|numeric|even']
);

$is_valid = $validator->validate(); // Will return false
$errors = $validator->errors(); // Will contain custom error message for 'even' rule
```

### errors()

Retrieves the validation errors. Returns an array of errors, where each key is a field name and the value is an array of error messages.

```php
// Get validation errors
$errors = $validator->errors();
print_r($errors);
```

Example of error output:
```php
Array
(
    [name] => Array
        (
            [0] => The name field must be at least 5 characters long.
        )

    [age] => Array
        (
            [0] => The age field must be a numeric value.
        )
)
```

## Built-in Validation Rules

### required

Ensures that a field is present and not empty.

```php
$rules = ['name' => 'required'];
```

### string

Ensures that a field is a string.

```php
$rules = ['name' => 'string'];
```

### numeric

Ensures that a field is a numeric value.

```php
$rules = ['age' => 'numeric'];
```

### array

Ensures that a field is an array.

```php
$rules = ['items' => 'array'];
```

### min

Ensures that a field meets the minimum requirement. Works for both strings (minimum length) and numeric values.

```php
// Minimum length for string
$rules = ['name' => 'min:5'];

// Minimum value for numeric
$rules = ['age' => 'min:18'];
```

### max

Ensures that a field does not exceed the maximum requirement. Works for both strings (maximum length) and numeric values.

```php
// Maximum length for string
$rules = ['name' => 'max:50'];

// Maximum value for numeric
$rules = ['age' => 'max:65'];
```
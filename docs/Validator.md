# Validator Class Documentation

The `Validator` class provides a straightforward way to validate arrays of data against a set of rules. This class allows for easy definition and execution of validation rules with custom error messages.

## All methods available in the Validator class

- [__construct()](#__construct) Constructs a new Validator instance.
- [validate()](#validate) Validates the data against the defined rules.
- [make()](#make) Creates a new Validator instance using static method.
- [addValidationMethod()](#addValidationMethod) Adds a custom validation method.
- [errors()](#errors) Retrieves the validation errors.

## All available built-in validation rules
- [required](#required) Ensures that a field is present and not empty.
- [required_if](#required_if) Ensures that a field is required if another field is present and has a specific value.
- [required_unless](#required_unless) Ensures that a field is required unless another field is present and has a specific value.
- [string](#string) Ensures that a field is a string.
- [numeric](#numeric) Ensures that a field is a numeric value.
- [array](#array) Ensures that a field is an array.
- [min](#min) Ensures that a field meets the minimum requirement.
- [max](#max) Ensures that a field does not exceed the maximum requirement.
- [between](#between) Ensures that a field is within a specified range.
- [in](#in) Ensures that a field is one of the specified values.

### Constructing a Validator Instance

To start using the `Validator`, instantiate it with the data to validate, the rules to apply, and optionally, custom error messages.

```php
// Create a new Validator instance
$data = [
    'number' => 3,
    'username' => 'john'
];

$rules = [
    'number' => ['required', 'numeric'], // Passing rules as an array
    'username' => 'required|string|min:5|max:10' // Passing rules as a string
];

$validator = new Validator($data, $rules);

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

### make()

Create a new Validator instance using a static method.

```php
$validator = Validator::make($data, $rules, $messages = []);
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

### Nested Array Validation

The Validator class also supports validation of nested arrays. You can specify validation rules for nested arrays using the `*` notation.

```php
// Example nested array data
$data = [
    'users' => [
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'bob@example.com']
    ]
];

// Rules for nested array
$rules = [
    'users.*.name' => 'required|string|min:3|max:50',
    'users.*.email' => 'required|string|email'
];

// Create Validator instance
$validator = new Validator($data, $rules);

// Validate data
$is_valid = $validator->validate();

// Retrieve errors if validation fails
if (!$is_valid) {
    $errors = $validator->errors();
    print_r($errors);
}
```

### addValidationMethod()

Adds a custom validation method.
The first argument is the name of the custom rule, and the second argument is a closure that defines the validation logic. The rest of the parameters are all passed to the rule. The closure should return `true` if the validation passes or an error message if it fails.

```php
// Add a custom validation method to check if a value is even
$validator->addValidationMethod('even', function ($value, $field) {
    return $value % 2 === 0 ? true : "The {$field} field must be an even number.";
});

// Add a custom validation method to check if a string's length is within a specified range
$validator->addValidationMethod('exampleBetween', function ($value, $field, $min, $max) {
    $length = strlen($value);
    if ($length < $min || $length > $max) {
        return "The {$field} field must be between {$min} and {$max} characters long.";
    }
    return true;
});


// Usage of custom validation rules
$validator = new Validator([
    'number' => 3,
    'username' => 'john'
], [
    'number' => 'required|numeric|even',
    'username' => 'required|string|exampleBetween:5,10'
]);

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

### required_if

Ensures that a field is required if another field is present and has a specific value.

```php
$rules = [
    'name' => 'required',
    'age' => 'required_if:name,John'
];
```
### required_unless

Ensures that a field is required unless another field is present and has a specific value.

```php
$rules = [
    'name' => 'required',
    'age' => 'required_unless:name,John'
];
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

### between

Ensures that a field is within a specified range. Works for both strings (length) and numeric values.

```php
// Range for string length
$rules = ['name' => 'between:5,50'];

// Range for numeric value
$rules = ['age' => 'between:18,65'];
```

### in

Ensures that a field is one of the specified values.

```php
$rules = ['status' => 'in:active,inactive'];
```

## Full Example

Below is a full example that demonstrates the use of the `Validator` class with various rules, including nested arrays and custom messages.

```php
$data = [
    'name' => 'John',
    'age' => 17,
    'email' => 'invalid-email',
    'users' => [
        ['name' => 'Alice', 'email' => 'alice@example.com'],
        ['name' => 'Bob', 'email' => 'invalid-email']
    ]
];

$rules = [
    'name' => 'required|string|min:5|max:50',
    'age' => 'required|numeric|min:18|max:65',
    'email' => 'required|email',
    'users.*.name' => 'required|string|min:3|max:50',
    'users.*.email' => 'required|email'
];

$messages = [
    'required' => '(:attribute) is required',
    'email' => '(:attribute) must be a valid email address',
    'min.string' => '(:attribute) is too short',
    'max.string' => '(:attribute) is too long',
    'min.numeric' => '(:attribute) is too low',
    'max.numeric' => '(:attribute) is too high',
    'string' => '(:attribute) must be a string',
    'numeric' => '(:attribute) must be a number'
];

$validator = new Validator($data, $rules, $messages);

$is_valid = $validator->validate();

if (!$is_valid) {
    $errors = $validator->errors();
    print_r($errors);
}
```
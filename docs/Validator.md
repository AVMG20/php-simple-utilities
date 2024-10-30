# PHP Simple Validator

A lightweight, Laravel-inspired data validation library for PHP arrays.

## Basic Usage

```php
use Avmg\PhpSimpleUtilities\Validator;

$validator = Validator::make([
    'name' => 'John Doe',
    'age' => '25',
], [
    'name' => 'required|string|min:3',
    'age' => 'required|numeric',
]);

try {
    $validated = $validator->validate();
    // Use validated data...
} catch (Exception $e) {
    $errors = $validator->errors();
    // Handle validation errors...
}
```

## Key Features

- Non-strict numeric validation (accepts both `'25'` and `25`)
- Chain multiple rules using pipe (`|`) syntax
- Supports nested array validation
- Customizable error messages
- Extensible with custom validation rules

## Available Validation Rules

### required
The field must be present and non-empty.

```php
$rules = [
    'username' => 'required',
    'email' => 'required',
];
```

### required_if
The field is required when another field equals a specific value.

```php
$rules = [
    'payment_type' => 'required|in:card,cash',
    'card_number' => 'required_if:payment_type,card',
];
```

### required_unless
The field is required unless another field equals a specific value.

```php
$rules = [
    'subscription' => 'required|in:free,premium',
    'card_details' => 'required_unless:subscription,free',
];
```

### string
The field must be a string.

```php
$rules = [
    'name' => 'string',  // "John" ✓
    'bio' => 'nullable|string',  // null ✓
];
```

### numeric
The field must be numeric (accepts both strings and numbers).

```php
$rules = [
    'age' => 'numeric',  // 25 ✓ or "25" ✓
    'price' => 'numeric',  // 99.99 ✓ or "99.99" ✓
];
```

### array
The field must be an array.

```php
$rules = [
    'items' => 'array',
    'tags' => 'nullable|array',
];
```

### boolean
The field must be a boolean or boolean-like value.

```php
$rules = [
    'active' => 'boolean',  // true ✓, false ✓, 1 ✓, 0 ✓, "1" ✓, "0" ✓
];
```

### min
Minimum value for numbers or minimum length for strings.

```php
$rules = [
    'password' => 'string|min:8',  // Minimum 8 characters
    'age' => 'numeric|min:18',     // Minimum value of 18
];
```

### max
Maximum value for numbers or maximum length for strings.

```php
$rules = [
    'username' => 'string|max:20',  // Maximum 20 characters
    'quantity' => 'numeric|max:100', // Maximum value of 100
];
```

### between
Value or length must be between specified minimum and maximum.

```php
$rules = [
    'password' => 'string|between:8,20',   // Length between 8 and 20 characters
    'quantity' => 'numeric|between:1,100',  // Value between 1 and 100
];
```

### in
The field must be one of the specified values.

```php
$rules = [
    'status' => 'in:pending,approved,rejected',
    'role' => 'required|in:user,admin,moderator',
];
```

### nullable
The field may be null.

```php
$rules = [
    'middle_name' => 'nullable|string',
    'phone' => 'nullable|numeric|min:10',
];
```

## Handling Validation Results

### Using validate()
Throws an exception if validation fails:

```php
try {
    $validated = $validator->validate();
    // $validated contains only the validated fields
} catch (Exception $e) {
    $errors = $validator->errors();
}
```

### Using passes() / fails()
Check validation status without exceptions:

```php
if ($validator->passes()) {
    $validated = $validator->validated();
} else {
    $errors = $validator->errors();
}

// Or using fails()
if ($validator->fails()) {
    $errors = $validator->errors();
    return;
}
```

## Nested Array Validation

Validate arrays of objects using dot notation and wildcards:

```php
$data = [
    'users' => [
        ['name' => 'John', 'email' => 'john@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com'],
    ]
];

$rules = [
    'users' => 'required|array',
    'users.*.name' => 'required|string|min:2',
    'users.*.email' => 'required|string',
];
```

## Custom Error Messages

```php
$validator = Validator::make($data, $rules, [
    'required' => 'The :attribute field is required!',
    'min.string' => 'The :attribute must be at least :min characters.',
    'between.numeric' => 'The :attribute must be between :min and :max.',
]);
```

## Custom Validation Rules

Add your own validation rules:

```php
$validator->addRule('phone', function ($value, $field, $parameters, $data) {
    return preg_match('/^[0-9]{10}$/', $value)
        ? true
        : "The {$field} must be a valid 10-digit phone number.";
});

// Usage
$rules = [
    'contact' => 'required|phone',
];
```

## Complete Example

```php
$data = [
    'user' => [
        'name' => 'John Doe',
        'age' => '25',
        'email' => 'john@example.com',
        'settings' => [
            'newsletter' => true,
            'theme' => 'dark'
        ]
    ],
    'order' => [
        'items' => [
            ['product_id' => 1, 'quantity' => '2'],
            ['product_id' => 2, 'quantity' => '1'],
        ]
    ]
];

$rules = [
    'user.name' => 'required|string|between:2,50',
    'user.age' => 'required|numeric|min:18',
    'user.email' => 'required|string',
    'user.settings.newsletter' => 'required|boolean',
    'user.settings.theme' => 'required|in:light,dark',
    'order.items' => 'required|array',
    'order.items.*.product_id' => 'required|numeric',
    'order.items.*.quantity' => 'required|numeric|min:1'
];

$messages = [
    'required' => ':attribute is required.',
    'between.string' => ':attribute must be between :min and :max characters.',
    'min.numeric' => ':attribute must be at least :min.',
];

try {
    $validator = Validator::make($data, $rules, $messages);
    $validated = $validator->validate();
    // Process validated data...
} catch (Exception $e) {
    $errors = $validator->errors();
    // Handle validation errors...
}
```
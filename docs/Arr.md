# Array Utilities Documentation

The `Arr` class provides a set of powerful utility methods for working with arrays in PHP. It's designed to offer a clean and expressive API for common array operations, inspired by Laravel's collection methods.

## Available Methods

- [where()](#where) - Filter items by the given key value pair
- [whereIn()](#wherein) - Filter items by the given key value pairs
- [whereNot()](#wherenot) - Filter items by excluding the given key value pair
- [contains()](#contains) - Check if an array contains a given key value pair
- [first()](#first) - Get the first element from the array passing the given truth test
- [firstWhere()](#firstwhere) - Get the first element from the array matching the given key value pair
- [last()](#last) - Get the last element from the array
- [filter()](#filter) - Filter the array using the given callback
- [map()](#map) - Map over each of the items in the array
- [each()](#each) - Iterate over each item in the array and apply a callback

### where()

Filter items in an array by a given key-value pair. This method supports various comparison operators and callback functions.

```php
use Avmg\PhpSimpleUtilities\Arr;

$array = [
    ['name' => 'John', 'age' => 25],
    ['name' => 'Jane', 'age' => 30],
    ['name' => 'Bob', 'age' => 25]
];

// Using simple equality
$result = Arr::where($array, 'age', 25);
// Returns: [['name' => 'John', 'age' => 25], ['name' => 'Bob', 'age' => 25]]

// Using comparison operator
$result = Arr::where($array, 'age', '>=', 30);
// Returns: [['name' => 'Jane', 'age' => 30]]

// Using callback
$result = Arr::where($array, function($value, $key) {
    return $value['age'] < 30;
});
```

Supported operators: `=`, `==`, `!=`, `<>`, `<`, `>`, `<=`, `>=`, `===`, `!==`

### whereIn()

Filter items by checking if a key's value exists in a given array of values.

```php
$array = [
    ['name' => 'John', 'role' => 'admin'],
    ['name' => 'Jane', 'role' => 'user'],
    ['name' => 'Bob', 'role' => 'admin']
];

$result = Arr::whereIn($array, 'role', ['admin']);
// Returns: [['name' => 'John', 'role' => 'admin'], ['name' => 'Bob', 'role' => 'admin']]
```

### whereNot()

Filter items by excluding those with a specific key-value pair.

```php
$array = [
    ['name' => 'John', 'role' => 'admin'],
    ['name' => 'Jane', 'role' => 'user'],
    ['name' => 'Bob', 'role' => 'admin']
];

$result = Arr::whereNot($array, 'role', 'admin');
// Returns: [['name' => 'Jane', 'role' => 'user']]
```

### contains()

Check if an array contains a given key-value pair.

```php
$array = [
    ['name' => 'John', 'age' => 25],
    ['name' => 'Jane', 'age' => 30],
    ['name' => 'Bob', 'age' => 25]
];

$result = Arr::contains($array, 'age', 25);
// Returns: true
```

### first()

Get the first element from the array that passes a given truth test. If no callback is provided, returns the first element.

```php
$array = [1, 2, 3, 4, 5];

// Get first element
$result = Arr::first($array);
// Returns: 1

// Get first element matching condition
$result = Arr::first($array, function($value) {
    return $value > 3;
});
// Returns: 4

// With default value if nothing matches
$result = Arr::first($array, function($value) {
    return $value > 10;
}, 'default');
// Returns: 'default'
```

### firstWhere()

Get the first element from the array that matches the given key-value pair.

```php
$array = [
    ['name' => 'John', 'age' => 25],
    ['name' => 'Jane', 'age' => 30],
    ['name' => 'Bob', 'age' => 25]
];

// Using key/value pair
$result = Arr::firstWhere($array, 'name', 'Bob');
// Returns: ['name' => 'Bob', 'age' => 25]

// Using comparison operator
$result = Arr::firstWhere($array, 'age', '>=', 18);
// Returns: ['name' => 'John', 'age' => 25]

// Using truthy check
$result = Arr::firstWhere($array, 'age');
// Returns: ['name' => 'John', 'age' => 25]

// Using callback, You can use the normal first method for this instead :) 
$result = Arr::firstWhere($array, fn($item) => $item['age'] > 18);
// Returns: ['name' => 'John', 'age' => 25]
```

### last()

Get the last element from the array that passes a given truth test. If no callback is provided, returns the last element.

```php
$array = [1, 2, 3, 4, 5];

// Get last element
$result = Arr::last($array);
// Returns: 5

// Get last element matching condition
$result = Arr::last($array, function($value) {
    return $value < 4;
});
// Returns: 3

// With default value if nothing matches
$result = Arr::last($array, function($value) {
    return $value > 10;
}, 'default');
// Returns: 'default'
```

### filter()

Filter the array using the given callback function.

```php
$array = [1, 2, 3, 4, 5];

$result = Arr::filter($array, function($value, $key) {
    return $value > 3;
});
// Returns: [4, 5]

// Without callback (removes falsy values)
$array = [0, 1, '', null, false, 'hello'];
$result = Arr::filter($array);
// Returns: [1, 'hello']
```

### map()

Map over each item in the array using a callback function. Preserves the original array keys.

```php
$array = ['a' => 1, 'b' => 2, 'c' => 3];

$result = Arr::map($array, function($value, $key) {
    return $value * 2;
});
// Returns: ['a' => 2, 'b' => 4, 'c' => 6]
```

### each()

Iterate over each item in the array and apply a callback function.

```php
$array = ['a' => 1, 'b' => 2, 'c' => 3];

Arr::each($array, function($value, $key) {
    echo "$key: $value\n";
});

// Outputs:
// a: 1
// b: 2
// c: 3
```

## Advanced Features

### Dot Notation

The utility supports dot notation for accessing nested array values:

```php
$array = [
    'user' => [
        'profile' => [
            'name' => 'John'
        ]
    ]
];

$result = Arr::where($array, 'user.profile.name', 'John');
```

### Closure Support

Many methods support closure values for dynamic evaluation:

```php
$default = function() {
    return computeExpensiveDefaultValue();
};

$result = Arr::first($array, null, $default);
```
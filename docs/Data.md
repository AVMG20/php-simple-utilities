# Data Class Documentation

## Overview

The `Data` class provides a structured way to handle data transfer objects (DTOs) with support for automatic nesting, type casting, and validation of required properties.

## Usage

### Basic Usage

To create a simple data object:

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}

$userData = UserData::from([
    'name' => 'John Doe',
    'age' => 30,
]);
```

### Nesting Data Objects

Nested data objects can be easily defined and instantiated:

```php
class AddressData extends Data
{
    public function __construct(
        public string $street,
        public string $city,
    ) {
    }
}

class CustomerData extends Data
{
    public function __construct(
        public string $name,
        public AddressData $address,
    ) {
    }
}

$customerData = CustomerData::from([
    'name' => 'Jane Doe',
    'address' => [
        'street' => '123 Elm St',
        'city' => 'Springfield',
    ],
]);
```

### Automatic Type Casting

The `Data` class automatically casts input values to the appropriate types based on the constructor parameters. Simply define the types in your class constructor, and `Data::from` will handle the rest.

```php
enum TagEnum: string {
    case electronics = 'electronics';
    case clothing = 'clothing';
    case books = 'books';
}

class ProductData extends Data
{
    public function __construct(
        public string $name,
        public float $price,
        public TagEnum $tag,
    ) {
    }
}

$productData = ProductData::from([
    'name' => 'Gadget',
    'price' => '19.99',
    'tag' => 'electronics',
]);

echo $productData->name . PHP_EOL; // Outputs: Gadget
echo $productData->price . PHP_EOL; // Outputs: 19.99
echo $productData->tag->value . PHP_EOL; // Outputs: electronics
```

### Converting to Array
The `Data` class includes a `toArray` method that converts the object and any nested `Data` objects to an associative array.
```php
$productData = new ProductData(
    name: "Gadget",
    price: 19.99,
    tag: TagEnum::Electronics
);

$array = $productData->toArray();

print_r($array);
```

### Custom classes
`Data` also works with custom classes.
please note that for the `toArray` function to work the custom used class also needs to implement this method.

```php
class CustomerData extends Data
{
    public function __construct(
        public string $name,
        public Collection $address,
    ) {
    }
}

$customerData = CustomerData::from([
    'name' => 'Jane Doe',
    'address' => Collection::collect([AddressData::from([
        'street' => '123 Elm St',
        'city' => 'Springfield',
    ])]),
]);

$customerData->toArray();
```

### From Json
The `Data` class includes a `fromJson` method that converts a JSON string to a `Data` object.
```php
$json = '{
    "name": "Gadget",
    "price": 19.99,
    "tag": "electronics"
}';
$productData = ProductData::fromJson($json);
```


### To Json
The `Data` class includes a `toJson` method that converts the `Data` object to a JSON string.
```php
$productData = new ProductData(
    name: "Gadget",
    price: 19.99,
    tag: TagEnum::Electronics
);

$json = $productData->toJson();
```

## Features

- **Automatic Type Casting:** Converts input values to the specified types, supporting PHP's basic types (`int`, `float`, `string`, `bool`, `array`) and custom classes like Enums.
- **Nesting:** Allows nested data objects, automatically handling instantiation of nested `Data` objects.
- **Validation:** Throws `InvalidArgumentException` if required attributes are missing when instantiating a data object.
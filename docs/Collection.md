
# Collection Class Method Examples()

This document provides examples for each method in the `Collection` class, illustrating how to use them in PHP code.

## All methods available in the Collection class:
- [constructor()](#constructor) make a new collection instance with an array of items.
- [collect()](#collect) make a new collection instance statically.
- [each()](#each) apply a callback function to each item in the collection.
- [count()](#count) count the number of items in the collection.
- [push()](#push) add an item to the end of the collection.
- [first()](#first) retrieve the first item.
- [last()](#last) retrieve the last item.
- [take()](#take) create a new collection with a specified number of items from the start.
- [get()](#get) retrieve the item at a given key.
- [put()](#put) set the item at a given key.
- [sum()](#sum) Get the sum of the given values.
- [unique()](#unique) retrieve all unique items in the collection.
- [isEmpty()](#isEmpty) determine if the collection is empty.
- [isNotEmpty()](#isNotEmpty) determine if the collection is not empty.
- [values()](#values) get the values of the collection.
- [reduce()](#reduce) reduce the collection to a single value.
- [map()](#map) apply a callback to each item in the collection and return a new collection of the results.
- [dot()](#dot) flatten a multi-dimensional collection into a single level using 'dot' notation for keys.
- [pipe()](#pipe) pass the collection to a given closure and return the result.
- [pipeThrough()](#pipeThrough) pass the collection to a given callback and return the result.
- [tap()](#tap) apply a given callback to the collection without affecting the collection itself.
- [all()](#all) retrieve all items in the collection. 
- [filter()](#filter) filter the collection using a callback function.
- [transform()](#transform) transform each item in the collection using a callback.
- [chunk()](#chunk) split the collection into chunks of the given size.
- [pluck()](#pluck) get the values of a specified key from the collection.
- [flatten()](#flatten) flatten a multi-dimensional collection into a single level.
- [reject()](#reject) filter the collection by removing items that pass the truth test.
- [merge()](#merge) merge another array or collection with the original collection.
- [ensure()](#ensure) verify that all elements of a collection are of a given type or list of types.
- [toArray()](#toArray) convert the collection into a plain PHP array.
- [toJson()](#toJson) convert the collection into a JSON string.

## Creating Collections
### Constructor()

Instantiates a new Collection object with an array of items.

```php
use Avmg\PhpSimpleUtilities\Collection;

// Creating a new collection instance with items
$collection = new Collection(['apple', 'banana', 'orange']);
// The collection contains: ['apple', 'banana', 'orange']
```


### Collect (Static Factory Method)

Creates a new collection instance statically, which is useful for chaining methods off a newly created collection.

```php
$collection = Collection::collect(['car', 'bike', 'plane']);
// The collection contains: ['car', 'bike', 'plane']
```


## Working with Items
### Each()

Iterates over each item in the collection, applying a callback function. If the callback returns `false`, iteration stops.

```php
$collection = Collection::collect([1, 2, 3, 4]);

// Echo each item in the collection
$collection->each(function ($item, $key) {
    echo $item . PHP_EOL;
});
// Outputs: 1 2 3 4
```


### Count()

Returns the total number of items in the collection.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

echo $collection->count();
// Outputs: 3
```


### Push()

Adds an item to the end of the collection. This is useful for appending new items.

```php
$collection = Collection::collect(['apple', 'banana']);

$collection->push('orange');
// The collection now contains: ['apple', 'banana', 'orange']
```


### First()

Retrieves the first item in the collection that passes a given truth test. If no callback is provided, the first item is returned. A default value can be specified if no item passes the test.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

// Retrieve the first item
$firstItem = $collection->first();
// $firstItem is 'apple'

// Retrieve the first item that passes the given truth test
$firstLongName = $collection->first(function ($item) {
    return strlen($item) > 5;
}, 'default');
// $firstLongName is 'banana', because 'banana' is the first item with more than 5 characters
```


### Last()

Retrieves the last item in the collection that passes a given truth test. If no callback is provided, the last item is returned. A default value can also be provided.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

// Retrieve the last item
$lastItem = $collection->last();
// $lastItem is 'orange'

// Retrieve the last item that passes a given truth test
$lastShortName = $collection->last(function ($item) {
    return strlen($item) < 6;
}, 'default');
// $lastShortName is 'apple', because it's the last item with less than 6 characters
```

### Take()

Creates a new collection with a specified number of items from the start or the end, based on the sign of the provided number.

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);

// Take the first 3 items
$newCollection = $collection->take(3);
// $newCollection contains: [1, 2, 3]

// Take the last 2 items
$newCollection = $collection->take(-2);
// $newCollection contains: [4, 5]
```


### Get()

Retrieves the item at a given key. If the key does not exist, a default value is returned.

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);

// Retrieve item by key
$name = $collection->get('name');
// $name is 'John'

// Retrieve with default value
$height = $collection->get('height', 175);
// $height is 175
```


### Put()

Sets an item at a given key. If the key already exists, the value is replaced.

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);

$collection->put('age', 31);
// The collection now contains: ['name' => 'John', 'age' => 31]
```


### Sum()

Calculates the sum of the given values. If a callback is provided, it will be used to determine the values to sum.

```php
$collection = Collection::collect([1, 2, 3, 4]);

// Calculate sum
$total = $collection->sum();
// $total is 10

// Calculate sum using a callback
$total = $collection->sum(function ($item) {
    return $item * 2;
});
// $total is 20
```

### Unique()

Retrieves all unique items in the collection.

```php
$collection = Collection::collect([1, 2, 2, 3, 3, 3]);

$unique = $collection->unique();

// $unique contains: [1, 2, 3]
```
Retrieves all unique items in the collection, based on a given key.
```php
$collection = Collection::collect([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 30],
    ['name' => 'John', 'age' => 30],
]);

$unique = $collection->unique('name');

// $unique contains: [['name' => 'John', 'age' => 30], ['name' => 'Jane', 'age' => 30]]
```

### IsEmpty()

Determines if the collection is empty.

```php
$collection = Collection::collect([]);

if ($collection->isEmpty()) {
    echo 'The collection is empty';
}
// Outputs: The collection is empty
```

### IsNotEmpty()

Determines if the collection is not empty.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

if ($collection->isNotEmpty()) {
    echo 'The collection is not empty';
}
// Outputs: The collection is not empty
```

### Values()

Retrieves the values of the collection.

```php

$collection = Collection::collect(['name' => 'John', 'age' => 30]);

$values = $collection->values();

// $values is ['John', 30]
```

### Reduce()

Reduces the collection to a single value, using a callback function.

```php
$collection = Collection::collect([1, 2, 3, 4]);

// Reduce to a single value
$result = $collection->reduce(function ($carry, $item) {
    return $carry + $item;
}, 0);
// $result is 10
```


### Map()

Applies a callback to each item in the collection and returns a new collection of the results.

```php
$collection = Collection::collect([1, 2, 3]);

$multiplied = $collection->map(function ($item) {
    return $item * 2;
});
// $multiplied contains: [2, 4, 6]
```


### Dot()

Flattens a multi-dimensional collection into a single level, using 'dot' notation for nested keys.

```php
$collection = Collection::collect(['product' => ['name' => 'Desk', 'price' => 200]]);

$flattened = $collection->dot();
// $flattened contains: ['product.name' => 'Desk', 'product.price' => 200]
```


### Pipe()

Passes the collection to a given closure, allowing for transformation or inspection without modifying the original collection.

```php
$collection = Collection::collect([1, 2, 3]);

$result = $collection->pipe(function ($collection) {
    return $collection->sum();
});
// $result is 6
```


### PipeThrough()

Passes the collection through an array of callbacks, transforming it sequentially.

```php
$collection = Collection::collect([1, 2, 3]);

$result = $collection->pipeThrough([
    function ($collection) {
        return $collection->map(function ($item) {
            return $item * 2;
        });
    },
    function ($collection) {
        return $collection->sum();
    },
]);
// $result is 12
```


### Tap()

Applies a callback to the collection for inspection or logging without affecting the original collection.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

$collection->tap(function ($collection) {
    log('Current state:', $collection->all());
});
// The collection remains unchanged
```


### All()

Retrieves all items in the collection as a PHP array.

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

$items = $collection->all();
// $items is ['apple', 'banana', 'orange']
```


### Filter()

Filters the collection using a callback function, returning a new collection with only the items that pass the truth test.

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);

$filtered = $collection->filter(function ($item) {
    return $item > 2;
});
// $filtered contains: [3, 4, 5]
```


### Transform()

Transforms each item in the collection using a callback. Unlike `map`, `transform` modifies the collection itself.

```php
$collection = Collection::collect([1, 2, 3]);

$collection->transform(function ($item) {
    return $item * 2;
});
// The collection now contains: [2, 4, 6]
```


### Chunk()

Splits the collection into chunks of the given size.

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);

$chunks = $collection->chunk(2);
// $chunks is a collection of two collections: [[1, 2], [3, 4], [5]]
```


### Pluck()

Extracts a list of values from a collection based on a given key or value pair.

```php
$collection = Collection::collect([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
]);

$names = $collection->pluck('name');
// $names contains: ['Desk', 'Chair']
```


### Reject()

Filters the collection by removing items that pass the given truth test.

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);

$rejected = $collection->reject(function ($item) {
    return $item > 2;
});
// $rejected contains: [1, 2]
```


### Merge()

Merges another array or collection with the original collection.

```php
$collection = Collection::collect(['apple', 'banana']);

$merged = $collection->merge(['cherry', 'date']);
// $merged contains: ['apple', 'banana', 'cherry', 'date']
```

### Flatten()

Flattens a multi-dimensional collection into a single level.

```php
$collection = Collection::collect([1, [2, 3], [4, [5, 6]]]);

$flattened = $collection->flatten();

// $flattened contains: [1, 2, 3, 4, 5, 6]
```

### Ensure()

Ensures that all elements in the collection match a given type or list of types.

```php
$collection = Collection::collect([1, 2, '3', 4]);

// Throws UnexpectedValueException if any element is not an integer
$collection->ensure('int');

$collection->ensure([Custom::class, AnotherCustom::class]);
```


### ToArray()

Converts the collection to a plain PHP array, including all nested objects that can be cast to arrays.

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);

$array = $collection->toArray();
// $array is ['name' => 'John', 'age' => 30]
```


### ToJson()

Converts the collection into a JSON string.

```php
$collection = new Collection(['name' => 'John', 'age' => 30]);

$json = $collection->toJson();
// $json is '{"name":"John","age":30}'
```
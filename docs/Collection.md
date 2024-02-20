
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
- [reject()](#reject) filter the collection by removing items that pass the truth test.
- [merge()](#merge) merge another array or collection with the original collection.
- [ensure()](#ensure) verify that all elements of a collection are of a given type or list of types.
- [toArray()](#toArray) convert the collection into a plain PHP array.
- [toJson()](#toJson) convert the collection into a JSON string.

## Constructor()

Creating a new Collection instance with an array of items.
```php
use Avmg\PhpSimpleUtilities\Collection;

$collection = new Collection(['a', 'b', 'c']);
```

## collect()
Creating a new Collection instance statically.
```php
use Avmg\PhpSimpleUtilities\Collection;

$collection = Collection::collect(['a', 'b', 'c']);
```

## each()
Applying a callback function to each item in the collection.
```php
$collection->each(function ($item, $key) {
    echo $item;
});
```

## count()
Counting the number of items in the collection.
```php
echo $collection->count();
```

## push()
Adding an item to the end of the collection.
```php
$collection->push('d');
// if the collection looks like this ['a', 'b', 'c'], it will now look like this ['a', 'b', 'c', 'd']
```

## first()
Retrieving the first item
```php
$firstItem = $collection->first();
// returns 'a' if the collection looks like this ['a', 'b', 'c']
```
Retrieving the first item that passes a given truth test.
```php
$firstItem = $collection->first(function ($item) {
    return $item === 'b';
});
// returns 'b' if the collection looks like this ['a', 'b', 'c']
```

## last()
Retrieving the last item
```php
$lastItem  = $collection->last();
// returns 'c' if the collection looks like this ['a', 'b', 'c']
```
Retrieving the last item that passes a given truth test.
```php
$lastItem = $collection->last(function ($item) {
    return $item === 'b';
});
// returns 'b' if the collection looks like this ['a', 'b', 'c']
```

## take()
Creating a new collection with a specified number of items from the start.
```php
$newCollection = $collection->take(2);
// returns a new collection with the items ['a', 'b'] if the original collection looks like this ['a', 'b', 'c']
```
## get()
The get method returns the item at a given key. If the key does not exist, `null` is returned:
```php
$item = $collection->get('name');
// returns 'a' if the collection looks like this ['name' => 'a', 'age' => 20]
```
## put()
Setting the item at a given key.
```php
// ['name' => a, 'age' => 20]
$collection->put('name', 'b');
// ['name' => b, 'age' => 20]
```

## sum()
Getting the sum of the given values.
```php
$collection = new Collection([1, 2, 3, 4]);

$total = $collection->sum();

// returns 10
```

## reduce()
Reducing the collection to a single value.
```php
$collection = new Collection([1, 2, 3, 4]);

$total = $collection->reduce(function ($carry, $item) {
    return $carry + $item;
}, 1);

// returns 24
```

You may optionally pass a default value as the second argument:
```php
$item = $collection->get('name', 'default');
// returns 'default' if the specified key does not exist
```
You may even pass a callback as the method's default value. The result of the callback will be returned if the specified key does not exist:
```php
$item = $collection->get('name', function () {
    return 'default';
});
// returns 'default' if the specified key does not exist
```

## map()
Applying a callback to each item in the collection and returning a new collection of the results.
```php
$multiplied = $collection->map(function ($item) {
    return $item * 2;
});
// returns a new collection with the items [2, 4, 6] if the original collection looks like this [1, 2, 3]
```

## dot()
Flattening a multi-dimensional collection into a single level using 'dot' notation for keys.
```php
$flattenedCollection = $collection->dot();
// ['products' => ['desk' => ['price' => 100]]] becomes ['products.desk.price' => 100]
```

## pipe()
Passing the collection to a given closure and returning the result.
```php
$result = $collection->pipe(function ($collection) {
    return $collection->count();
});
// returns the number of items in the collection
```

## pipeThrough()
The `pipeThrough` method passes the collection to the given array of closures and returns the result of the executed closures:
```php
$collection = new Collection([1, 2, 3]);
 
$result = $collection->pipeThrough([
    function (Collection $collection) {
        return $collection->merge([4, 5]);
    },
    function (Collection $collection) {
        return $collection->sum();
    },
]);
 
// 15
```

## tap()
Applying a given callback to the collection without affecting the collection itself.
```php
$collection->tap(function ($collection) {
    // perform operations with the collection here
    echo 'Values after sorting: '
    print_r($collection->values()->all());
});
// the collection remains unchanged
```

## all()
Retrieving all items in the collection.
```php
$items = $collection->all();

// returns ['a', 'b', 'c'] if the collection looks like this ['a', 'b', 'c']
```

## filter()
Filtering the collection using a callback function.
```php

$filteredCollection = $collection->filter(function ($item) {
    return $item !== 'a';
});

// returns a new collection with the items ['b', 'c'] if the original collection looks like this ['a', 'b', 'c']
```

## transform()
Transforming each item in the collection using a callback.
```php
$collection->transform(function ($item) {
    return strtoupper($item);
});

// the collection now looks like this ['A', 'B', 'C']
```

## chunk()
Splitting the collection into chunks of the given size.
```php
$chunks = $collection->chunk(2);

// returns a new collection with the items [['a', 'b'], ['c']] if the original collection looks like this ['a', 'b', 'c']
```

## pluck()
Getting the values of a specified key from the collection.
```php
$pluckedValues = $collection->pluck('name');
// ['product_id' => 'prod-100', 'name' => 'Desk'],
// ['product_id' => 'prod-200', 'name' => 'Chair'],
// Becomes:
// ['Desk', 'Chair']
```

## reject()
Filtering the collection by removing items that pass the truth test.
```php
$rejectedCollection = $collection->reject(function ($item) {
    return $item === 'a';
});

// returns a new collection with the items ['b', 'c'] if the original collection looks like this ['a', 'b', 'c']
```

## merge()
Merging another array or collection with the original collection.
```php
$mergedCollection = $collection->merge(['d', 'e']);

// returns a new collection with the items ['a', 'b', 'c', 'd', 'e'] if the original collection looks like this ['a', 'b', 'c']
```

## ensure()
The `ensure` method may be used to verify that all elements of a collection are of a given type or list of types. Otherwise, an `UnexpectedValueException` will be thrown:
```php
return $collection->ensure(User::class);
 
return $collection->ensure([User::class, Customer::class]);
```
Primitive types such as `string`, `int`, `float`, `bool`, and `array` may also be specified:
```php
return $collection->ensure('string');
```

## toArray()
Converting the collection into a plain PHP array.
```php
$array = $collection->toArray();
```

## toJson()
Converting the collection into a JSON string.
```php
$json = $collection->toJson();
```
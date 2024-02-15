
# Collection Class Method Examples()

This document provides examples for each method in the `Collection` class, illustrating how to use them in PHP code.

## All methods available in the Collection class:
- Constructor()
- [collect()](#collect)
- [each()](#each)
- [count()](#count)
- [push()](#push)
- [first()](#first)
- [last()](#last)
- [take()](#take)
- [get()](#get)
- [map()](#map)
- [dot()](#dot)
- [pipe()](#pipe)
- [tap()](#tap)
- [all()](#all)
- [filter()](#filter)
- [transform()](#transform)
- [chunk()](#chunk)
- [pluck()](#pluck)
- [reject()](#reject)
- [merge()](#merge)
- [ensure()](#ensure)
- [toArray()](#toArray)
- [toJson()](#toJson)

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
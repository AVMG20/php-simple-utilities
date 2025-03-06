# Collection Class

The `Collection` class provides a fluent, convenient wrapper for working with arrays of data. It offers various methods for manipulating, filtering, mapping, and extracting information from arrays.

## Creating Collections

You can create a new collection in two ways:

```php
use Avmg\PhpSimpleUtilities\Collection;

// Using the constructor
$collection = new Collection(['apple', 'banana', 'orange']);

// Using the static factory method
$collection = Collection::collect(['apple', 'banana', 'orange']);
```

## Available Methods

### Basic Methods

#### `all()`

Get all items in the collection:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$all = $collection->all();
// ['name' => 'John', 'age' => 30]
```

#### `count()`

Count the number of items in the collection:

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);
$count = $collection->count();
// 3
```

#### `isEmpty()`

Determine if the collection is empty:

```php
$collection = Collection::collect([]);
$empty = $collection->isEmpty();
// true
```

#### `isNotEmpty()`

Determine if the collection is not empty:

```php
$collection = Collection::collect(['apple', 'banana']);
$notEmpty = $collection->isNotEmpty();
// true
```

### Retrieving Items

#### `get()`

Get an item at a specified key:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$name = $collection->get('name');
// 'John'

// Provide a default value if the key doesn't exist
$height = $collection->get('height', 175);
// 175
```

#### `first()`

Get the first item in the collection:

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);
$first = $collection->first();
// 'apple'

// With a callback
$first = $collection->first(function ($item) {
    return strlen($item) > 5;
});
// 'banana' (first item with more than 5 characters)

// With a default value
$first = $collection->first(function ($item) {
    return strlen($item) > 10;
}, 'default');
// 'default' (no items have more than 10 characters)
```

#### `last()`

Get the last item in the collection:

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);
$last = $collection->last();
// 'orange'

// With a callback
$last = $collection->last(function ($item) {
    return strlen($item) < 6;
});
// 'apple' (last item with fewer than 6 characters)
```

#### `pluck()`

Extract a list of values for a given key:

```php
$collection = Collection::collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);

$products = $collection->pluck('product');
// ['Desk', 'Chair']

// Specifying a key for the resulting collection
$prices = $collection->pluck('price', 'product');
// ['Desk' => 200, 'Chair' => 100]
```

#### `values()`

Get all values in the collection, discarding keys:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$values = $collection->values();
// [0 => 'John', 1 => 30]
```

### Modifying Collections

#### `push()`

Add an item to the end of the collection:

```php
$collection = Collection::collect(['apple', 'banana']);
$collection->push('orange');
// ['apple', 'banana', 'orange']
```

#### `put()`

Put an item in the collection at a specified key:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$collection->put('occupation', 'Developer');
// ['name' => 'John', 'age' => 30, 'occupation' => 'Developer']
```

#### `merge()`

Merge another collection or array with the collection:

```php
$collection = Collection::collect(['apple', 'banana']);
$merged = $collection->merge(['cherry', 'date']);
// ['apple', 'banana', 'cherry', 'date']
```

#### `transform()`

Transform each item in the collection (modifies the collection):

```php
$collection = Collection::collect([1, 2, 3]);
$collection->transform(function ($item) {
    return $item * 2;
});
// [2, 4, 6]
```

### Filtering Collections

#### `filter()`

Filter items using a callback:

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);
$filtered = $collection->filter(function ($item) {
    return $item > 3;
});
// [3 => 4, 4 => 5]
```

#### `where()`

Filter items by a key and value:

```php
$collection = Collection::collect([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25],
    ['name' => 'Bob', 'age' => 35],
]);

// Simple equality
$johns = $collection->where('name', 'John');
// [['name' => 'John', 'age' => 30]]

// With operator
$adults = $collection->where('age', '>=', 30);
// [['name' => 'John', 'age' => 30], ['name' => 'Bob', 'age' => 35]]
```

#### `reject()`

Remove items using a callback:

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);
$filtered = $collection->reject(function ($item) {
    return $item > 3;
});
// [0 => 1, 1 => 2, 2 => 3]
```

#### `unique()`

Get unique items from the collection:

```php
$collection = Collection::collect([1, 1, 2, 2, 3, 3]);
$unique = $collection->unique();
// [0 => 1, 2 => 2, 4 => 3]

// With a key for object comparison
$collection = Collection::collect([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25],
    ['name' => 'John', 'age' => 35],
]);

$unique = $collection->unique('name');
// [0 => ['name' => 'John', 'age' => 30], 1 => ['name' => 'Jane', 'age' => 25]]
```

#### `take()`

Take the specified number of items:

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);

// From the beginning
$chunk = $collection->take(3);
// [1, 2, 3]

// From the end
$chunk = $collection->take(-2);
// [4, 5]
```

### Mapping & Transforming

#### `map()`

Map the values into a new collection:

```php
$collection = Collection::collect([1, 2, 3]);
$doubled = $collection->map(function ($item) {
    return $item * 2;
});
// [2, 4, 6]
```

#### `mapWithKeys()`

Map with custom keys:

```php
$collection = Collection::collect([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);

$keyed = $collection->mapWithKeys(function ($item) {
    return [$item['email'] => $item['name']];
});
// ['john@example.com' => 'John', 'jane@example.com' => 'Jane']
```

#### `flatten()`

Flatten a multi-dimensional collection:

```php
$collection = Collection::collect([1, [2, 3], [4, [5, 6]]]);
$flattened = $collection->flatten();
// [1, 2, 3, 4, 5, 6]

// With depth control
$flattened = $collection->flatten(1);
// [1, 2, 3, 4, [5, 6]]
```

#### `dot()`

Flatten with "dot" notation for keys:

```php
$collection = Collection::collect([
    'user' => ['name' => 'John', 'job' => ['title' => 'Developer']]
]);

$flattened = $collection->dot();
// ['user.name' => 'John', 'user.job.title' => 'Developer']
```

### Higher-Order Methods

#### `each()`

Execute a callback over each item:

```php
$collection = Collection::collect([1, 2, 3]);

$collection->each(function ($item, $key) {
    // Process each item
    echo "Key: {$key}, Value: {$item}\n";
});
```

#### `reduce()`

Reduce to a single value:

```php
$collection = Collection::collect([1, 2, 3]);
$sum = $collection->reduce(function ($carry, $item) {
    return $carry + $item;
}, 0);
// 6
```

#### `pipe()`

Pass to a callback and return the result:

```php
$collection = Collection::collect([1, 2, 3]);
$result = $collection->pipe(function ($collection) {
    return $collection->sum();
});
// 6
```

#### `pipeThrough()`

Pass through a series of callbacks:

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
// 12
```

#### `tap()`

"Tap" into the collection for debugging:

```php
$collection = Collection::collect([1, 2, 3]);
$collection->tap(function ($collection) {
    // Inspect the collection without modifying it
    var_dump($collection->all());
})->map(function ($item) {
    return $item * 2;
});
```

### Chunking & Splitting

#### `chunk()`

Split into chunks of the given size:

```php
$collection = Collection::collect([1, 2, 3, 4, 5]);
$chunks = $collection->chunk(2);
// [[1, 2], [3, 4], [5]]
```

### Testing & Finding

#### `contains()`

Determine if the collection contains a value:

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);
$contains = $collection->contains('apple');
// true

// With a callback
$collection = Collection::collect([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25],
]);

$hasAdult = $collection->contains(function ($value) {
    return $value['age'] >= 30;
});
// true

// With a key-value pair
$hasJohn = $collection->contains('name', 'John');
// true
```

#### `ensure()`

Ensure all items are of the given type(s):

```php
$collection = Collection::collect([1, 2, 3]);

// Check for a single type
$collection->ensure('int');

// Check for multiple types
$collection->ensure(['int', 'float']);
```

### Output Formatting

#### `toArray()`

Convert the collection to an array:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$array = $collection->toArray();
// ['name' => 'John', 'age' => 30]
```

#### `toJson()`

Convert the collection to JSON:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);
$json = $collection->toJson();
// '{"name":"John","age":30}'
```

## Array Access

The Collection class implements the `ArrayAccess` interface, allowing you to interact with the collection as if it were an array:

```php
$collection = Collection::collect(['name' => 'John', 'age' => 30]);

// Get an item
$name = $collection['name']; // 'John'

// Set an item
$collection['occupation'] = 'Developer';

// Check if an item exists
$exists = isset($collection['age']); // true

// Remove an item
unset($collection['age']);
```

## Iteration

The Collection class implements the `IteratorAggregate` interface, allowing you to iterate over the collection using a foreach loop:

```php
$collection = Collection::collect(['apple', 'banana', 'orange']);

foreach ($collection as $item) {
    echo $item . PHP_EOL;
}
```
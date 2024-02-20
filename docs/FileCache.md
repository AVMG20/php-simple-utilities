# File Cache Documentation
The `FileCache` class provides a simple interface for storing, retrieving, and managing cache data in the filesystem. It's designed to be intuitive and straightforward, following a simple key-value storage mechanism.
## All methods available in the FileCache class
- [constructor()](#Constructing a FileCache Instance)   Constructs a new FileCache instance.
- [put()](#put)  Stores a value in the cache under a given key for a specified duration.
- [get()](#get)  Retrieves the item at a given key.
- [remember()](#remember)  Attempts to get the item at a given key. If the key does not exist, the provided callback is executed, its result is stored in the cache, and then returned.
- [forget()](#forget)  Removes an item from the cache by its key.
- [flush()](#flush)  Clears all items from the cache.

### Constructing a FileCache Instance

To start using the FileCache, instantiate it with the path where cache files should be stored. The class will automatically handle the creation of a `cache` directory within this path.

```php
$cache = new FileCache('/path/to/storage');
```

### put()

Stores a value in the cache under a given key for a specified duration. The duration can be provided as an integer (seconds) or a `DateTime` instance for when the cache should expire.

```php
$cache->put('key', 'value', 3600); // Stores 'value' under 'key' for 3600 seconds (1 hour)

// Using DateTime for TTL
$expiration = new DateTime('+1 hour');
$cache->put('key', 'value', $expiration);
```


### get()

Retrieves the item at a given key. If the key does not exist or the cache has expired, a default value is returned. The default value can be a simple value or a closure that returns the value.

```php
// Retrieve item by key
$value = $cache->get('key');
// Returns the value stored under 'key', or null if it does not exist

// Retrieve with a simple default value
$defaultValue = $cache->get('non_existent_key', 'default_value');
// Returns 'default_value' because 'non_existent_key' does not exist in the cache

// Retrieve with a closure as the default value
$computedValue = $cache->get('another_non_existent_key', function() {
    return 'computed_default_value';
});
// Returns 'computed_default_value', computed on-the-fly because 'another_non_existent_key' does not exist
```


### remember()

Attempts to get the item at a given key. If the key does not exist, the provided callback is executed, its result is stored in the cache, and then returned.

```php
$value = $cache->remember('computed_key', 3600, function() {
    return 'computed_value';
});
// $value is 'computed_value', stored under 'computed_key' for 3600 seconds
```


### forget()

Removes an item from the cache by its key. Returns `true` if the item was successfully removed, or `false` if the item did not exist.

```php
$result = $cache->forget('key_to_remove');
// $result is true if 'key_to_remove' existed and was removed, false otherwise
```


### flush()

Clears all items from the cache.

```php
$cache->flush();
// All cache items are removed
```

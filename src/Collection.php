<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use ArrayAccess;
use Closure;
use Iterator;
use IteratorAggregate;
use stdClass;
use UnexpectedValueException;

/**
 * Collection Class
 *
 * This class provides a fluent, convenient wrapper for working with arrays of data.
 * It offers various methods for manipulating and extracting data from the arrays.
 *
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 */
class Collection implements ArrayAccess, \Countable
{
    /**
     * @var array<TKey, TValue> The items contained in the collection.
     */
    protected array $items = [];

    /**
     * Constructor.
     *
     * @param array<TKey, TValue> $items An array of items to initialize the collection with. Default is an empty array.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Static factory method to create a new collection instance.
     *
     * @param array<TKey, TValue> $items An array of items to initialize the collection with. Default is an empty array.
     * @return static<TKey, TValue> Returns a new instance of the Collection class.
     */
    public static function collect(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Applies a callback function to each item in the collection.
     *
     * Iterates over each item in the collection, applying the callback function. If the callback returns false, iteration stops.
     *
     * @param (callable(TValue, TKey): mixed) $callback The callback function to apply. The callback should accept two arguments: the item and its key.
     * @return static Returns the collection instance for method chaining.
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Counts the number of items in the collection.
     *
     * @return int Returns the count of items in the collection, or null if the count is not applicable.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Adds an item to the end of the collection.
     *
     * @param mixed $item The item to add to the collection.
     * @return static Returns the collection instance for method chaining.
     */
    public function push(mixed $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @template TFirstDefault
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @param  TFirstDefault|(Closure(): TFirstDefault)  $default
     * @return TValue|TFirstDefault
     */
    public function first(callable $callback = null, $default = null)
    {
        foreach ($this->items as $key => $item) {
            if ($callback === null || $callback($item, $key)) {
                return $item;
            }
        }

        return $this->value($default);
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Reset the keys on the collection and return only the values.
     *
     * @return static<int, TValue> A new collection instance with reindexed values.
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Get the last item from the collection.
     *
     * @template TLastDefault
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @param  TLastDefault|(Closure(): TLastDefault)  $default
     * @return TValue|TLastDefault
     */
    public function last(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $this->value($default);
            }

            return end($this->items);
        }

        $filtered = array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);

        if (empty($filtered)) {
            return $this->value($default);
        }

        return end($filtered);
    }

    /**
     * Get the sum of the given values.
     *
     * @param (callable(TValue): mixed)|string|null  $callback
     * @return mixed
     */
    public function sum($callback = null): mixed
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Creates a new collection with a specified number of items from the start or end of the current collection.
     * If the number is positive, it takes from the start. If negative, it takes from the end.
     *
     * @param int $count The number of items to include in the new collection.
     *                   Positive numbers take from the start, negative numbers from the end.
     * @return static Returns a new collection instance containing the specified number of items.
     */
    public function take(int $count): static
    {
        if ($count < 0) {
            // Taking from the end of the collection
            return new static(array_slice($this->items, $count));
        }

        // Taking from the start of the collection
        return new static(array_slice($this->items, 0, $count));
    }

    /**
     * Retrieves the item at the specified key from the collection.
     * If the key does not exist, a default value is returned, which can be a static value or the result of a callback.
     *
     * @param TKey $key The key of the item to retrieve.
     * @param mixed $default Optional. The default value to return if the key does not exist.
     *                       Can be a value or a callback.
     * @return TValue Returns the item at the specified key, the default value, or the result of the default callback.
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $this->value($default);
    }

    /**
     * Applies a callback function to each item in the collection and returns a new collection of the results.
     *
     * @param (callable(TValue, TKey): mixed) $callback $callback The callback function to apply. The callback should accept two arguments: the item and its key.
     * @return static Returns a new collection instance containing the results of applying the callback function to each item.
     */
    public function map(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $result[] = $callback($item, $key);
        }

        return new static($result);
    }

    /**
     * Flattens a multi-dimensional collection into a single level collection using 'dot' notation for keys.
     *
     * @return static Returns a new flattened collection instance.
     */
    public function dot(): static
    {
        $results = $this->flattenDot($this->items);

        return new static($results);
    }

    /**
     * A helper method to recursively flatten an array using 'dot' notation for keys.
     *
     * @param array $array The array to flatten.
     * @param string $prepend A string to prepend to keys during recursion.
     * @return array Returns the flattened array.
     */
    protected function flattenDot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, $this->flattenDot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Passes the collection to the given closure and returns the result.
     *
     * This method allows for transforming the collection using a closure, which can return anything,
     * not necessarily a collection. The original collection remains unchanged.
     *
     * @param (callable(self): mixed) $callback The closure that performs the transformation. It should accept the collection as its argument.
     * @return mixed The result of the closure.
     */
    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    /**
     * Pass the collection through a series of callable pipes and return the result.
     *
     * @param  array<(callable(mixed): mixed)>  $callbacks
     * @return mixed
     */
    public function pipeThrough($callbacks): mixed
    {
        return static::collect($callbacks)->reduce(
            fn ($carry, $callback) => $callback($carry),
            $this,
        );
    }

    /**
     * Reduce the collection to a single value.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param  callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType  $callback
     * @param  TReduceInitial  $initial
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, $initial = null)
    {
        $carry = $initial;

        foreach ($this->items as $key => $item) {
            $carry = $callback($carry, $item, $key);
        }

        return $carry;
    }

    /**
     * Put an item in the collection by key.
     *
     * @param  TKey  $key
     * @param  TValue  $value
     * @return static
     */
    public function put($key, $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Applies the given callback to the collection without affecting the collection itself.
     *
     * This method allows you to "tap" into the collection at a specific point, perform operations
     * or logging with the collection, and then continue processing without modifying the original collection.
     * The collection is returned by the tap method, allowing for method chaining.
     *
     * @param (callable(self): void) $callback The callback function to apply. The callback should accept the collection as its argument.
     * @return static Returns the collection instance for method chaining.
     */
    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }

    /**
     * Retrieves all items in the collection.
     *
     * @return array<TKey, TValue> Returns an array of all the items in the collection.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Filters the collection using a callback function and returns a new collection with the filtered items.
     *
     * @param (callable(TValue, TKey): bool) $callback The callback function for filtering. The callback should accept two arguments: the item and its key.
     * @return static Returns a new collection instance containing only the items that pass the callback function filter.
     */
    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param  (callable(TValue, TKey): TValue) $callback
     * @return static
     */
    public function transform(callable $callback): static
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * Split the collection into chunks of the given size.
     *
     * @param  int  $size
     * @return static
     */
    public function chunk(int $size): static
    {
        if ($size <= 0) {
            return new static;
        }

        $chunks = [];
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Get the values of a specified key from the collection.
     * The method allows using "dot" notation for accessing nested array elements.
     * If a key is provided, the method returns an associative array with keys from the collection
     * and the corresponding values from the nested arrays or objects.
     *
     * @param  string  $value  The key to pluck from the collection items. Supports dot notation for nested arrays.
     * @param  string|null  $key  Optional. The key to serve as the resulting array's keys. Also supports dot notation.
     * @return static  A new collection instance containing the plucked values.
     */

    public function pluck(string $value, string|null $key = null): static
    {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = $this->dataGet($item, $value);

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = $this->dataGet($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    /**
     * Filter the collection using the given callback, removing items that pass the truth test.
     *
     * @param  (callable(TValue, TKey): bool)|mixed  $callback
     * @return static
     */
    public function reject(mixed $callback): static
    {
        if (is_callable($callback)) {
            return $this->filter(function ($value, $key) use ($callback) {
                return !$callback($value, $key);
            });
        }

        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }

    /**
     * Retrieve an item from an array or object using "dot" notation.
     * This method allows for traversing nested arrays or objects to access deep values.
     * If the specified key is not found, the method returns the default value.
     *
     * @param  mixed   $target  The array or object to search in.
     * @param  string  $key  The key to search for, using dot notation for nested elements.
     * @param  mixed   $default  Optional. The default value to return if the specified key does not exist.
     *                          This value is returned when the final segment of the key is not found in the target.
     * @return mixed  The value of the specified key in the target, or the default value if the key is not found.
     */
    protected function dataGet(mixed $target, string $key, mixed $default = null): mixed
    {
        $key = explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return $default;
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (!isset($target[$segment])) {
                    return $default;
                }

                $target = $target[$segment];
            } else {
                return $default;
            }
        }

        return $target;
    }

    /**
     * Return the default value of the given value or callback.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Merges the given array or collection with the original collection.
     *
     * If a string key in the given items matches a string key in the original collection,
     * the value from the given items will overwrite the original value.
     * If the keys are numeric, the values will be appended to the end of the collection.
     *
     * @param Collection|array $items The items to be merged into the collection.
     * @return static<TKey, TValue> Returns a new collection instance containing the merged items.
     */
    public function merge(Collection|array $items): static
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        // Merging associative arrays
        $merged = array_merge($this->items, $items);

        return new static($merged);
    }

    /**
     * Converts the collection into a plain PHP array.
     *
     * This method will recursively convert all objects that are instances of Collection
     * into arrays as well. If an item in the collection is an object that implements
     * the toArray method, that method will be called to convert the object.
     *
     * @return array A plain PHP array containing the collection's elements.
     */
    public function toArray(): array
    {
        return $this->recursiveToArray($this->items);
    }

    /**
     * Recursively convert items to an array.
     *
     * @param mixed $value The item to be converted.
     * @return mixed The converted item.
     */
    protected function recursiveToArray($value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'recursiveToArray'], $value);
        } elseif ($value instanceof Collection) {
            return $this->recursiveToArray($value->all());
        } elseif ($value instanceof ArrayAccess || $value instanceof Iterator || $value instanceof IteratorAggregate) {
            return $this->recursiveToArray(iterator_to_array($value));
        } elseif ($value instanceof stdClass) {
            return $this->recursiveToArray((array) $value);
        } elseif (is_object($value) && method_exists($value, 'toArray')) {
            return $this->recursiveToArray($value->toArray());
        }

        return $value;
    }

    /**
     * Converts the collection into a JSON string.
     *
     * This method first converts the collection to an array using the toArray method,
     * and then encodes this array into a JSON string. It uses PHP's json_encode function,
     * and you can pass options and a depth parameter to customize the JSON encoding behavior.
     *
     * @param int $options Optional. Options for json_encode. Default is 0.
     * @param int $depth Optional. Maximum depth for JSON encoding. Default is 512.
     * @return string A JSON encoded string representing the collection.
     */
    public function toJson(int $options = 0, int $depth = 512): string
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    /**
     * Ensures that all elements of the collection are of a given type or list of types.
     *
     * This method checks each item in the collection against the specified type(s).
     * The type can be a class name or a primitive type (e.g., 'string', 'int').
     * If any item is not of the specified type(s), an UnexpectedValueException is thrown.
     *
     * @param array|string $types The type or array of types to check against.
     * @return static Returns the collection instance if all items match the type(s).
     * @throws UnexpectedValueException if any item does not match the type(s).
     */
    public function ensure(array|string $types): static
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($this->items as $item) {
            if (!$this->checkType($item, $types)) {
                throw new UnexpectedValueException("All elements must be of type: " . implode(', ', $types));
            }
        }

        return $this;
    }

    /**
     * Checks if a value is of one of the specified types.
     *
     * @param mixed $value The value to check.
     * @param array $types Array of types to check against.
     * @return bool True if the value matches one of the types, false otherwise.
     */
    protected function checkType(mixed $value, array $types): bool
    {
        foreach ($types as $type) {
            if ($type === gettype($value)) {
                return true;
            } elseif (is_object($value) && is_a($value, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether an offset exists
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @return TValue|null
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Offset to set
     *
     * @param TKey $offset
     * @param TValue $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
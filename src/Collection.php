<?php

declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use stdClass;
use UnexpectedValueException;

/**
 * Collection Class
 *
 * A fluent, convenient wrapper for working with arrays of data that offers
 * various methods for manipulating and extracting data from arrays.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array<TKey, TValue> The items contained in the collection.
     */
    protected array $items = [];

    /**
     * Creates a new collection instance.
     *
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Creates a new collection instance.
     *
     * @param array<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public static function collect(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Get all items in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if the collection is not empty.
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get the first item from the collection.
     *
     * @template TFirstDefault
     *
     * @param (callable(TValue, TKey): bool)|null $callback
     * @param TFirstDefault|callable(): TFirstDefault $default
     *
     * @return TValue|TFirstDefault
     */
    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $this->value($default);
            }

            foreach ($this->items as $item) {
                return $item;
            }
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $this->value($default);
    }

    /**
     * Get the last item from the collection.
     *
     * @template TLastDefault
     *
     * @param (callable(TValue, TKey): bool)|null $callback
     * @param TLastDefault|callable(): TLastDefault $default
     *
     * @return TValue|TLastDefault
     */
    public function last(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($this->items) ? $this->value($default) : end($this->items);
        }

        $filtered = array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);

        return empty($filtered) ? $this->value($default) : end($filtered);
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param TValue $item
     * @return $this<TKey, TValue>
     */
    public function push(mixed $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Put an item in the collection by key.
     *
     * @param TKey $key
     * @param TValue $value
     * @return $this<TKey, TValue>
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Get a value from the collection.
     *
     * @template TGetDefault
     *
     * @param TKey $key
     * @param TGetDefault|callable(): TGetDefault $default
     *
     * @return TValue|TGetDefault
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->items)
            ? $this->items[$key]
            : $this->value($default);
    }

    /**
     * Applies a callback to each item in the collection.
     *
     * @param callable(TValue, TKey): mixed $callback
     * @return $this<TKey, TValue>
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
     * Filter the collection using the given callback.
     *
     * @param (callable(TValue, TKey): bool)|null $callback
     * @return static<TKey, TValue>
     */
    public function filter(?callable $callback = null): static
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param string|callable(TValue, TKey): bool $key
     * @param mixed $operator
     * @param mixed $value
     * @return static<TKey, TValue>
     */
    public function where(string|callable $key, mixed $operator = null, mixed $value = null): static
    {
        if ($this->useAsCallable($key)) {
            return $this->filter($key);
        }

        return $this->filter($this->operatorForWhere($key, $operator, $value));
    }

    /**
     * Filter the collection using the given callback, removing items that pass the truth test.
     *
     * @param callable(TValue, TKey): bool|mixed $callback
     * @return static<TKey, TValue>
     */
    public function reject(mixed $callback): static
    {
        if (is_callable($callback)) {
            return $this->filter(fn ($value, $key) => !$callback($value, $key));
        }

        return $this->filter(fn ($item) => $item != $callback);
    }

    /**
     * Map the values into a new collection.
     *
     * @template TMapValue
     *
     * @param callable(TValue, TKey): TMapValue $callback
     * @return static<int, TMapValue>
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
     * Run an associative map over each of the items.
     *
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue> $callback
     * @return static<TMapWithKeysKey, TMapWithKeysValue>
     */
    public function mapWithKeys(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);
            foreach ($assoc as $assocKey => $assocValue) {
                $result[$assocKey] = $assocValue;
            }
        }

        return new static($result);
    }

    /**
     * Reduce the collection to a single value.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
     * @param TReduceInitial $initial
     *
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $carry = $initial;

        foreach ($this->items as $key => $item) {
            $carry = $callback($carry, $item, $key);
        }

        return $carry;
    }

    /**
     * Get the sum of the given values.
     *
     * @param callable(TValue): mixed|string|null $callback
     * @return mixed
     */
    public function sum(callable|string|null $callback = null): mixed
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }

        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Transform each item in the collection.
     *
     * @param callable(TValue, TKey): TValue $callback
     * @return $this<TKey, TValue>
     */
    public function transform(callable $callback): static
    {
        $this->items = array_map($callback, $this->items);
        return $this;
    }

    /**
     * Pass the collection to the given callback and return the result.
     *
     * @param callable(static<TKey, TValue>): mixed $callback
     * @return mixed
     */
    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    /**
     * Pass the collection through a series of callable pipes and return the result.
     *
     * @param array<callable(mixed): mixed> $callbacks
     * @return mixed
     */
    public function pipeThrough(array $callbacks): mixed
    {
        return static::collect($callbacks)->reduce(
            fn ($carry, $callback) => $callback($carry),
            $this,
        );
    }

    /**
     * Apply a callback without affecting the collection.
     *
     * @param callable(static<TKey, TValue>): void $callback
     * @return $this<TKey, TValue>
     */
    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }

    /**
     * Create a collection with a specified number of items.
     *
     * @param int $count
     * @return static<TKey, TValue>
     */
    public function take(int $count): static
    {
        if ($count < 0) {
            return new static(array_slice($this->items, $count));
        }

        return new static(array_slice($this->items, 0, $count));
    }

    /**
     * Reset the keys and return the values.
     *
     * @return static<int, TValue>
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Get the values of a specified key.
     *
     * @param string $value
     * @param string|null $key
     * @return static
     */
    public function pluck(string $value, ?string $key = null): static
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
     * Chunk the collection into arrays of the given size.
     *
     * @param int $size
     * @return static<int, static<TKey, TValue>>
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
     * Flatten a multi-dimensional collection into a single level.
     *
     * @param int $depth
     * @return static<int, mixed>
     */
    public function flatten(int $depth = 256): static
    {
        return new static($this->doFlatten($this->items, $depth));
    }

    /**
     * Helper method to recursively flatten an array.
     *
     * @param array $array
     * @param int $depth
     * @return array
     */
    protected function doFlatten(array $array, int $depth): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, $this->doFlatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Flatten a multi-dimensional collection into a single level with 'dot' notation for keys.
     *
     * @return static
     */
    public function dot(): static
    {
        return new static($this->flattenDot($this->items));
    }

    /**
     * Helper method to recursively flatten an array using 'dot' notation for keys.
     *
     * @param array $array
     * @param string $prepend
     * @return array
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
     * Return only unique items.
     *
     * @param callable(TValue, TKey): mixed|string|null $key
     * @param bool $strict
     * @return static<TKey, TValue>
     */
    public function unique(callable|string|null $key = null, bool $strict = false): static
    {
        if (is_null($key) && $strict === false) {
            return new static(array_unique($this->items, SORT_REGULAR));
        }

        $callback = $this->valueRetriever($key);
        $exists = [];

        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            $id = $callback($item, $key);
            if (in_array($id, $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
            return false;
        });
    }

    /**
     * Merge the given items into the collection.
     *
     * @param Collection<TKey, TValue>|array<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function merge(Collection|array $items): static
    {
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return new static(array_merge($this->items, $items));
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param callable(TValue, TKey): bool|TValue|string $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function contains(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;
                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->items);
        }

        return $this->contains($this->operatorForWhere($key, $operator, $value));
    }

    /**
     * Ensure all elements are of the given type(s).
     *
     * @param array|string $types
     * @return $this<TKey, TValue>
     * @throws UnexpectedValueException
     */
    public function ensure(array|string $types): static
    {
        $types = is_array($types) ? $types : [$types];

        foreach ($this->items as $item) {
            if (!$this->checkType($item, $types)) {
                throw new UnexpectedValueException("All elements must be of type: " . implode(', ', $types));
            }
        }

        return $this;
    }

    /**
     * Check if a value is of one of the specified types.
     *
     * @param mixed $value
     * @param array $types
     * @return bool
     */
    protected function checkType(mixed $value, array $types): bool
    {
        foreach ($types as $type) {
            if ($type === gettype($value) || (is_object($value) && is_a($value, $type))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the collection to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->recursiveToArray($this->items);
    }

    /**
     * Recursively convert an item to an array.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function recursiveToArray(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'recursiveToArray'], $value);
        }

        if ($value instanceof Collection) {
            return $this->recursiveToArray($value->all());
        }

        if ($value instanceof ArrayAccess || $value instanceof IteratorAggregate) {
            return $this->recursiveToArray(iterator_to_array($value));
        }

        if ($value instanceof stdClass) {
            return $this->recursiveToArray((array) $value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->recursiveToArray($value->toArray());
        }

        return $value;
    }

    /**
     * Convert the collection to JSON.
     *
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson(int $options = 0, int $depth = 512): string
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    /**
     * Determine if a key exists.
     *
     * @param mixed $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return TValue|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Return the default value of the given value.
     *
     * @template T
     *
     * @param T|Closure(): T $value
     * @return T
     */
    protected function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Get a value retriever callback.
     *
     * @param callable|string|null $value
     * @return callable(TValue, TKey): mixed
     */
    protected function valueRetriever(callable|string|null $value): callable
    {
        if (is_null($value)) {
            return fn ($item) => $item;
        }

        if (is_string($value)) {
            return fn ($item) => $this->dataGet($item, $value);
        }

        return $value;
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     * @return bool
     */
    protected function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get a callback for filtering using an operator.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return Closure(TValue, TKey): bool
     */
    protected function operatorForWhere(string $key, mixed $operator = null, mixed $value = null): Closure
    {
        // The number of arguments passed to the calling function (where)
        // is different from the number of arguments passed to this method,
        // so we need to handle this manually

        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item, $itemKey) use ($key, $operator, $value) {
            $retrieved = $this->dataGet($item, $key);

            return match($operator) {
                '=', '==' => $retrieved == $value,
                '!=', '<>' => $retrieved != $value,
                '<' => $retrieved < $value,
                '>' => $retrieved > $value,
                '<=' => $retrieved <= $value,
                '>=' => $retrieved >= $value,
                '===' => $retrieved === $value,
                '!==' => $retrieved !== $value,
                default => false,
            };
        };
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function dataGet(mixed $target, string $key, mixed $default = null): mixed
    {
        if (is_null($target)) {
            return $this->value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && property_exists($target, $segment)) {
                $target = $target->$segment;
            } else {
                return $this->value($default);
            }
        }

        return $target;
    }
}
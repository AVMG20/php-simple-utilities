<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Closure;

class Arr
{
    /**
     * Filter items by the given key value pair.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to filter
     * @param string|callable(TValue, TKey): bool $key Either a dot-notation key string or a callback function
     * @param mixed $operator Comparison operator (=, ==, !=, <>, etc.) or the value when using 2 arguments
     * @param mixed $value The value to compare against when using 3 arguments
     *
     * @return array<TKey, TValue> Filtered array containing only matching elements
     */
    public static function where(array $array, string|callable $key, mixed $operator = null, mixed $value = null): array
    {
        if (static::useAsCallable($key)) {
            return array_filter($array, $key, ARRAY_FILTER_USE_BOTH);
        }

        return array_filter($array, static::operatorForWhere($key, $operator, $value), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Determine if an array contains a given item or key-value pair,
     * or if a callback returns true for any item.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The array to search in
     * @param mixed|callable(TValue, TKey): bool|string $key
     *        When a callback: Function that returns true when item is found
     *        When a value: The value to search for
     *        When a string with $operator: Dot-notation key to check
     * @param mixed $operator When present with string $key: Value to compare against
     *
     * @return bool True if the array contains the given value or key-value pair, or if callback returns true
     */
    public static function contains(array $array, mixed $key, mixed $operator = null): bool
    {
        if (is_callable($key) && !is_string($key)) {
            foreach ($array as $k => $value) {
                if ($key($value, $k)) {
                    return true;
                }
            }
            return false;
        }

        if (func_num_args() === 2) {
            foreach ($array as $item) {
                if (is_array($item)) {
                    if (in_array($key, $item, true)) {
                        return true;
                    }
                } else {
                    if ($item === $key) {
                        return true;
                    }
                }
            }
            return false;
        }

        return count(static::where($array, $key, '=', $operator)) > 0;
    }

    /**
     * Filter items by the given key value pairs.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to filter
     * @param string $key Dot-notation key to compare against values
     * @param array<int|string, mixed> $values Array of values to match against
     *
     * @return array<TKey, TValue> Filtered array containing only elements where key value is in $values
     */
    public static function whereIn(array $array, string $key, array $values): array
    {
        return static::where($array, fn($item) =>
            in_array(static::get($item, $key), $values, true)
        );
    }

    /**
     * Filter items by the given key value pair, excluding matching items.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to filter
     * @param string $key Dot-notation key to compare against value
     * @param mixed $value Value to exclude from results
     *
     * @return array<TKey, TValue> Filtered array containing only elements where key value is not equal to $value
     */
    public static function whereNot(array $array, string $key, mixed $value): array
    {
        return static::where($array, fn($item) =>
            static::get($item, $key) !== $value
        );
    }

    /**
     * Get the first element from the array passing the given truth test.
     *
     * @template TKey
     * @template TValue
     * @template TDefault
     *
     * @param array<TKey, TValue> $array The input array to search
     * @param (callable(TValue, TKey): bool)|null $callback Optional callback function to test elements
     * @param TDefault|callable(): TDefault $default Default value to return if no item passes the test
     *
     * @return TValue|TDefault The first matching item or the default value
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return static::value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return static::value($default);
    }

    /**
     * Get the first element in the array matching the given key/value pair.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to search
     * @param string|callable(TValue, TKey): bool $key
     *        When a string: Dot-notation key to compare
     *        When a callback: Function that returns true when item is found
     * @param mixed $operator Comparison operator or value when used as 2nd argument
     * @param mixed $value Value to compare against when using operator
     *
     * @return TValue|null The first matching item or null if not found
     */
    public static function firstWhere(array $array, string|callable $key, mixed $operator = null, mixed $value = null): mixed
    {
        if (static::useAsCallable($key)) {
            foreach ($array as $k => $item) {
                if ($key($item, $k)) {
                    return $item;
                }
            }
            return null;
        }

        if (func_num_args() === 2) {
            foreach ($array as $item) {
                $result = static::get($item, $key);
                if ($result) {
                    return $item;
                }
            }
            return null;
        }

        $callback = static::operatorForWhere($key, $operator, $value);

        foreach ($array as $k => $item) {
            if ($callback($item, $k)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get the last element from the array.
     *
     * @template TKey
     * @template TValue
     * @template TDefault
     *
     * @param array<TKey, TValue> $array The input array
     * @param (callable(TValue, TKey): bool)|null $callback Optional callback function to test elements
     * @param TDefault|callable(): TDefault $default Default value to return if no item passes the test
     *
     * @return TValue|TDefault The last matching item or the default value
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? static::value($default) : end($array);
        }

        $filtered = array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);

        if (empty($filtered)) {
            return static::value($default);
        }

        return end($filtered);
    }

    /**
     * Filter the array using the given callback.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to filter
     * @param (callable(TValue, TKey): bool)|null $callback Optional callback function to test elements
     *
     * @return array<TKey, TValue> Filtered array containing only elements that pass the test
     */
    public static function filter(array $array, ?callable $callback = null): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Map over each of the items in the array.
     *
     * @template TKey
     * @template TIn
     * @template TOut
     *
     * @param array<TKey, TIn> $array The input array to map
     * @param callable(TIn, TKey): TOut $callback Function to transform each element
     *
     * @return array<TKey, TOut> New array with transformed values but original keys
     */
    public static function map(array $array, callable $callback): array
    {
        $keys = array_keys($array);
        $items = array_map($callback, $array, $keys);

        return array_combine($keys, $items);
    }

    /**
     * Iterate over each of the items in the array.
     *
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $array The input array to iterate over
     * @param callable(TValue, TKey): void $callback Function to execute on each element
     *
     * @return void
     */
    public static function each(array $array, callable $callback): void
    {
        foreach ($array as $key => $value) {
            $callback($value, $key);
        }
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @template TTarget of array<string, mixed>|object
     * @template TReturn
     * @template TDefault
     *
     * @param TTarget|null $target The target array or object to retrieve the value from
     * @param string $key The "dot" notation key used to fetch the desired value
     * @param TDefault|callable(): TDefault $default The default value to return if the key does not exist
     *
     * @return TReturn|TDefault The value retrieved from the target or the default value if not found
     * @deprecated use Arr::get instead
     */
    public static function dataGet(mixed $target, string $key, mixed $default = null)
    {
        return static::get($target, $key, $default);
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @template TTarget of array<string, mixed>|object
     * @template TReturn
     * @template TDefault
     *
     * @param TTarget|null $target The target array or object to retrieve the value from
     * @param string $key The "dot" notation key used to fetch the desired value
     * @param TDefault|callable(): TDefault $default The default value to return if the key does not exist
     *
     * @return TReturn|TDefault The value retrieved from the target or the default value if not found
     */
    public static function get(mixed $target, string $key, mixed $default = null): mixed
    {
        if (is_null($target)) {
            return self::value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && property_exists($target, $segment)) {
                $target = $target->$segment;
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value The value to check
     *
     * @return bool True if value is callable but not a string
     */
    protected static function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get a callback for filtering using an operator.
     *
     * @template TKey
     * @template TValue
     *
     * @param string $key Dot-notation key to compare
     * @param mixed $operator Comparison operator or value when operator is defaulted to =
     * @param mixed $value Value to compare against or null when using 2 arguments
     *
     * @return Closure(TValue, TKey): bool Callback function that implements the comparison
     */
    protected static function operatorForWhere(string $key, mixed $operator = null, mixed $value = null): Closure
    {
        if (func_num_args() === 2 || func_num_args() === 3 && $value === null) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = static::get($item, $key, null);

            if (is_null($retrieved)) {
                return false;
            }

            return match ($operator) {
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
     * Return the default value of the given value.
     *
     * @template T
     *
     * @param T|Closure(): T $value The value or closure to evaluate
     *
     * @return T The value or the result of the closure
     */
    protected static function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Closure;

class Arr
{
    /**
     * Filter items by the given key value pair.
     *
     * @template T
     * @param array<mixed, T> $array
     * @param string|callable(T, mixed): bool $key
     * @param mixed $operator
     * @param mixed $value
     * @return array<mixed, T>
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
     * @template T
     * @param array<mixed, T> $array
     * @param mixed|callable(T, mixed): bool|string $key
     * @param mixed $operator
     * @return bool
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
     * @template T
     * @param array<mixed, T> $array
     * @param string $key
     * @param array<mixed> $values
     * @return array<mixed, T>
     */
    public static function whereIn(array $array, string $key, array $values): array
    {
        return static::where($array, fn($item) =>
        in_array(static::dataGet($item, $key), $values, true)
        );
    }

    /**
     * Filter items by the given key value pair.
     *
     * @template T
     * @param array<mixed, T> $array
     * @param string $key
     * @param mixed $value
     * @return array<mixed, T>
     */
    public static function whereNot(array $array, string $key, mixed $value): array
    {
        return static::where($array, fn($item) =>
            static::dataGet($item, $key) !== $value
        );
    }

    /**
     * Get the first element from the array passing the given truth test.
     *
     * @template T
     * @param array<mixed, T> $array
     * @param (callable(T, mixed): bool)|null $callback
     * @param T|null $default
     * @return T|null
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
     * @template T
     * @param array<mixed, T> $array
     * @param string|callable(T, mixed): bool $key
     * @param mixed $operator
     * @param mixed $value
     * @return T|null
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
                $result = static::dataGet($item, $key);
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
     * @template T
     * @param array<mixed, T> $array
     * @param (callable(T, mixed): bool)|null $callback
     * @param T|null $default
     * @return T|null
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
     * @template T
     * @param array<mixed, T> $array
     * @param (callable(T, mixed): bool)|null $callback
     * @return array<mixed, T>
     */
    public static function filter(array $array, ?callable $callback = null): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Map over each of the items in the array.
     *
     * @template TIn
     * @template TOut
     * @param array<mixed, TIn> $array
     * @param callable(TIn, mixed): TOut $callback
     * @return array<mixed, TOut>
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
     * @template T
     * @param array<mixed, T> $array
     * @param callable(T, mixed): void $callback
     * @return void
     */
    public static function each(array $array, callable $callback): void
    {
        foreach ($array as $key => $value) {
            $callback($value, $key);
        }
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     * @return bool
     */
    protected static function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get a callback for filtering using an operator.
     *
     * @template T
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return Closure(T, mixed): bool
     */
    protected static function operatorForWhere(string $key, mixed $operator = null, mixed $value = null): Closure
    {
        if (func_num_args() === 2 || func_num_args() === 3 && $value === null) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = static::dataGet($item, $key, null);

            if (is_null($retrieved)) {
                return false;
            }

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
     * @template T
     * @param T $target
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected static function dataGet(mixed $target, string $key, mixed $default = null): mixed
    {
        if (is_null($target)) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && property_exists($target, $segment)) {
                $target = $target->$segment;
            } else {
                return $default;
            }
        }

        return $target;
    }

    /**
     * Return the default value of the given value.
     *
     * @template T
     * @param T|Closure(): T $value
     * @return T
     */
    protected static function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
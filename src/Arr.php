<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use Closure;

class Arr
{
    /**
     * Filter items by the given key value pair.
     *
     * @param array $array
     * @param string|callable $key
     * @param mixed $operator
     * @param mixed $value
     * @return array
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
     * @param array $array
     * @param mixed|callable|string $key
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
     * @param array $array
     * @param string $key
     * @param array $values
     * @return array
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
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
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
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
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
     * @param array $array
     * @param string|callable $key
     * @param mixed $operator
     * @param mixed $value
     * @return mixed
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

        // Handle the case where only key is provided (truthy check)
        if (func_num_args() === 2) {
            foreach ($array as $item) {
                $result = static::dataGet($item, $key);
                if ($result) {
                    return $item;
                }
            }
            return null;
        }

        // Use the existing where logic to get the operator closure
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
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
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
     * @param array $array
     * @param callable|null $callback
     * @return array
     */
    public static function filter(array $array, ?callable $callback = null): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Map over each of the items in the array.
     *
     * @param array $array
     * @param callable $callback
     * @return array
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
     * @param array $array
     * @param callable $callback
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
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return Closure
     */
    protected static function operatorForWhere(string $key, mixed $operator = null, mixed $value = null): Closure
    {
        if (func_num_args() === 2 || func_num_args() === 3 && $value === null) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = static::dataGet($item, $key, null);

            // Handle null values
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
     * @param mixed $target
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
     * @param mixed $value
     * @return mixed
     */
    protected static function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
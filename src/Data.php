<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use ArrayAccess;
use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use UnitEnum;

/**
 * Base DTO Data class
 *
 * This class provides a base implementation for Data Transfer Objects (DTOs).
 * It includes functionality for creating instances from arrays, type casting,
 * and converting objects back to arrays.
 */
abstract class Data implements JsonSerializable, ArrayAccess
{
    /**
     * Create a new instance from an array of attributes.
     *
     * @param array $attributes The source attributes for creating the data object.
     * @return static Returns an instance of the data object.
     * @throws InvalidArgumentException|ReflectionException
     */
    public static function from(array $attributes): static
    {
        $reflectionClass = new ReflectionClass(static::class);
        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return new static();
        }

        return $reflectionClass->newInstanceArgs(
            self::resolveConstructorParameters($constructor->getParameters(), $attributes)
        );
    }

    /**
     * Create a new instance from a json string
     *
     * @param string $json The json string to create the data object from.
     * @return static Returns an instance of the data object.
     * @throws InvalidArgumentException|ReflectionException
     */
    public static function fromJson(string $json): static
    {
        return static::from(json_decode($json, true));
    }

    /**
     * Resolve constructor parameters from attributes array.
     *
     * @param array<ReflectionParameter> $parameters
     * @param array $attributes
     * @return array
     * @throws ReflectionException
     */
    private static function resolveConstructorParameters(array $parameters, array $attributes): array
    {
        return array_map(fn(ReflectionParameter $parameter) => self::castValue(
            $parameter,
            self::resolveParameterValue($parameter, $attributes)
        ),
            $parameters
        );
    }

    /**
     * Resolve the value for a parameter from attributes array.
     *
     * @param ReflectionParameter $parameter
     * @param array $attributes
     * @return mixed
     * @throws ReflectionException
     */
    private static function resolveParameterValue(ReflectionParameter $parameter, array $attributes): mixed
    {
        $name = $parameter->getName();

        if (array_key_exists($name, $attributes)) {
            return $attributes[$name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->getType()?->allowsNull()) {
            return null;
        }

        throw new InvalidArgumentException(
            sprintf("Missing required attribute: '%s' in %s::from() method.", $name, static::class)
        );
    }

    /**
     * Cast a value to the requested type.
     *
     * @param ReflectionParameter $parameter
     * @param mixed $value
     * @return mixed
     * @throws ReflectionException
     */
    private static function castValue(ReflectionParameter $parameter, mixed $value): mixed
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin() || $value === null) {
            return $value;
        }

        $typeName = $type->getName();

        if (enum_exists($typeName)) {
            return self::castToEnum($value, $typeName);
        }

        if (is_subclass_of($typeName, self::class)) {
            return self::castToDataObject($value, $typeName);
        }

        return $value;
    }

    /**
     * Cast a value to an enum type.
     *
     * @param mixed $value
     * @param class-string<UnitEnum|BackedEnum> $enumClass
     * @return UnitEnum|BackedEnum
     * @throws InvalidArgumentException
     */
    private static function castToEnum(mixed $value, string $enumClass): UnitEnum|BackedEnum
    {
        if ($value instanceof $enumClass) {
            return $value;
        }

        if ($enum = $enumClass::tryFrom($value)) {
            return $enum;
        }

        throw new InvalidArgumentException(
            sprintf("Invalid enum value '%s' for enum type '%s'.", $value, $enumClass)
        );
    }

    /**
     * Cast a value to a Data object type.
     *
     * @param mixed $value
     * @param class-string<self> $dataClass
     * @return self
     * @throws InvalidArgumentException
     */
    private static function castToDataObject(mixed $value, string $dataClass): self
    {
        if ($value instanceof $dataClass) {
            /** @var self $value */
            return $value;
        }

        if (is_array($value)) {
            return $dataClass::from($value);
        }

        throw new InvalidArgumentException(
            sprintf(
                "Invalid value of type '%s' for type '%s', expected array in %s::from() method.",
                is_object($value) ? get_class($value) : gettype($value),
                $dataClass,
                static::class
            )
        );
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this as $propertyName => $value) {
            if (is_iterable($value)) {
                $result[$propertyName] = array_map(
                    fn($item) => $this->itemToArray($item),
                    is_array($value) ? $value : iterator_to_array($value)
                );
                continue;
            }

            $result[$propertyName] = $this->itemToArray($value);
        }

        return $result;
    }

    /**
     * Convert the object to a json string.
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Convert an item to its array representation.
     *
     * @param mixed $item
     * @return mixed
     */
    private function itemToArray(mixed $item): mixed
    {
        if ($item instanceof UnitEnum) {
            return $item instanceof BackedEnum ? $item->value : $item->name;
        }

        if ($item instanceof self || (is_object($item) && method_exists($item, 'toArray'))) {
            return $item->toArray();
        }

        return $item;
    }

    /**
     * ArrayAccess implementation
     */

    /**
     * Whether an offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    /**
     * Get an offset
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return property_exists($this, $offset) ? $this->{$offset} : null;
    }

    /**
     * Set an offset
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!property_exists($this, $offset)) {
            throw new InvalidArgumentException(
                sprintf("Cannot set non-existent property '%s' in %s.", $offset, static::class)
            );
        }

        $this->{$offset} = $value;
    }

    /**
     * Unset an offset
     *
     * @param mixed $offset
     * @return void
     * @throws InvalidArgumentException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new InvalidArgumentException(
            sprintf("Cannot unset property '%s' in %s. Properties in Data objects are immutable.", $offset, static::class)
        );
    }
}
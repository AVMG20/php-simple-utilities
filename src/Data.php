<?php
declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use UnitEnum;

/**
 * Base DTO Data class
 *
 * This class provides a base implementation for Data Transfer Objects (DTOs).
 * It includes a static factory method for creating new instances from an array of attributes,
 */
abstract class Data implements JsonSerializable
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
        $args = [];

        if ($constructor) {
            // Loop through all parameters of the constructor
            foreach ($constructor->getParameters() as $parameter) {

                // Get the value for each parameter from the attributes array
                $value = self::getValue($parameter, $attributes);

                // Determine the value based on the parameter type
                $value = self::castValue($parameter, $value);

                $args[] = $value;
            }
        }

        return $reflectionClass->newInstanceArgs($args);
    }

    /**
     * Get the value for each parameter from the attributes array.
     *
     * @param ReflectionParameter $parameter The parameter to get the value for.
     * @param array $attributes The source attributes for creating the data object.
     * @return mixed Returns the value for the parameter.
     * @throws ReflectionException Throws an exception if the parameter is required but not found in the attributes array.
     */
    protected static function getValue(ReflectionParameter $parameter, array $attributes): mixed
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        $isNullable = $type && $type->allowsNull();
        $hasDefaultValue = $parameter->isDefaultValueAvailable();
        $isOptional = $hasDefaultValue || $isNullable;

        // Check if the parameter is included in the provided attributes
        if (!array_key_exists($name, $attributes)) {

            // Check if the parameter that was not found in the provided attributes is optional
            if ($isOptional) {
                // Return default value or null if parameter is optional
                return $isNullable && !$hasDefaultValue ? null : $parameter->getDefaultValue();
            }

            // Throw an exception if the parameter is required but not found in the attributes array
            throw new InvalidArgumentException("Missing required attribute: '{$name}' in " . static::class . "::from() method.");
        }

        // Return the value from the attributes array
        return $attributes[$name];
    }

    /**
     * Cast the value to the requested type.
     * We let the reflection system handle casting built-in types.
     *
     * @param ReflectionParameter $parameter The parameter to cast the value for.
     * @param mixed $value The value to cast.
     * @return mixed|static Returns the cast value.
     * @throws ReflectionException
     */
    protected static function castValue(ReflectionParameter $parameter, $value)
    {
        $type = $parameter->getType();

        if ($type && !$type->isBuiltin() && $value !== null) {
            $typeName = $type->getName();

            // Cast to enum
            if (enum_exists($typeName)) {
                // Bypass casting if value is already of the correct enum type
                /** @var UnitEnum|BackedEnum $typeName */
                if ($value instanceof $typeName) {
                    return $value;
                }
                // Attempt to cast to enum
                if ($enum = $typeName::tryFrom($value)) {
                    return $enum;
                } else {
                    throw new InvalidArgumentException("Invalid enum value '{$value}' for enum type '{$typeName}'.");
                }
            }

            // Cast to Data object
            if (is_subclass_of($typeName, self::class)) {
                /** @var static $typeName */ // check if value is not already of type data object.
                // if the user has already initialized the data object, we just return it
                // else create a new data object from the array, allowing the user to choose how to nest data objects
                if ($value instanceof $typeName) {
                    // return already initialized data object
                    return $value;
                }

                if (is_array($value)) {
                    // initialize data object from array
                    return $typeName::from($value);
                }

                // Throw InvalidArgumentException if value is not an array or Data object
                if (is_object($value)) {
                    $type = get_class($value);
                    throw new InvalidArgumentException("Invalid value of type '{$type}' for type '{$typeName}', expected array. in " . static::class . "::from() method.");
                } else {
                    throw new InvalidArgumentException("Invalid value '{$value}' for type '{$typeName}', expected array. in " . static::class . "::from() method.");
                }
            }

            // Just return value if no cast is found
            return $value;
        }

        // return value if type is built-in or value is null
        return $value;
    }


    /**
     * Convert the object to an array, including all nested Data objects.
     *
     * @return array The object converted to an array.
     */
    public function toArray(): array {
        $result = [];

        // Loop through all properties of the object
        foreach ($this as $propertyName => $value) {

            // Convert UnitEnum to string
            if ($value instanceof UnitEnum) {
                $result[$propertyName] = $value instanceof BackedEnum ? $value->value : $value->name;
                continue; // value converted, skip to next property
            }

            // Recursively convert nested Data objects or iterables to arrays
            if (is_iterable($value)) {

                // Convert all items in the array
                $result[$propertyName] = array_map(function ($item) {

                    // Convert UnitEnum to string
                    if ($item instanceof UnitEnum) {
                        return $item instanceof BackedEnum ? $item->value : $item->name;
                    }

                    // Recursively convert nested Data objects to arrays
                    if ($item instanceof self) {
                        return $item->toArray();
                    }

                    // Convert Data objects in arrays to arrays
                    if (is_object($item) && method_exists($item, 'toArray')) {
                        return $item->toArray();
                    }

                    // Just return the value if no conversion method is found
                    return $item;
                }, is_array($value) ? $value : iterator_to_array($value));

                continue; // value converted, skip to next property
            }

            // Convert object to array if it has a toArray method
            if (is_object($value) && method_exists($value, 'toArray')) {
                $result[$propertyName] = $value->toArray();
                continue; // value converted, skip to next property
            }

            // Just set the value if no conversion method is found
            $result[$propertyName] = $value;
        }

        return $result;
    }

    /**
     * Specify data which should be serialized to JSON.
     * Utilizes the toArray method for JSON conversion.
     *
     * @return mixed Data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
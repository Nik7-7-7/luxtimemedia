<?php

namespace AIKit\Dependencies\Qdrant\Models\Traits;

use InvalidArgumentException;
use ReflectionProperty;

/**
 * Trait ProtectedPropertyAccessor
 *
 * Allows access to protected properties through a magic getter method.
 */
trait ProtectedPropertyAccessor
{
    /**
     * Magic method to implement generic getter functionality for protected properties.
     *
     * @param string $method The name of the method being called.
     * @param array $arguments The arguments used to invoke the method.
     * @return mixed The value of the property.
     * @throws InvalidArgumentException if the property doesn't exist or is not protected.
     */
    public function __call(string $method, array $arguments)
    {
        $prefix = 'get';

        if ($this->startsWith($method, $prefix)) {
            $property = lcfirst(substr($method, strlen($prefix)));

            if (property_exists($this, $property)) {
                $reflection = new ReflectionProperty($this, $property);
                if ($reflection->isProtected()) {
                    return $this->$property;
                } else {
                    throw new InvalidArgumentException("Access to property '$property' is not allowed");
                }
            }

            throw new InvalidArgumentException("Property '$property' does not exist");
        }
    }

    public function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
}

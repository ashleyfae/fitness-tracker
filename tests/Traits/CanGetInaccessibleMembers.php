<?php
/**
 * CanGetInaccessibleMembers.php
 *
 * @package   fitness-tracker
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace Tests\Traits;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait CanGetInaccessibleMembers
{
    /**
     * Makes a protected method public for the given class, so it can be tested.
     *
     * @param  string|object  $class  Class name or instance of it.
     * @param  string  $methodName  Name of the method.
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getInaccessibleMethod(string|object $class, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($class);

        $method = $class->getMethod($methodName);

        return $method;
    }

    /**
     * Invokes an inaccessible method and returns the result.
     *
     * @param  object  $class  Instance of the class.
     * @param  string  $methodName  Name of the method.
     * @param  mixed  ...$args  Arguments to pass to the method.
     *
     * @return mixed
     * @throws ReflectionException
     */
    protected function invokeInaccessibleMethod($class, string $methodName, ...$args): mixed
    {
        return $this->getInaccessibleMethod($class, $methodName)
            ->invoke($class, ...$args);
    }

    /**
     * Makes a protected property public for the given class, so it can be tested.
     *
     * @param  string|object  $class  Class name or instance of it.
     * @param  string  $propertyName  Name of the property.
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    protected function getInaccessibleProperty(string|object $class, string $propertyName): ReflectionProperty
    {
        $class = new ReflectionClass($class);

        $property = $class->getProperty($propertyName);

        return $property;
    }

    /**
     * Sets the value of a protected property.
     *
     * @param  object  $classInstance  Instance of the class.
     * @param  string  $propertyName  Name of the property.
     * @param  mixed  $propertyValue  Desired property value.
     *
     * @return void
     * @throws ReflectionException
     */
    protected function setInaccessibleProperty(object $classInstance, string $propertyName, $propertyValue): void
    {
        $class = new ReflectionClass($classInstance);

        $property = $class->getProperty($propertyName);
        $property->setValue($classInstance, $propertyValue);
    }
}

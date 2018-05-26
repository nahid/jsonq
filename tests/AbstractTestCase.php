<?php

namespace Nahid\JsonQ\Tests;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Make private and protected function callable
     *
     * @param mixed $object
     * @param string $function
     * @return \ReflectionMethod
     */
    protected function makeCallable($object, $function)
    {
        $method = new \ReflectionMethod($object, $function);
        $method->setAccessible(true);

        return $method;
    }
}

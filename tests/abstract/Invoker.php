<?php

/**
 * Call protected/private method of a class.
 *
 * @param object &$object    Instantiated object that we will run method on.
 * @param string $methodName Method name to call
 * @param array  $parameters Array of parameters to pass into method.
 *
 * @return mixed Method return.
 */
function invokeMethod(&$object, $methodName, array $parameters = array())
{
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $access = $method->isPublic() ? true : false;
    $method->setAccessible(true);
    $result = $method->invokeArgs($object, $parameters);
    $method->setAccessible($access);
    return $result;
}


/**
 * Get protected/private property of a class.
 * 
 * @param object &$object      Instantiated object that we will run method on.
 * @param string $propertyName Property name to get
 * 
 * @return mixed Property value.
 */
function getProperty(&$object, $propertyName)
{
    $reflection = new \ReflectionClass(get_class($object));
    $property = $reflection->getProperty($propertyName);
    $access = $property->isPublic() ? true : false;
    $property->setAccessible(true);
    $result = $property->getValue($object);
    $property->setAccessible($access);
    return $result;
}

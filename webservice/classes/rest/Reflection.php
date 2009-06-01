<?php

/**
 * Rest_Reflection_Function
 */
require_once 'classes/rest/reflection/Function.php';

/**
 * Rest_Reflection_Class
 */
require_once 'classes/rest/reflection/Class.php';

/**
 * Reflection for determining method signatures to use with server classes
 *
 */
class Rest_Reflection
{
    /**
     * Perform class reflection to create dispatch signatures
     *
     * Creates a {@link Rest_Reflection_Class} object for the class or
     * object provided.
     *
     * If extra arguments should be passed to dispatchable methods, these may
     * be provided as an array to $argv.
     *
     * @param string|object $class Class name or object
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * method name (used for the signature key). Primarily to avoid collisions,
     * also for XmlRpc namespacing
     * @return Rest_Reflection_Class
     * @throws Rest_Exception
     *
     *
     */

    public static function reflectClass($class, $argv = false, $namespace = '')
    {
            $reflection = new ReflectionClass($class);

            //require_once 'Exception.php';
            //throw new Rest_Exception('Invalid class or object passed to attachClass()');

        if ($argv && !is_array($argv)) {
            require_once 'Exception.php';
            throw new Rest_Exception('Invalid argv argument passed to reflectClass');
        }

        return new Rest_Reflection_Class($reflection, $namespace, $argv);
    }

    /**
     * Perform function reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for a function. It returns a
     * {@link Rest_Reflection_Function} object.
     *
     * If extra arguments should be passed to the dispatchable function, these
     * may be provided as an array to $argv.
     *
     * @param string $function Function name
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the
     * function name (used for the signature key). Primarily to avoid
     * collisions, also for XmlRpc namespacing
     * @return Rest_Reflection_Function
     * @throws Rest_Exception
     */
    public static function reflectFunction($function, $argv = false, $namespace = '')
    {
        if (!is_string($function) || !function_exists($function)) {
            require_once 'Exception.php';
            throw new Rest_Exception('Invalid function "' . $function . '" passed to reflectFunction');
        }


        if ($argv && !is_array($argv)) {
            require_once 'Exception.php';
            throw new Rest_Exception('Invalid argv argument passed to reflectClass');
        }

        return new Rest_Reflection_Function(new ReflectionFunction($function), $namespace, $argv);
    }
}

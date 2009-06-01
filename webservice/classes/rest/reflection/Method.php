<?php

/**
 * Rest_Reflection_Function_Abstract
 */
require_once 'classes/rest/reflection/function/Abstract.php';

/**
 * Method Reflection
 *
 *
 */
class Rest_Reflection_Method extends Rest_Reflection_Function_Abstract
{
    /**
     * Parent class name
     * @var string
     */
    protected $_class;

    /**
     * Parent class reflection
     * @var Rest_Reflection_Class
     */
    protected $_classReflection;

    /**
     * Constructor
     *
     * @param Rest_Reflection_Class $class
     * @param ReflectionMethod $r
     * @param string $namespace
     * @param array $argv
     * @return void
     */
    public function __construct(Rest_Reflection_Class $class, ReflectionMethod $r, $namespace = null, $argv = array())
    {
        $this->_classReflection = $class;
        $this->_reflection      = $r;

        $classNamespace = $class->getNamespace();

        // Determine namespace
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        } elseif (!empty($classNamespace)) {
            $this->setNamespace($classNamespace);
        }

        // Determine arguments
        if (is_array($argv)) {
            $this->_argv = $argv;
        }

        // If method call, need to store some info on the class
        $this->_class = $class->getName();

        // Perform some introspection
        $this->_reflect();
    }

    /**
     * Return the reflection for the class that defines this method
     *
     * @return Rest_Reflection_Class
     */
    public function getDeclaringClass()
    {
        return $this->_classReflection;
    }

    /**
     * Wakeup from serialization
     *
     * Reflection needs explicit instantiation to work correctly. Re-instantiate
     * reflection object on wakeup.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_classReflection = new Rest_Reflection_Class(new ReflectionClass($this->_class), $this->getNamespace(), $this->getInvokeArguments());
        $this->_reflection = new ReflectionMethod($this->_classReflection->getName(), $this->getName());
    }

}

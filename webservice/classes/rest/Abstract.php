<?php


/** Rest_Interface */
require_once 'classes/rest/Interface.php';

/**
 * Rest_Definition
 */
require_once 'classes/rest/Definition.php';

/**
 * Rest_Method_Definition
 */
require_once 'classes/rest/method/Definition.php';

/**
 * Rest_Method_Callback
 */
require_once 'classes/rest/method/Callback.php';

/**
 * Rest_Method_Prototype
 */
require_once 'classes/rest/method/Prototype.php';

/**
 * Rest_Method_Parameter
 */
require_once 'classes/rest/method/Parameter.php';

/**
 * Rest_Abstract
 *
 *
 */
abstract class Rest_Abstract implements Rest_Interface
{
    /**
     * @deprecated
     * @var array List of PHP magic methods (lowercased)
     */
    protected static $magic_methods = array(
        '__call',
        '__clone',
        '__construct',
        '__destruct',
        '__get',
        '__isset',
        '__set',
        '__set_state',
        '__sleep',
        '__tostring',
        '__unset',
        '__wakeup',
    );

    /**
     * @var bool Flag; whether or not overwriting existing methods is allowed
     */
    protected $_overwriteExistingMethods = false;

    /**
     * @var Rest_Definition
     */
    protected $_table;

    /**
     * Constructor
     *
     * Setup server description
     *
     * @return void
     */
    public function __construct()
    {
        $this->_table = new Rest_Definition();
        $this->_table->setOverwriteExistingMethods($this->_overwriteExistingMethods);
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an array of method definitions.
     *
     * @return Rest_Definition
     */
    public function getFunctions()
    {
        return $this->_table;
    }

    /**
     * Lowercase a string
     *
     * Lowercase's a string by reference
     *
     * @deprecated
     * @param  string $string value
     * @param  string $key
     * @return string Lower cased string
     */
    public static function lowerCase(&$value, &$key)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . '() is deprecated and will be removed in a future version', E_USER_NOTICE);
        return $value = strtolower($value);
    }

    /**
     * Build callback for method signature
     *
     * @param  Rest_Reflection_Function_Abstract $reflection
     * @return Rest_Method_Callback
     */
    protected function _buildCallback(Rest_Reflection_Function_Abstract $reflection)
    {
        $callback = new Rest_Method_Callback();
        if ($reflection instanceof Rest_Reflection_Method) {
            $callback->setType($reflection->isStatic() ? 'static' : 'instance')
                     ->setClass($reflection->getDeclaringClass()->getName())
                     ->setMethod($reflection->getName());
        } elseif ($reflection instanceof Rest_Reflection_Function) {
            $callback->setType('function')
                     ->setFunction($reflection->getName());
        }
        return $callback;
    }

    /**
     * Build a method signature
     *
     * @param  Rest_Reflection_Function_Abstract $reflection
     * @param  null|string|object $class
     * @return Rest_Method_Definition
     * @throws Rest_Exception on duplicate entry
     */
    protected function _buildSignature(Rest_Reflection_Function_Abstract $reflection, $class = null)
    {
        $ns         = $reflection->getNamespace();
        $name       = $reflection->getName();
        $method     = empty($ns) ? $name : $ns . '.' . $name;

        if (!$this->_overwriteExistingMethods && $this->_table->hasMethod($method)) {
            require_once 'Exception.php';
            throw new Rest_Exception('Duplicate method registered: ' . $method);
        }

        $definition = new Rest_Method_Definition();
        $definition->setName($method)
                   ->setCallback($this->_buildCallback($reflection))
                   ->setMethodHelp($reflection->getDescription())
                   ->setInvokeArguments($reflection->getInvokeArguments());

        foreach ($reflection->getPrototypes() as $proto) {
            $prototype = new Rest_Method_Prototype();
            $prototype->setReturnType($this->_fixType($proto->getReturnType()));
            foreach ($proto->getParameters() as $parameter) {
                $param = new Rest_Method_Parameter(array(
                    'type'     => $this->_fixType($parameter->getType()),
                    'name'     => $parameter->getName(),
                    'optional' => $parameter->isOptional(),
                ));
                if ($parameter->isDefaultValueAvailable()) {
                    $param->setDefaultValue($parameter->getDefaultValue());
                }
                $prototype->addParameter($param);
            }
            $definition->addPrototype($prototype);
        }
        if (is_object($class)) {
            $definition->setObject($class);
        }
        $this->_table->addMethod($definition);
        return $definition;
    }

    /**
     * Dispatch method
     *
     * @param  Rest_Method_Definition $invocable
     * @param  array $params
     * @return mixed
     */
    protected function _dispatch(Rest_Method_Definition $invocable, array $params)
    {
        $callback = $invocable->getCallback();
        $type     = $callback->getType();

        if ('function' == $type) {
            $function = $callback->getFunction();
            return call_user_func_array($function, $params);
        }

        $class  = $callback->getClass();
        $method = $callback->getMethod();

        if ('static' == $type) {
            return call_user_func_array(array($class, $method), $params);
        }

        $object = $invocable->getObject();
        if (!is_object($object)) {
            $invokeArgs = $invocable->getInvokeArguments();
            if (!empty($invokeArgs)) {
                $reflection = new ReflectionClass($class);
                $object     = $reflection->newInstanceArgs($invokeArgs);
            } else {
                $object = new $class;
            }
        }
        return call_user_func_array(array($object, $method), $params);
    }

    /**
     * Map PHP type to protocol type
     *
     * @param  string $type
     * @return string
     */
    abstract protected function _fixType($type);
}

<?php

/**
 * Rest_Reflection_ReturnValue
 */
require_once 'classes/rest/reflection/ReturnValue.php';

/**
 * Rest_Reflection_Parameter
 */
require_once 'classes/rest/reflection/Parameter.php';

/**
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 *
 */
class Rest_Reflection_Prototype
{
    /**
     * Constructor
     *
     * @param Reflection_ReturnValue $return
     * @param array $params
     * @return void
     */
    public function __construct(Rest_Reflection_ReturnValue $return, $params = null)
    {
        $this->_return = $return;

        if (!is_array($params) && (null !== $params)) {
            require_once 'rest/Exception.php';
            throw new Rest_Exception('Invalid parameters');
        }

        if (is_array($params)) {
            foreach ($params as $param) {
                if (!$param instanceof Rest_Reflection_Parameter) {
                    require_once 'rest/Exception.php';
                    throw new Rest_Exception('One or more params are invalid');
                }
            }
        }

        $this->_params = $params;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->_return->getType();
    }

    /**
     * Retrieve the return value object
     *
     * @access public
     * @return Reflection_ReturnValue
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Retrieve method parameters
     *
     * @return array Array of {@link Reflection_Parameter}s
     */
    public function getParameters()
    {
        return $this->_params;
    }
}

<?php

/**
 * Return value reflection
 *
 * Stores the return value type and description
 *
 *
 */
class Rest_Reflection_ReturnValue
{
    /**
     * Return value type
     * @var string
     */
    protected $_type;

    /**
     * Return value description
     * @var string
     */
    protected $_description;

    /**
     * Constructor
     *
     * @param string $type Return value type
     * @param string $description Return value type
     */
    public function __construct($type = 'mixed', $description = '')
    {
        $this->setType($type);
        $this->setDescription($description);
    }

    /**
     * Retrieve parameter type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set parameter type
     *
     * @param string|null $type
     * @return void
     */
    public function setType($type)
    {
        if (!is_string($type) && (null !== $type)) {
            require_once 'classes/rest/Exception.php';
            throw new Rest_Exception('Invalid parameter type');
        }

        $this->_type = $type;
    }

    /**
     * Retrieve parameter description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set parameter description
     *
     * @param string|null $description
     * @return void
     */
    public function setDescription($description)
    {
        if (!is_string($description) && (null !== $description)) {
            require_once 'classes/rest/Exception.php';
            throw new Rest_Exception('Invalid parameter description');
        }

        $this->_description = $description;
    }
}

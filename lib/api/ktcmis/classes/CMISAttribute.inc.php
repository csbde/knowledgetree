<?php

/**
 * Defines the rules for an object attribute
 */

class CMISAttribute {
    
    public function __construct()
    {
        // will be implemented in the extending classes
    }
    
    /**
     * Sets the value for the property definition according to the defined rules
     *
     * @params $value the value to be set
     * @throws InvalidArgumentException if the value to be set does not satisfy all the rules
     */
    public function set($value)
    {
        // will be implemented in the extending classes
    }
    
}

?>
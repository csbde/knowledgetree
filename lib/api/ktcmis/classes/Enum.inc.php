<?php

/**
 * Base class for enumerators
 */

// TODO enable creation of enum instances on the fly - this will most likely be done in an extending class

abstract class Enum {
    
    // actual implementation of these will be in child classes
    static private $values;
    static private $value;
    static private $name;
    
    /**
     * Sets the value of the enumerator
     *
     * @param unknown_type $value
     * @throws invalidArgumentException if the given value does not match one of the allowed values
     */
    static protected function set($value)
    {        
        if (!in_array($value, self::$values)) {
            throw new InvalidArgumentException("Unable to set value for $name: Illegal input ($value)");
        }
        
        self::$value = $value;
    }
    
    /**
     * Returns the currently set value, or null if unset
     *
     */
    static protected function get()
    {
        return self::$value;
    }
    
}

?>
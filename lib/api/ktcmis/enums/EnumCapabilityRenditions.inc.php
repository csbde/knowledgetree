<?php

/**
 * Enumaerator class for capabilityRenditions
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/Enum.inc.php');

class EnumCapabilityRenditions extends Enum {
    
    static protected $name = 'capabilityRenditions';
    static protected $values = array('none', 'objectidsonly', 'properties', 'all');
    
    /**
     * Sets the value of the enumerator
     *
     * @param unknown_type $value
     * @throws invalidArgumentException if the given value does not match one of the allowed values
     *         (exception is thrown in parent class function)
     */
    static protected function set($value)
    {
        parent::set($value);
    }
    
}

?>
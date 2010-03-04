<?php

/**
 * Enumaerator class for capabilityACL
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/Enum.inc.php');

class EnumCapabilityACL extends Enum {
    
    static protected $name = 'capabilityACL';
    static protected $values = array('none', 'discover', 'manage');
    
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
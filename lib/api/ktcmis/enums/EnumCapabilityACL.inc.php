<?php

/**
 * Enumaerator class for capabilityACL
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/AbstractEnum.inc.php');

class EnumCapabilityACL extends AbstractEnum {
    
    static private $values = array('none', 'discover', 'manage');
    static private $name = 'capabilityACL';
}

?>
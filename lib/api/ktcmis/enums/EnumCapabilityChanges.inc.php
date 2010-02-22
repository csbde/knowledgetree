<?php

/**
 * Enumaerator class for capabilityChanges
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/AbstractEnum.inc.php');

class EnumCapabilityChanges extends AbstractEnum {
    
    static private $values = array('none', 'objectidsonly', 'properties', 'all');
    static private $name = 'capabilityChanges';
}

?>
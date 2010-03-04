<?php

/**
 * Enumaerator class for capabilityRenditions
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/Enum.inc.php');

class EnumCapabilityRenditions extends Enum {
    
    static private $values = array('none', 'objectidsonly', 'properties', 'all');
    static private $name = 'capabilityRenditions';
}

?>
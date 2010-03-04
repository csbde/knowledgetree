<?php

/**
 * Enumaerator class for capabilityContentStreamUpdatability
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/Enum.inc.php');

class EnumCapabilityContentStreamUpdatability extends Enum {
    
    static private $values = array('none', 'anytime', 'pwconly');
    static private $name = 'capabilityContentStreamUpdatability';
}

?>
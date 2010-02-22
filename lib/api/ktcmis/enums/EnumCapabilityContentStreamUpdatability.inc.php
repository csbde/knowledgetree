<?php

/**
 * Enumaerator class for capabilityContentStreamUpdatability
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/AbstractEnum.inc.php');

class EnumCapabilityContentStreamUpdatability extends AbstractEnum {
    
    static private $values = array('none', 'anytime', 'pwconly');
    static private $name = 'capabilityContentStreamUpdatability';
}

?>
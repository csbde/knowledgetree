<?php

/**
 * Enumaerator class for capabilityQuery
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/AbstractEnum.inc.php');

class EnumCapabilityQuery extends AbstractEnum {
    
    static private $values = array('none', 'metadataonly', 'fulltextonly', 'bothseparate', 'bothcombined');
    static private $name = 'capabilityQuery';
}

?>
<?php

/**
 * Enumaerator class for capabilityJoin
 */

require_once(realpath(dirname(__FILE__) . '/../../../../config/dmsDefaults.php'));

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/classes/AbstractEnum.inc.php');

class EnumCapabilityJoin extends AbstractEnum {
    
    static private $values = array('none', 'inneronly', 'innerandouter');
    static private $name = 'capabilityJoin';
}

?>
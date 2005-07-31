<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

error_reporting(E_ALL);

$aAllowed = array(
    "group" => array(1, 2, 3, 4),
    "user" => array(1, 2, 3, 4),
    "role" => array(1, 2, 3, 4),
);

var_dump(KTPermissionUtil::generateDescriptor($aAllowed));

$aAllowed = array(
    "role" => array(4, 3, 2, 1),
    "group" => array(1, 3, 2, 4),
    "user" => array(2, 3, 1, 4),
);
var_dump(KTPermissionUtil::generateDescriptor($aAllowed));

?>

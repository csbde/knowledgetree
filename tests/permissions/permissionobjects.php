<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");

error_reporting(E_ALL);

$res = KTPermissionObject::createFromArray(array(
));
var_dump($res);

?>

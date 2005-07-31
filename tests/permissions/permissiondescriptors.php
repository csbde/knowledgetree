<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");

error_reporting(E_ALL);

$res = KTPermissionDescriptor::createFromArray(array(
    "descriptortext" => "asdf",
));
var_dump($res);

?>

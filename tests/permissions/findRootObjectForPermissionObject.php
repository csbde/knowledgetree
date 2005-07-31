<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

$oFolder =& Folder::get(2);
$oPO = KTPermissionObject::get($oFolder->getPermissionObjectID());
$res = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
var_dump($res);

?>

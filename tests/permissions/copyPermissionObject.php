<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

$oFolder = Folder::get(19);
var_dump($oFolder->getPermissionObjectID());
KTPermissionUtil::copyPermissionObject($oFolder);
var_dump($oFolder->getPermissionObjectID());

?>

<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

$oFolder = Folder::get(2);
$oUser = User::get(4);
$oPermission = KTPermission::getByName('ktcore.permissions.read');
$res = KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oFolder);
var_dump($res);

?>

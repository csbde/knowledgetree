<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

$oFolder = Folder::get(19);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(20);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(21);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(22);
var_dump($oFolder->getPermissionObjectID());
$oDocument = Document::get(123);
var_dump($oDocument->getPermissionObjectID());

$oFolder = Folder::get(19);
$res = KTPermissionUtil::inheritPermissionObject($oFolder);
if (PEAR::isError($res)) {
    var_dump($res);
}

$oFolder = Folder::get(19);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(20);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(21);
var_dump($oFolder->getPermissionObjectID());
$oFolder = Folder::get(22);
var_dump($oFolder->getPermissionObjectID());
$oDocument = Document::get(123);
var_dump($oDocument->getPermissionObjectID());

?>

<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

error_reporting(E_ALL);

$iFolderId = 43;

$oUser = User::get(1);

$j = 'll';

$oFolder = Folder::get($iFolderId);
var_dump(KTFolderUtil::rename($oFolder, $j . '1', $oUser));
$oFolder = Folder::get($iFolderId);
var_dump(KTFolderUtil::rename($oFolder, $j . '2', $oUser));
$oFolder = Folder::get($iFolderId);
var_dump(KTFolderUtil::rename($oFolder, $j . '3', $oUser));
$oFolder = Folder::get($iFolderId);
var_dump(KTFolderUtil::rename($oFolder, $j . '4', $oUser));

?>
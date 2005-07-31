<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

/*
$aFolders =& Folder::getList();
foreach ($aFolders as $oFolder) {
    KTPermissionUtil::updatePermissionLookup($oFolder);
}
$aDocuments =& Document::getList('permission_object_id IS NOT NULL');
foreach ($aDocuments as $oDocument) {
    KTPermissionUtil::updatePermissionLookup($oDocument);
}
*/
$oFolder = Folder::get(18);
KTPermissionUtil::updatePermissionLookup($oFolder);

?>

<?php

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

$sQuery = "SELECT id FROM $default->folders_table WHERE permission_folder_id = NULL";

$aIDs = DBUtil::getResultArrayKey($sQuery, 'id');

foreach ($aIDs as $iID) {
    $oFolder =& Folder::get($iID);
    $oFolder->calculatePermissionFolder();
    $oFolder->update();
}

?>

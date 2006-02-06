<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

error_reporting(E_ALL);

KTPermissionUtil::updatePermissionLookupForState(2);

?>

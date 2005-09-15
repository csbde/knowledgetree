<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');

$path = "Root Folder/test-move-folder";

DBUtil::runQuery("DELETE FROM documents WHERE full_path LIKE $path%");
DBUtil::runQuery("DELETE FROM folders WHERE full_path LIKE $path%");
DBUtil::runQuery("DELETE FROM folders WHERE full_path = 'Root Folder' AND name = 'test-move-folder'");

?>

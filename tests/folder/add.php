<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');

$oParentFolder = Folder::get(1);
var_dump(KTFolderUtil::_add($oParentFolder, "testfolder", User::get(1)));

?>

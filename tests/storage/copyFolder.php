<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

//error_reporting(E_ALL);

$oSrc = Folder::get(3);
$oDest = Folder::get(2);
$oUser = User::get(1);

var_dump(KTFolderUtil::copy($oSrc, $oDest, $oUser, 'copy test'));

?>
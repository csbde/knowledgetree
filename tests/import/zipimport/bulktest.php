<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/import/zipimportstorage.inc.php');

$fs =& new KTZipImportStorage(KT_DIR .  "/tests/import/dataset2/dataset2.zip");

$oParentFolder =& Folder::get(1);
$oFolder =& Folder::get(3); // KTFolderUtil::_add($oParentFolder, "bulktestfolder", User::get(1));
$oUser =& User::get(1);

$bm =& new KTBulkImportManager($oFolder, $fs, $oUser);

$res = $bm->import();
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res);
    exit(0);
}
var_dump($res);

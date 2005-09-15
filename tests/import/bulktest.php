<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');

$fs =& new KTFSImportStorage(KT_DIR . "/tests/import/dataset1");

$oParentFolder =& Folder::get(1);
$oFolder =& Folder::get(28); // KTFolderUtil::_add($oParentFolder, "bulktestfolder", User::get(1));
$oUser =& User::get(1);

$bm =& new KTBulkImportManager($oFolder, $fs, $oUser);

$res = $bm->import();
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res);
    exit(0);
}
var_dump($res);

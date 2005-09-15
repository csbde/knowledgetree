<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');
require_once(KT_LIB_DIR . '/import/bulkimport.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/users/User.inc');

$fs =& new KTFSImportStorage(KT_DIR . "/tests/folder/move-dataset");

$oRootFolder =& Folder::get(1);
$oUser =& User::get(1);

DBUtil::startTransaction();

$oTestFolder = KTFolderUtil::add($oRootFolder, "test-move-folder", $oUser);
if (PEAR::isError($oTestFolder)) {
    var_dump($oTestFolder); exit(0);
}

$oSrcFolder = KTFolderUtil::add($oTestFolder, "test-src-folder", $oUser);
if (PEAR::isError($oSrcFolder)) {
    var_dump($oSrcFolder); exit(0);
}

$bm =& new KTBulkImportManager($oSrcFolder, $fs, $oUser);

$res = $bm->import();
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res);
    exit(0);
}

$oDstFolder = KTFolderUtil::add($oTestFolder, "test-dst-folder", $oUser);
if (PEAR::isError($oDstFolder)) {
    var_dump($oDstFolder); exit(0);
}

$res = KTFolderUtil::move($oSrcFolder, $oDstFolder, $oUser);
if (PEAR::isError($res)) {
    var_dump($res); exit(0);
}
DBUtil::commit();

?>

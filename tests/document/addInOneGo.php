<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

error_reporting(E_ALL);

$oStorage = KTStorageManagerUtil::getSingleton();

$oFolder =& Folder::get(1);
$oUser =& User::get(1);

$sLocalname = KT_DIR .  "/tests/document/dataset1/critique-of-pure-reason.txt";
$sFilename = $oStorage->tempnam("/tmp", "kt_tests_document_add");
$oStorage->copy($sLocalname, $sFilename);

DBUtil::startTransaction();
$oDocument =& KTDocumentUtil::add($oFolder, "testfullupload2.txt", $oUser, array(
    'contents' => new KTFSFileLike($sFilename), 
    'metadata' => array(),
));

if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
    exit(0);
}
DBUtil::commit();

print "SUCCESS\n";

?>

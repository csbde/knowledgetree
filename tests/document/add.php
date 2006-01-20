<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

error_reporting(E_ALL);

$oFolder =& Folder::get(1);
$oUser =& User::get(1);

$sLocalname = KT_DIR .  "/tests/document/dataset1/critique-of-pure-reason.txt";
$sFilename = tempnam("/tmp", "kt_tests_document_add");
copy($sLocalname, $sFilename);

$oDocument =& KTDocumentUtil::add($oFolder, "testquickupload.txt", $oUser, array(
    'contents' => new KTFSFileLike($sFilename), 
));

if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
    exit(0);
}

if (!file_exists($sFilename)) {
    copy($sLocalname, $sFilename);
}

$oDocument =& KTDocumentUtil::add($oFolder, "newtest2.txt", $oUser, array());
if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
}

$res = KTDocumentUtil::storeContents($oDocument, new KTFSFileLike($sFilename));
var_dump($res);

/*
if (file_exists($sFilename)) {
    unlink($sFilename);
}

$oDocument->setStatusID(LIVE);
$oDocument->update();
*/

?>

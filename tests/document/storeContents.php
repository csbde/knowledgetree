<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

$sLocalname = KT_DIR .  "/tests/document/dataset1/critique-of-pure-reason.txt";
$sFilename = tempnam("/tmp", "kt_tests_document_add");
copy($sLocalname, $sFilename);

$oDocument =& Document::get(207);
if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
}

$res = KTDocumentUtil::storeContents($oDocument, new KTFSFileLike($sFilename));
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res);
    exit(0);
}
// storeContents can update storage_path and also status id
$oDocument->update();

if (file_exists($sFilename)) {
    unlink($sFilename);
}

?>

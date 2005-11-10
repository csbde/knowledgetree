<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

$sLocalname = KT_DIR .  "/tests/document/dataset1/critique-of-pure-reason.txt";
$sFilename = tempnam("/tmp", "kt_tests_document_add");
copy($sLocalname, $sFilename);

$oDocument =& Document::get(6);
if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
}

$res = KTDocumentUtil::updateTransactionText($oDocument);
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res);
    exit(0);
}

?>

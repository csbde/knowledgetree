<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

$oDocument =& Document::get(22);
if (PEAR::isError($oDocument)) {
    print "FAILURE\n";
    var_dump($oDocument);
}

$res = KTDocumentUtil::validateMetadata($oDocument, array());
if (PEAR::isError($res)) {
    print "FAILURE\n";
    var_dump($res->aFailed);
    exit(0);
}

?>

<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

$f = new KTFSImportStorage(KT_DIR . "/tests/import/dataset1");

$rootFiles = array("c");

if ($f->listDocuments("/") !== $rootFiles) {
    print "Root file listing failure\n";
    print "Should be:\n";
    var_dump($rootFiles);
    print "Got:\n";
    var_dump($f->listDocuments("/"));
    exit(0);
}

print "SUCCESS\n";

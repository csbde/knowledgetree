<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

$f = new KTFSImportStorage(KT_DIR . "/tests/import/dataset1");

$rootFolders = array("a");

if ($f->listFolders("/") !== $rootFolders) {
    print "Root folder listing failure\n";
    print "Should be:\n";
    var_dump($rootFolders);
    print "Got:\n";
    var_dump($f->listFolders("/"));
    exit(0);
}

print "SUCCESS\n";

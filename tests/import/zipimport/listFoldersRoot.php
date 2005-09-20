<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/zipimportstorage.inc.php');

$f = new KTZipImportStorage(KT_DIR .  "/tests/import/dataset2/dataset2.zip");
$f->init();

$rootFolders = array("a");

if ($f->listFolders("/") !== $rootFolders) {
    print "Root folder listing failure\n";
    print "Should be:\n";
    var_dump($rootFolders);
    print "Got:\n";
    var_dump($f->listFolders("/"));
    $f->cleanup();
    exit(0);
}

$f->cleanup();
print "SUCCESS\n";

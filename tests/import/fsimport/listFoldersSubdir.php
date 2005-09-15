<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

$f = new KTFSImportStorage(KT_DIR . "/tests/import/dataset1");

$afolders = array("a/d");

if ($f->listFolders("a") !== $afolders) {
    print "Subdir (a) folder listing failure\n";
    print "Should be:\n";
    var_dump($afolders);
    print "Got:\n";
    var_dump($f->listFolders("a"));
    exit(0);
}

print "SUCCESS\n";

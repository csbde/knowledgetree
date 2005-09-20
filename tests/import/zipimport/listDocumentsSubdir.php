<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/zipimportstorage.inc.php');

$f = new KTZipImportStorage(KT_DIR .  "/tests/import/dataset2/dataset2.zip");
$f->init();

$afiles = array("a/b");

if ($f->listDocuments("a") !== $afiles) {
    print "Subdir (a) file listing failure\n";
    print "Should be:\n";
    var_dump($afiles);
    print "Got:\n";
    var_dump($f->listDocuments("a"));
    exit(0);
}
$f->cleanup();

print "SUCCESS\n";

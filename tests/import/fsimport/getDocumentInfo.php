<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

$oStorage = KTStorageManagerUtil::getSingleton();
$f = new KTFSImportStorage(KT_DIR . "/tests/import/dataset1");
$oInfo = $f->getDocumentInfo("a/b");
$norm = $oStorage->file_get_contents(KT_DIR .  '/tests/import/dataset1/a/b');

$gFilename = $oInfo->getFilename();
if ($gFilename !== "b") {
    print "FAILURE\n";
    print "Filename should have been: b\n";
    print "Filename was: " . $gFilename . "\n";
    exit(0);
}

$oFile =& $oInfo->aVersions[0];
$gData = $oFile->get_contents();

if ($norm !== $gData) {
    print "FAILURE\n";
    print "Data doesn't match\n";
    exit(0);
}

print "SUCCESS\n";

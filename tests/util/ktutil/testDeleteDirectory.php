<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$oStorage = KTStorageManagerUtil::getSingleton();
$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = $oStorage->tempnam('/tmp', 'deletedirectory');
$oStorage->unlink($name);
print "copying to $name first\n";
KTUtil::copyDirectory($sSrc, $name);

$name2 = $oStorage->tempnam($name, 'deletedirectory');
KTUtil::copyDirectory($sSrc, $name2);

var_dump($name);
var_dump($name2);
KTUtil::deleteDirectory($name);

?>

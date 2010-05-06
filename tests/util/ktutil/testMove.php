<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$oStorage = KTStorageManagerUtil::getSingleton();
$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = $oStorage->tempnam('/tmp', 'movedirectory');
$oStorage->unlink($name);
print "copying to $name first\n";
KTUtil::copyDirectory($sSrc, $name);

$sSrc = $name;
$name = $oStorage->tempnam('/tmp', 'movedirectory');
$oStorage->unlink($name);
print "moving to $name\n";
KTUtil::moveDirectory($sSrc, $name);

?>

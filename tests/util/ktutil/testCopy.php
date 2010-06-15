<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$oStorage = KTStorageManagerUtil::getSingleton();
$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = $oStorage->tempnam('/tmp', 'copydirectory');
$oStorage->unlink($name);
print $name;
KTUtil::copyDirectory($sSrc, $name);

?>

<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = tempnam('/tmp', 'deletedirectory');
unlink($name);
print "copying to $name first\n";
KTUtil::copyDirectory($sSrc, $name);

$name2 = tempnam($name, 'deletedirectory');
KTUtil::copyDirectory($sSrc, $name2);

var_dump($name);
var_dump($name2);
KTUtil::deleteDirectory($name);

?>

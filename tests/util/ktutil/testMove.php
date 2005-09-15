<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = tempnam('/tmp', 'movedirectory');
unlink($name);
print "copying to $name first\n";
KTUtil::copyDirectory($sSrc, $name);

$sSrc = $name;
$name = tempnam('/tmp', 'movedirectory');
unlink($name);
print "moving to $name\n";
KTUtil::moveDirectory($sSrc, $name);

?>

<?php

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

$sSrc = KT_DIR . '/tests/util/ktutil/dataset1';

$name = tempnam('/tmp', 'copydirectory');
unlink($name);
print $name;
KTUtil::copyDirectory($sSrc, $name);

?>

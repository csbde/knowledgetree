<?php

require_once("../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');

$sFilename = "critique-of-pure-reason.txt";

$norm = file_get_contents($sFilename);
$from = new KTFSFileLike($sFilename);

ob_start();
KTFileLikeUtil::send_contents($from);
$obcont = ob_get_contents();
ob_end_clean();

if ($norm === $obcont) {
    print "SUCCESS\n";
}

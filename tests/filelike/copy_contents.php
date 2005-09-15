<?php

require_once("../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');

$sFilename = "critique-of-pure-reason.txt";

$norm = file_get_contents($sFilename);

$tmpfile = "tmpcc-critique-of-pure-reason.txt";

$from = new KTFSFileLike($sFilename);
$to = new KTFSFileLike($tmpfile);

KTFileLikeUtil::copy_contents($from, $to);

$tocont = file_get_contents($tmpfile);

unlink($tmpfile);

if ($norm === $tocont) {
    print "SUCCESS\n";
}

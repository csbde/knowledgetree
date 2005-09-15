<?php

require_once("../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

$sFilename = "critique-of-pure-reason.txt";

$norm = file_get_contents($sFilename);

$tmpfile = "tmp-critique-of-pure-reason.txt";

$f = new KTFSFileLike($tmpfile);
$f->open("w");
$f->write($norm);
$f->close();

$fcont = file_get_contents($tmpfile);
unlink($tmpfile);

if ($norm === $fcont) {
    print "SUCCESS\n";
}

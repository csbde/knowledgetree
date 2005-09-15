<?php

require_once("../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

$sFilename = "critique-of-pure-reason.txt";

$norm = file_get_contents($sFilename);

$f = new KTFSFileLike($sFilename);

$getcont = $f->get_contents();

$g = new KTFSFileLike($sFilename);
$g->open();
$chunk = "";
while (!$g->eof()) {
    $chunk .= $g->read(8192);
}
$g->close();

if ($getcont !== $norm) {
    print "get_contents not working!\n";
}

if ($chunk !== $norm) {
    print "chunk not working!\n";
}



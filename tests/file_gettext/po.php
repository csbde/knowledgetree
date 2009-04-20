<?php

require_once("../../config/dmsDefaults.php");

require_once('File/Gettext.php');

$aExpected = array(
    '&copy; 2008, 2009 <a href="http://www.knowledgetree.com/">KnowledgeTree Inc.</a>' => '&copy; 2008, 2009 <a href="http://www.knowledgetree.com/">KnowledgeTree Inc.</a> Todos los Derechos Reservados - <a href="http://www.oriondatacenter.com/">Orion Datacenter.</a> Bussines Partner para Colombia',
    'Document "%s" renamed.' => 'El documento "%s" ha sido renombrado.',
    'Document archived: %s' => 'Documento archivados: %s',
    'Document checked in' => 'Documento liberado',
);

$sFilename = "test2.po";

$foo = File_Gettext::factory('po', $sFilename);
$res = $foo->load();
var_dump("t");

foreach ($aExpected as $sSrc => $sExpected) {
    $sGot = $foo->strings[$sSrc];
    if ($sGot != $sExpected) {
        print "Expected $sExpected, but got $sGot\n";
    }
}

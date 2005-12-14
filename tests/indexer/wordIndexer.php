<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_DIR . '/plugins/ktstandard/contents/WordIndexer.php');

$oDocument = Document::get(959);
$oIndexer = new KTWordIndexerTrigger;
$oIndexer->setDocument($oDocument);
$oIndexer->transform();

?>

<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_DIR . '/plugins/ktstandard/contents/PdfIndexer.php');

$oDocument = Document::get(966);
$oIndexer = new KTPdfIndexerTrigger;
$oIndexer->setDocument($oDocument);
$oIndexer->transform();

?>

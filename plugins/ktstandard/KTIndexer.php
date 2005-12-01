<?php

class KTIndexerPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.indexer.plugin";
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTIndexerPlugin', 'ktstandard.indexer.plugin', __FILE__);
$oPlugin =& $oPluginRegistry->getPlugin('ktstandard.indexer.plugin');

$oPlugin->registerTrigger('content', 'transform', 'KTWordIndexerTrigger', 'ktstandard.indexer.triggers.word', 'contents/WordIndexer.php');
$oPlugin->registerTrigger('content', 'transform', 'KTPowerpointIndexerTrigger', 'ktstandard.indexer.triggers.powerpoint', 'contents/PowerpointIndexer.php');
$oPlugin->registerTrigger('content', 'transform', 'KTExcelIndexerTrigger', 'ktstandard.indexer.triggers.excel', 'contents/ExcelIndexer.php');
$oPlugin->registerTrigger('content', 'transform', 'KTTextIndexerTrigger', 'ktstandard.indexer.triggers.txt', 'contents/TextIndexer.php');
$oPlugin->registerTrigger('content', 'transform', 'KTPdfIndexerTrigger', 'ktstandard.indexer.triggers.pdf', 'contents/PdfIndexer.php');
$oPlugin->registerTrigger('content', 'transform', 'KTPostscriptIndexerTrigger', 'ktstandard.indexer.triggers.ps', 'contents/PsIndexer.php');
$oPlugin->register();

?>
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
$oPlugin->register();

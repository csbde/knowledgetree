<?php

class KTIndexerPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.indexer.plugin";

    function setup() {
        $this->registerTrigger('content', 'transform', 'KTWordIndexerTrigger',
                'ktstandard.indexer.triggers.word', 'contents/WordIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTPowerpointIndexerTrigger',
                'ktstandard.indexer.triggers.powerpoint', 'contents/PowerpointIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTExcelIndexerTrigger',
                'ktstandard.indexer.triggers.excel', 'contents/ExcelIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTTextIndexerTrigger',
                'ktstandard.indexer.triggers.txt', 'contents/TextIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTPdfIndexerTrigger',
                'ktstandard.indexer.triggers.pdf', 'contents/PdfIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTPostscriptIndexerTrigger',
                'ktstandard.indexer.triggers.ps', 'contents/PsIndexer.php');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTIndexerPlugin', 'ktstandard.indexer.plugin', __FILE__);

?>

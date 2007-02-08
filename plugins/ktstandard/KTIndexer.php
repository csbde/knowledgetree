<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

class KTIndexerPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.indexer.plugin";
    var $autoRegister = true;
    var $sFriendlyName = null;
    
    function KTIndexerPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Full-text Content Indexing');
        return $res;
    }      

    function setup() {
        $this->registerTrigger('content', 'transform', 'KTWordIndexerTrigger',
                'ktstandard.indexer.triggers.word', 'contents/WordIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTRtfIndexerTrigger',
                'ktstandard.indexer.triggers.rtf', 'contents/RtfIndexer.php');
        if (!OS_WINDOWS) {
            $this->registerTrigger('content', 'transform', 'KTPowerpointIndexerTrigger',
                'ktstandard.indexer.triggers.powerpoint', 'contents/PowerpointIndexer.php');
        }
        $this->registerTrigger('content', 'transform', 'KTExcelIndexerTrigger',
                'ktstandard.indexer.triggers.excel', 'contents/ExcelIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTTextIndexerTrigger',
                'ktstandard.indexer.triggers.txt', 'contents/TextIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTPdfIndexerTrigger',
                'ktstandard.indexer.triggers.pdf', 'contents/PdfIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTPostscriptIndexerTrigger',
                'ktstandard.indexer.triggers.ps', 'contents/PsIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTOpenDocumentIndexerTrigger',
                'ktstandard.indexer.triggers.opendocument', 'contents/OpenDocumentIndexer.php');
        $this->registerTrigger('content', 'transform', 'KTXmlHtmlIndexerTrigger',
                'ktstandard.indexer.triggers.xml', 'contents/XmlHtmlIndexer.php');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTIndexerPlugin', 'ktstandard.indexer.plugin', __FILE__);

?>

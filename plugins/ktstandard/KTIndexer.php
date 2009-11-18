<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
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

        // NEW SEARCH

        //debugger_start_debug();

        /*
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
        */
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTIndexerPlugin', 'ktstandard.indexer.plugin', __FILE__);

?>

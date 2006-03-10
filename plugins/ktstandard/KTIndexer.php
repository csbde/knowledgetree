<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

class KTIndexerPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.indexer.plugin";
    var $autoRegister = true;

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
        $this->registerTrigger('content', 'transform', 'KTOpenDocumentIndexerTrigger',
                'ktstandard.indexer.triggers.opendocument', 'contents/OpenDocumentIndexer.php');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTIndexerPlugin', 'ktstandard.indexer.plugin', __FILE__);

?>

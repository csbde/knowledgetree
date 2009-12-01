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
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class DeletePDFTrigger {
    var $namespace = 'pdf.converter.triggers.delete';
    var $aInfo = null;

    function setInfo($aInfo) {
        $this->aInfo = $aInfo;
    }

    /**
     * On deleting a document, send the document owner and alert creator a notification email
     */
    function postValidate() {
        $oDoc = $this->aInfo['document'];
        $docId = $oDoc->getId();
        $docInfo = array('id' => $docId, 'name' => $oDoc->getName());

        // Delete the pdf document
        global $default;
        $pdfDirectory = $default->pdfDirectory;

        $file = $pdfDirectory .'/'.$docId.'.pdf';

        if(file_exists($file)){
            @unlink($file);
        }
    }
}

class pdfConverterPlugin extends KTPlugin {
    var $sNamespace = 'pdf.converter.processor.plugin';
    var $iVersion = 0;
    var $autoRegister = true;
    var $createSQL = true;

    function pdfConverterPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Document PDF Converter');
        $this->dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->sSQLDir = $this->dir . 'sql' . DIRECTORY_SEPARATOR;
        return $res;
    }

    function setup() {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pdfConverter.php';
        $this->registerProcessor('PDFConverter', 'pdf.converter.processor', $dir);
        $this->registerTrigger('delete', 'postValidate', 'DeletePDFTrigger','pdf.converter.triggers.delete', __FILE__);
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('pdfConverterPlugin', 'pdf.converter.processor.plugin', __FILE__);
?>
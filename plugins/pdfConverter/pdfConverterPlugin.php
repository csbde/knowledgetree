<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
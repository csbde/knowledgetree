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

class pdfConverterPlugin extends KTPlugin {
    var $sNamespace = 'pdf.converter.processor.plugin';
    var $iVersion = 0;

    function pdfConverterPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Document PDF Converter');
        return $res;
    }

    function setup() {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pdfConverter.php';
        $this->registerProcessor('PDFConverter', 'pdf.converter.processor', $dir);
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('pdfConverterPlugin', 'pdf.converter.processor.plugin', __FILE__);
?>
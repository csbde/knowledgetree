<?php
/*
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
require_once(KT_LIB_DIR . '/browse/advancedcolumns.inc.php');

class PreviewColumn extends AdvancedColumn {

    var $namespace = 'ktcore.columns.preview';
    var $sActivation = 'onclick';
    var $sPluginPath = '';

    function PreviewColumn() {
        $this->label = null;

        $oConfig = KTConfig::getSingleton();
        $this->sActivation = $oConfig->get('browse/previewActivation', 'onclick');

        // Get file path
        $this->sPluginPath = 'plugins/ktstandard/documentpreview';
    }

    function renderHeader($sReturnURL) {
        // Get the yui libraries required
        global $main;

        // Get the CSS to render the pop-up
        $main->requireCSSResource($this->sPluginPath.'/resources/container.css');

        // Get the javascript to render the property preview
        $main->requireJSResource($this->sPluginPath.'/resources/preview.js');

        return '&nbsp;';
    }

    function renderData($aDataRow) {
        // only _ever_ show this for documents.
        if ($aDataRow["type"] === "folder") {
            return '&nbsp;';
        }

        $sUrl = KTUtil::kt_url().'/'.$this->sPluginPath.'/documentPreview.php';
        $sDir = KT_DIR;
        $iDelay = 1000; // milliseconds

        $iDocumentId = $aDataRow['document']->getId();
        $sTitle = _kt('Property Preview');
        $sLoading = _kt('Loading...');

        $width = 500;

		// Check for existence of thumbnail plugin
        if (KTPluginUtil::pluginIsActive('thumbnails.generator.processor.plugin')) {
            // hook into thumbnail plugin to get display for thumbnail
            include_once(KT_DIR . '/plugins/thumbnails/thumbnails.php');
            $thumbnailer = new ThumbnailViewlet();
            $thumbnailwidth = $thumbnailer->get_width($iDocumentId);
            $width += $thumbnailwidth + 30;
        }

        //$link = '<a name = "ktP'.$iDocumentId.'" href = "#ktP'.$iDocumentId.'" class="ktAction ktPreview" id = "box_'.$iDocumentId.'" ';
		$link = '<a href = "#browseForm" class="ktAction ktPreview" id = "box_'.$iDocumentId.'" ';
        if($this->sActivation == 'mouse-over'){
            $sJs = "javascript: this.t = setTimeout('showInfo(\'$iDocumentId\', \'$sUrl\', \'$sDir\', \'$sLoading\', $width)', $iDelay);";
            $link .= 'onmouseover = "'.$sJs.'" onmouseout = "clearTimeout(this.t);">';
        }else{
            $sJs = "javascript: showInfo('$iDocumentId', '$sUrl', '$sDir', '$sLoading', $width);";
            $link .= 'onclick = "'.$sJs.'" title="'.$sTitle.'">';
        }

        return $link.$sTitle.'</a>';
    }

    function getName() { return _kt('Property Preview'); }
}

class DocumentPreviewPlugin extends KTPlugin {
    var $sNamespace = 'ktstandard.preview.plugin';

    function DocumentPreviewPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Property Preview Plugin');
        return $res;
    }

    function setup() {
        $this->registerColumn(_kt('Property Preview'), 'ktcore.columns.preview', 'PreviewColumn', 'documentPreviewPlugin.php');

        require_once(KT_LIB_DIR . '/templating/templating.inc.php');
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('documentpreview', '/plugins/ktstandard/documentpreview/templates', 'ktstandard.preview.plugin');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('DocumentPreviewPlugin', 'ktstandard.preview.plugin', __FILE__);
?>

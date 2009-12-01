<?php
/**
 * $Id: $
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

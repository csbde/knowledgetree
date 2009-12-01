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

$kt_dir = $_REQUEST['kt_dir'];
require_once($kt_dir.'/config/dmsDefaults.php');

class DocumentPreview {
    var $_oDocument;
    var $_IDocId;
    var $_iMimeId;
    var $_oFolder;
    var $_iFolderId;

    /**
     * Constructer - creates the document object
     *
     * @param int $iDocumentId The document Id
     * @return
     */
    function DocumentPreview($iId, $type = 'document'){
        if($type == 'folder'){
            // $type should never be a folder.
            $this->_oDocument = false;
            return;
        }
        $oDocument = Document::get($iId);

        if(PEAR::isError($oDocument)){
            $this->_oDocument = false;
            return;
        }

        $this->_oDocument = $oDocument;
        $this->_IDocId = $iId;
        $this->_iMimeId = $oDocument->getMimeTypeID();
        $this->imageMimeTypes = array(10, 27, 37, 38, 39, 71);
    }

    /**
     * Get the document title for the preview
     *
     * @return string The document title and mime icon
     */
    function getTitle(){
        if($this->_oDocument === false){
            return '<b>'._kt('Error').'</b>';
        }
        GLOBAL $default;
        $sIcon = '';

        $sTitle = htmlentities($this->_oDocument->getName(), ENT_NOQUOTES, 'utf-8');
        $iLen = strlen($sTitle);

        if($iLen > 60){
            $sFull = $sTitle;
            if($iLen >= 99){
                $sTitle = substr($sTitle, 0, 97).'...';
            }
            $sTitle = '<h4 title="'.$sFull.'">'.$sTitle.'</h4>';
        }else{
            $sTitle = '<h2>'.$sTitle.'</h2>';
        }

        // Get the icon
        $sIcon = $this->getMimeIcon();

        $sTitle = '<div class="previewhd">
                <div style="float:left">'.$sIcon.'</div>
                <div style="float:left; width: 375px;">'.$sTitle.'</div>
            </div>';
        return $sTitle;
    }

    /**
     * Display the mime type icon.
     *
     * @param unknown_type $iMimeId
     * @return unknown
     */
    function getMimeIcon() {
        global $default;
        $iMimeId = $this->_iMimeId;

        $sIconPath = $this->getIconPath();
        $sIconPath = $default->rootUrl.$sIconPath;
        return "<img src='$sIconPath' title='$sTitle' />&nbsp;&nbsp;";
    }

    /**
     * If there isn't an icon for the given extension, find a generic icon for the type else return the default icon.
     *
     * @param string $ext
     * @return string
     */
    function checkForGeneric($ext) {
        if(in_array($ext, array('py','php'))){
            return 'generic/source';
        }
        if(in_array($ext, array('odt','sxw', 'ott', 'sxt'))){
            return 'generic/wordprocessing';
        }
        if(in_array($ext, array('ods','ots', 'sxc', 'stc'))){
            return 'spreadsheet';
        }
        if(in_array($ext, array('odp','otp', 'sxi', 'sti'))){
            return 'generic/pres';
        }
        if(in_array($ext, array('mp3','m4a'))){
            return 'generic/sound';
        }
        if(in_array($ext, array('m4v'))){
            return 'generic/video';
        }
        return 'default';
    }

    /**
     * Get the path to the correct icon for the mime type
     *
     * @return string
     */
    function getIconPath() {

        $sIconPath = KTMime::getIconPath($this->_iMimeId);

        // Get mime type icon
        $sIconPath = '/resources/mimetypes/big/'.$sIconPath.'.png';

        if(!file_exists(KT_DIR.$sIconPath)){
            // See if there is an icon for the extension
            $sMimeType = KTMime::getMimeTypeName($this->_iMimeId);
            $aMimeInfo = KTMime::getFriendlyNameAndExtension($sMimeType);
            if(!PEAR::isError($aMimeInfo) && !empty($aMimeInfo)){
                $sExt = $aMimeInfo[0]['filetypes'];
                $sIconPath = '/resources/mimetypes/big/'.$sExt.'.png';

                if(!file_exists(KT_DIR.$sIconPath)){
                    $generic = $this->checkForGeneric($sExt);
                    // if all else fails, use the default icon
                    $sIconPath = '/resources/mimetypes/big/'.$generic.'.png';
                }
            }
        }
        return $sIconPath;
    }

    /**
     * Render the info box content
     *
     * @return string
     */
    function renderPreview(){
        if($this->_oDocument === false){
            return '<p>'._kt('A problem occured while loading the property preview.').'</p>';
        }

        $sInfo = $this->getMetadata();

        $sInfo = '<div id="preview" class="preview" onclick="javascript: destroyPanel();">'.$sInfo.'</div>';

        $sInfo .= $this->getThumbnail();

        return $sInfo;
    }

    /**
     * Create a table of the document metadata.
     * Hard coded for the moment
     *
     * @return unknown
     */
    function getMetadata(){
        /* Get document info */

        // Filename
        $sFilenameLb = _kt('Document Filename: ');
        $sFilename = $this->_oDocument->getFileName();

        // Mime type
        $sMimeTypeLb = _kt('File is a: ');
        $iMimeId = $this->_oDocument->getMimeTypeID();
        $sMimeType = KTMime::getMimeTypeName($iMimeId);
        $sMimeType = KTMime::getFriendlyNameForString($sMimeType);

        // Version
        $sVersionLb = _kt('Document Version: ');
        $iVersion = $this->_oDocument->getVersion();

        // Created by
        $sCreatedByLb = _kt('Created by: ');
        $iCreatorId = $this->_oDocument->getCreatorID();
        $sCreated = $this->_oDocument->getCreatedDateTime();
        $oCreator = User::get($iCreatorId);
        $sCreatedBy = $oCreator->getName().' ('.$sCreated.')';

        // Owned by
        $sOwnedByLb =  _kt('Owned by: ');
        $iOwnedId = $this->_oDocument->getOwnerID();
        $oOwner = User::get($iOwnedId);
        $sOwnedBy = $oOwner->getName();

        // Last update by
        $iModifiedId = $this->_oDocument->getModifiedUserId();
        $sLastUpdatedByLb = ''; $sLastUpdatedBy = '';
        if(!empty($iModifiedId)){
            $sLastUpdatedByLb = _kt('Last updated by: ');
            $sModified = $this->_oDocument->getLastModifiedDate();
            $oModifier = User::get($iModifiedId);
            $sLastUpdatedBy = $oModifier->getName().' ('.$sModified.')';
        }

        // Document type
        $sDocTypeLb = _kt('Document Type: ');
        $iDocTypeId = $this->_oDocument->getDocumentTypeID();
        $oDocType = DocumentType::get($iDocTypeId);
        $sDocType = $oDocType->getName();

        // Workflow
        $iWFId = $this->_oDocument->getWorkflowId();
        $sWF = ''; $sWFLb = '';
        if(!empty($iWFId)){
            $sWFLb = _kt('Workflow: ');
            $iWFStateId = $this->_oDocument->getWorkflowStateId();
            $oWF = KTWorkflow::get($iWFId);
            $sWF = $oWF->getHumanName();
            $oWFState = KTWorkflowState::get($iWFStateId);
            $sWF .= ' ('.$oWFState->getHumanName().')';
        }

        // Checked out by
        $sCheckedLb = ''; $sCheckedOutBy = '';
        if($this->_oDocument->getIsCheckedOut()){
            $sCheckedLb = _kt('Checked out by: ');
            $iCheckedID = $this->_oDocument->getCheckedOutUserID();
            $oCheckedUser = User::get($iCheckedID);
            $sCheckedOutBy = $oCheckedUser->getName();
        }

        // Id
        $sIdLb = _kt('Document ID: ');
        $sId = $this->_IDocId;

        /* Create table */

        $sInfo = "<div style='float:left; width:405px;'>
            <table cellspacing='3px' cellpadding='3px' width='405px'>
            <tr><td>{$sFilenameLb}</td><td><b>{$sFilename}</b></td></tr>
            <tr><td>{$sMimeTypeLb}</td><td><b>{$sMimeType}</b></td></tr>
            <tr><td>{$sVersionLb}</td><td><b>{$iVersion}</b></td></tr>
            <tr><td>{$sCreatedByLb}</td><td><b>{$sCreatedBy}</b></td></tr>
            <tr><td>{$sOwnedByLb}</td><td><b>{$sOwnedBy}</b></td></tr>";

        if(!empty($sLastUpdatedBy)){
            $sInfo .= "<tr><td>{$sLastUpdatedByLb}</td><td><b>{$sLastUpdatedBy}</b></td></tr>";
        }
            $sInfo .= "<tr><td>{$sDocTypeLb}</td><td><b>{$sDocType}</b></td></tr>";
        if(!empty($sWF)){
            $sInfo .= "<tr><td>{$sWFLb}</td><td><b>{$sWF}</b></td></tr>";
        }
        if(!empty($sCheckedOutBy)){
            $sInfo .= "<tr><td>{$sCheckedLb}</td><td><b>{$sCheckedOutBy}</b></td></tr>";
        }

        $sInfo .= "<tr><td>{$sIdLb}</td><td><b>{$sId}</b></td></tr>";
        $sInfo .= " </table></div>";

        return $sInfo;
    }

    private function getThumbnail()
    {
        $sInfo = '';
        // Check for existence of thumbnail plugin
        if (KTPluginUtil::pluginIsActive('thumbnails.generator.processor.plugin'))
        {
            // hook into thumbnail plugin to get display for thumbnail
            include_once(KT_DIR . '/plugins/thumbnails/thumbnails.php');
            $thumbnailer = new ThumbnailViewlet();
            $thumbnailer->setDocument($this->_oDocument);
            $thumbnailDisplay = $thumbnailer->renderThumbnail($this->_IDocId);
            if ($thumbnailDisplay != '')
            {
        		$sInfo = "<div>$thumbnailDisplay</div>";
        	}
        }
        return $sInfo;
    }
}

/**
 * Get the document id and render the preview / info box
 */

$iDocumentId = $_REQUEST['fDocumentId'];

$oPreview = new DocumentPreview($iDocumentId);

$sTitle = $oPreview->getTitle();
$sContent = $oPreview->renderPreview();

echo $sTitle.'<br />'.$sContent;
exit;
?>

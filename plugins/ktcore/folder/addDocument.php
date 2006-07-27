<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

class KTFolderAddDocumentAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.addDocument';
    var $_sShowPermission = "ktcore.permissions.write";
    var $oDocumentType = null;

    function getDisplayName() {
        return _kt('Add Document');
    }

    function check() {
        $res = parent::check();
        if (empty($res)) {
            return $res;
        }
        $postExpected = KTUtil::arrayGet($_REQUEST, "postExpected");
        $postReceived = KTUtil::arrayGet($_REQUEST, "postReceived");
        if (!empty($postExpected)) {
            $aErrorOptions = array(
                'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
                'message' => _kt('Upload larger than maximum POST size (post_max_size variable in .htaccess or php.ini)'),
            );
            $this->oValidator->notEmpty($postReceived, $aErrorOptions);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("add document"));
        $this->oPage->setTitle(_kt('Add a document'));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/add');
        
        $aOptions = array('width' => '45');
        
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget(_kt('File'), _kt('The contents of the document to be added to the document management system.'), 'file', "", $this->oPage, true, null, null, $aOptions);
        $add_fields[] = new KTStringWidget(_kt('Title'), _kt('The document title is used as the main name of a document throughout KnowledgeTree.'), 'title', "", $this->oPage, true, null, null, $aOptions);
        


	/* Allows filename change on upload

        $add_fields[] = new KTStringWidget(_kt('New Filename'), _kt('If you wish to upload this file under a different filename, enter it here.'), 'altfilename', "", $this->oPage, false, null, null, $aOptions);
	 */

        
        $aVocab = array('' => _kt('&lt;Please select a document type&gt;'));
        foreach (DocumentType::getListForUserAndFolder($this->oUser, $this->oFolder) as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
            }
        }
        
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget(_kt('Document Type'), _kt('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'), 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

        $fieldsets = array();
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        $activesets = KTFieldset::getGenericFieldsets();
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }

        $oTemplate->setData(array(
            'context' => &$this,
            'add_fields' => $add_fields,
            'generic_fieldsets' => $fieldsets,
        ));
        return $oTemplate->render();
    }

    function do_upload() {
        $this->oPage->setBreadcrumbDetails(_kt("add document"));
        $this->oPage->setTitle(_kt('Add a document'));
        $mpo =& new JavascriptObserver($this);
        // $mpo =& new KTSinglePageObserver(&$this);
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->addObserver($mpo);
        
        require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
        require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
	    'max_str_len' => 200,
        );
        
        $aFile = $this->oValidator->validateFile($_FILES['file'], $aErrorOptions);
        $sTitle = $this->oValidator->validateString($_REQUEST['title'], $aErrorOptions);
	$sAltFilename = KTUtil::arrayGet($_REQUEST, 'altfilename', '');

	if(strlen(trim($sAltFilename))) {
	    $aFile['name'] = $sAltFilename;
	}


        $iFolderId = $this->oFolder->getId();
        /*
        // this is now done in ::add
        if (Document::fileExists(basename($aFile['name']), $iFolderId)) {
            $this->errorRedirectToMain(_kt('There is already a file with that filename in this folder.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
            exit(0);
        }
        
        if (Document::nameExists($sTitle, $iFolderId)) {
            $this->errorRedirectToMain(_kt('There is already a file with that title in this folder.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
            exit(0);
        }
        */
        
        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $aErrorOptions['message'] = _kt("Please select a valid document type");
        $this->oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId'], $aErrorOptions);

        $aOptions = array(
            'contents' => new KTFSFileLike($aFile['tmp_name']),
            'documenttype' => $this->oDocumentType,
            'metadata' => $aFields,
            'description' => $sTitle,
        );

        $mpo->start();
        $this->startTransaction();
        $oDocument =& KTDocumentUtil::add($this->oFolder, basename($aFile['name']), $this->oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }
        $this->addInfoMessage("Document added");

        $mpo->redirectToDocument($oDocument->getId());
        $this->commitTransaction();
        exit(0);
    }

}

?>

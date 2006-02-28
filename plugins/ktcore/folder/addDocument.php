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
        return _('Add Document');
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
                'message' => 'Upload larger than maximum POST size (max_post_size variable in .htaccess or php.ini)',
            );
            $this->oValidator->notEmpty($postReceived, $aErrorOptions);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("add document"));
        $this->oPage->setTitle(_('Add a document'));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/add');
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget(_('File'), _('The contents of the document to be added to the document management system.'), 'file', "", $this->oPage, true);
        $add_fields[] = new KTStringWidget(_('Title'), _('The document title is used as the main name of a document throughout KnowledgeTree.'), 'title', "", $this->oPage, true);

        
        $aVocab = array('' => _('&lt;Please select a document type&gt;'));
        foreach (DocumentType::getList() as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
            }
        }
        
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget(_('Document Type'), _('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'), 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

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
        $this->oPage->setBreadcrumbDetails(_("add document"));
        $this->oPage->setTitle(_('Add a document'));
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
        );
        
        $aFile = $this->oValidator->validateFile($_FILES['file'], $aErrorOptions);
        $sTitle = $this->oValidator->validateString($_REQUEST['title'], $aErrorOptions);
        
        $iFolderId = $this->oFolder->getId();
        /*
        // this is now done in ::add
        if (Document::fileExists(basename($aFile['name']), $iFolderId)) {
            $this->errorRedirectToMain(_('There is already a file with that filename in this folder.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
            exit(0);
        }
        
        if (Document::nameExists($sTitle, $iFolderId)) {
            $this->errorRedirectToMain(_('There is already a file with that title in this folder.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
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

        $aErrorOptions['message'] = _("Please select a valid document type");
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

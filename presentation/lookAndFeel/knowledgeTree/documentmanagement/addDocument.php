<?php
/**
 * $Id$
 *
 * Web interface to adding a document to a folder
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

// KTUtil::extractGPC('fFolderID', 'fStore', 'fDocumentTypeID', 'fName', 'fDependantDocumentID');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DependantDocumentInstance.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/visualpatterns/PatternMetaData.inc');

require_once(KT_DIR . '/presentation/webpageTemplate.inc');

$oStorage =& KTStorageManagerUtil::getSingleton();

class KTAddDocumentDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        if ($_REQUEST['fFolderID']) {
            $_REQUEST['fFolderId'] = $_REQUEST['fFolderID'];
            unset($_REQUEST['fFolderID']);
        }
        $this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);
        $this->oPermission =& $this->oValidator->validatePermissionByName('ktcore.permissions.write');
        $this->validateFolderPermission();
        $this->validatePost();
        return true;
    }

    function validateDocumentType($iId) {
        $this->oDocumentType =& DocumentType::get($iId);
        if (PEAR::isError($this->oDocumentType) || ($this->oDocumentType === false)) {
            $this->errorPage(_("Invalid document type given"));
            exit(0);
        }
    }

    function validateFolderPermission() {
        $oUser =& User::get($_SESSION['userID']);
        if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $this->oPermission, $this->oFolder)) {
            $this->errorPage(_("Permission denied"));
            exit(0);
        }
    }

    function validatePost() {
        $postExpected = KTUtil::arrayGet($_REQUEST, "postExpected");
        $postReceived = KTUtil::arrayGet($_REQUEST, "postReceived");
        
        if (is_null($postExpected)) {
            return;
        }
        
        if (!is_null($postReceived)) {
            return;
        }

        $this->errorPage(_("You tried to upload a file that is larger than the PHP post_max_size setting."));
        exit(0);
    }

    function errorPage($errorMessage) {
        $this->handleOutput($errorMessage);
        exit(0);
    }

    function do_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate("ktcore/document/add");
        $aTypes = $this->getDocumentTypes();
        $iDefaultType = $aTypes[0]->getId();
        $aTemplateData = array(
            'folder_id' => $this->oFolder->getID(),
            'folder_path_array' => $this->oFolder->getPathArray(),
            'document_type_choice' => $this->getDocumentTypeChoice($aTypes, 'getMetadataForType(this.value);'),
            'generic_metadata_fields' => $this->getGenericMetadataFieldsets(),
            'type_metadata_fields' => $this->getTypeMetadataFieldsets($iDefaultType),
        );
        $oTemplate->setData($aTemplateData);
        return $oTemplate->render();
    }

    function getDocumentTypeChoice($aTypes, $onchange = "") {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/document_type_choice');
        $aFields = array(
            'document_types' => $aTypes,
            'onchange' => $onchange,
        );
        return $oTemplate->render($aFields);
    }

    function getGenericMetadataFieldsets() {
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fieldsets");
        $aTemplateData = array(
            'caption' => _('Generic meta data'),
            'empty_message' => _("No Generic Meta Data"),
            'fieldsets' => KTFieldset::getGenericFieldsets(),
        );
        return $oTemplate->render($aTemplateData);
    }

    function getTypeMetadataFieldsets($iDocumentTypeID) {
        $aTemplateData = array(
            'caption' => _('Type specific meta data'),
            'empty_message' => _("No Type Specific Meta Data"),
            'fieldsets' => KTFieldset::getForDocumentType($iDocumentTypeID),
        );
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fieldsets");
        return $oTemplate->render($aTemplateData);
    }

    function getDocumentTypes() {
        if (!$this->oFolder->getRestrictDocumentTypes()) {
            return DocumentType::getList();
        }
        $sTable = KTUtil::getTableName('folder_doctypes');
        $aQuery = array(
            "SELECT document_type_id FROM $sTable WHERE folder_id = ?",
            array($this->oFolder->getId()),
        );
        $aIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');
        $aRet = array();
        foreach ($aIds as $iId) {
            $aRet[] = DocumentType::get($iId);
        }
        return $aRet;
    }

    function do_upload() {
        // make sure the user actually selected a file first
        // and that something was uploaded
        if (!((strlen($_FILES['fFile']['name']) > 0) && $_FILES['fFile']['size'] > 0)) {
            // no uploaded file
            $message = _("You did not select a valid document to upload");

            $errors = array(
               1 => _("The uploaded file is larger than the PHP upload_max_filesize setting"),
               2 => _("The uploaded file is larger than the MAX_FILE_SIZE directive that was specified in the HTML form"),
               3 => _("The uploaded file was not fully uploaded to KnowledgeTree"),
               4 => _("No file was selected to be uploaded to KnowledgeTree"),
               6 => _("An internal error occurred receiving the uploaded document"),
            );
            $message = KTUtil::arrayGet($errors, $_FILES['fFile']['error'], $message);

            if (@ini_get("file_uploads") == false) {
                $message = _("File uploads are disabled in your PHP configuration");
            }
    
            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^emd(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $this->validateDocumentType($_REQUEST['fDocumentTypeID']);

        $aOptions = array(
            'contents' => new KTFSFileLike($_FILES['fFile']['tmp_name']),
            'documenttype' => $this->oDocumentType,
            'metadata' => $aFields,
            'description' => $_REQUEST['fName'],
        );

        $oUser =& User::get($_SESSION["userID"]);
        $oDocument =& KTDocumentUtil::add($this->oFolder, basename($_FILES['fFile']['name']), $oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        //the document was created/uploaded due to a collaboration step in another
        //document and must be linked to that document
        if (isset($fDependantDocumentID)) {
            $oDependantDocument = DependantDocumentInstance::get($fDependantDocumentID);
            $oDocumentLink = & new DocumentLink($oDependantDocument->getParentDocumentID(), $oDocument->getID(), -1); // XXX: KT_LINK_DEPENDENT
            if ($oDocumentLink->create()) {
                //no longer a dependant document, but a linked document
                $oDependantDocument->delete();                         
            } else {
                //an error occured whilst trying to link the two documents automatically.  Email the parent document
                //original to inform him/her that the two documents must be linked manually
                $oParentDocument = Document::get($oDependantDocument->getParentDocumentID());
                $oUserDocCreator = User::get($oParentDocument->getCreatorID());
                
                $sBody = $oUserDocCreator->getName() . ", an error occured whilst attempting to automatically link the document, '" .
                        $oDocument->getName() . "' to the document, '" . $oParentDocument->getName() . "'.  These two documents " .
                        " are meant to be linked for collaboration purposes.  As creator of the document, ' " . $oParentDocument->getName() . "', you are requested to " .
                        "please link them manually by browsing to the parent document, " .
                        generateControllerLink("viewDocument","fDocumentID=" . $oParentDocument->getID(), $oParentDocument->getName()) . 
                        "  and selecting the link button.  " . $oDocument->getName() . " can be found at " . $oDocument->getDisplayPath();
                
                $oEmail = & new Email();
                $oEmail->send($oUserDocCreator->getEmail(), "Automatic document linking failed", $sBody);
                
                //document no longer dependant document, but must be linked manually
                $oDependantDocument->delete();                                    				
            }
        }

        $this->commitTransaction();
        //redirect to the document details page
        controllerRedirect("viewDocument", "fDocumentID=" . $oDocument->getID());
    }
}
$d =& new KTAddDocumentDispatcher;
$d->dispatch();


?>

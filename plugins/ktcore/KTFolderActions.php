<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTFolderAddDocumentAction extends KTFolderAction {
    var $sBuiltInAction = 'addDocument';
    var $sDisplayName = 'Add Document';
    var $sName = 'ktcore.actions.folder.addDocument';

    // var $_sShowPermission = "ktcore.permissions.write";

    function do_main() {
        $this->oPage->setBreadcrumbDetails("add document");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/add');
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget('File', 'The contents of the document to be added to the document management system.', 'file', "", $this->oPage, true);
        $add_fields[] = new KTStringWidget('Title', 'Describe the changes made to the document.', 'title', "", $this->oPage, true);

        $aVocab = array();
        foreach (DocumentType::getList() as $oDocumentType) {
            $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
        }
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget('Document Type', 'FIXME', 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

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
        require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
        require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

        // make sure the user actually selected a file first
        // and that something was uploaded
        if (!((strlen($_FILES['file']['name']) > 0) && $_FILES['file']['size'] > 0)) {
            // no uploaded file
            $message = _("You did not select a valid document to upload");

            $errors = array(
               1 => _("The uploaded file is larger than the PHP upload_max_filesize setting"),
               2 => _("The uploaded file is larger than the MAX_FILE_SIZE directive that was specified in the HTML form"),
               3 => _("The uploaded file was not fully uploaded to KnowledgeTree"),
               4 => _("No file was selected to be uploaded to KnowledgeTree"),
               6 => _("An internal error occurred receiving the uploaded document"),
            );
            $message = KTUtil::arrayGet($errors, $_FILES['file']['error'], $message);

            if (@ini_get("file_uploads") == false) {
                $message = _("File uploads are disabled in your PHP configuration");
            }

            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId']);

        $aOptions = array(
            'contents' => new KTFSFileLike($_FILES['fFile']['tmp_name']),
            'documenttype' => $this->oDocumentType,
            'metadata' => $aFields,
            'description' => $_REQUEST['fName'],
        );

        $oDocument =& KTDocumentUtil::add($this->oFolder, basename($_FILES['file']['name']), $this->oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        $this->commitTransaction();
        //redirect to the document details page
        controllerRedirect("viewDocument", "fDocumentId=" . $oDocument->getID());
    }

}
$oKTActionRegistry->registerAction('folderaction', 'KTFolderAddDocumentAction', 'ktcore.actions.folder.addDocument');

class KTFolderAddFolderAction extends KTBuiltInFolderAction {
    var $sBuiltInAction = 'addFolder';
    var $sDisplayName = 'Add a Folder';
}
$oKTActionRegistry->registerAction('folderaction', 'KTFolderAddFolderAction', 'ktcore.actions.folder.addFolder');
?>

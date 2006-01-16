<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

class KTFolderAddDocumentAction extends KTFolderAction {
    var $sDisplayName = 'Add Document';
    var $sName = 'ktcore.actions.folder.addDocument';

    var $_sShowPermission = "ktcore.permissions.write";
    
    
    var $oDocumentType = null;

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
        $add_fields[] = new KTStringWidget(_('Title'), _('The document title is used as the main name of a document through the KnowledgeTree.'), 'title', "", $this->oPage, true);

        
        $aVocab = array();
        foreach (DocumentType::getList() as $oDocumentType) {
            $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
        }
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget(_('Document Type'), 'FIXME', 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

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

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $this->oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId']);

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

        $mpo->redirectToDocument($oDocument->getId());
        $this->commitTransaction();
        exit(0);
    }

}

?>

<?

require_once(KT_LIB_DIR . "/actions/folderaction.inc.php");
require_once(KT_LIB_DIR . "/import/fsimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");


require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class KTBulkImportFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.bulkImport';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _('Bulk import');
    }

    function getInfo() {
        if (!Permission::userIsSystemAdministrator($this->oUser->getId())) {
            return null;
            
        }
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("bulk import"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/bulkImport');
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_('Path'), _('The path containing the documents to be added to the document management system.'), 'path', "", $this->oPage, true);

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

    function do_import() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $aErrorOptions['message'] = _('Invalid document type provided');
        $oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId'], $aErrorOptions);

        $aErrorOptions['message'] = _('Invalid path provided');
        $sPath = $this->oValidator->validateString($_REQUEST['path'], $aErrorOptions);

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $aOptions = array(
            'documenttype' => $oDocumentType,
            'metadata' => $aFields,
        );

        $po =& new JavascriptObserver($this);
        $po->start();
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->addObserver($po);

        $fs =& new KTFSImportStorage($sPath);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
        DBUtil::startTransaction();
        $res = $bm->import();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $_SESSION["KTErrorMessage"][] = _("Bulk import failed") . ": " . $res->getMessage();
        } else {
            DBUtil::commit();
            $this->addInfoMessage("Bulk import succeeded");
        }

        $po->redirectToFolder($this->oFolder->getId());
        exit(0);
    }
}

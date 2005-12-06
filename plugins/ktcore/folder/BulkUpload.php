<?

require_once(KT_LIB_DIR . "/actions/folderaction.inc.php");
require_once(KT_LIB_DIR . "/import/zipimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class KTBulkUploadFolderAction extends KTFolderAction {
    var $sDisplayName = 'Bulk upload';
    var $sName = 'ktcore.actions.folder.bulkUpload';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = true;

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("bulk upload"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/bulkUpload');
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget(_('Archive file'), _('The archive file containing the documents you wish to add to the document management system.'), 'file', "", $this->oPage, true);

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
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $aErrorOptions['message'] = _('Invalid document type provided');
        $oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId'], $aErrorOptions);

        unset($aErrorOptions['message']);
        $aFile = $this->oValidator->validateFile($_FILES['file'], $aErrorOptions);

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

        $fs =& new KTZipImportStorage($aFile['tmp_name']);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
        $this->startTransaction();
        $res = $bm->import();
        $aErrorOptions['message'] = _("Bulk import failed");
        $this->oValidator->notError($res, $aErrorOptions);

        $this->commitTransaction();
        controllerRedirect("browse", 'fFolderId=' . $this->oFolder->getID());
        exit(0);
    }
}

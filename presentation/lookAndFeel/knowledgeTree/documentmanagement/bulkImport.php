<?php

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_DIR . "/presentation/webpageTemplate.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/visualpatterns/PatternMetaData.inc");

require_once(KT_LIB_DIR . "/import/fsimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class KTBulkImportDispatcher extends KTStandardDispatcher {
    function check() {
        if ($_REQUEST['fFolderID']) {
            $_REQUEST['fFolderId'] = $_REQUEST['fFolderID'];
            unset($_REQUEST['fFolderID']);
        }
        
        $this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);
        $this->oPermission =& $this->oValidator->validatePermissionByName('ktcore.permissions.write');
        $this->oValidator->userHasPermissionOnItem($this->oUser, $this->oPermission, $this->oFolder);
        return true;
    }

    function do_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/import/fs_import');
        $aTypes = $this->getDocumentTypes();
        $iDefaultType = $aTypes[0]->getId();
        $aFields = array(
            'folder_id' => $this->oFolder->getID(),
            'folder_path_array' => $this->oFolder->getPathArray(),
            'document_type_choice' => $this->getDocumentTypeChoice($aTypes, 'getMetadataForType(this.value);'),
            'generic_metadata_fields' => $this->getGenericMetadataFields(),
            'type_metadata_fields' => $this->getTypeMetadataFields($iDefaultType),
        );
        return $oTemplate->render($aFields);
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

    function getGenericMetadataFields() {
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fields");
        $aTemplateData = array(
            'caption' => _('Generic meta data'),
            'empty_message' => _("No Generic Meta Data"),
            'fields' => DocumentField::getList(array('is_generic = ?', array(true))),
        );
        return $oTemplate->render($aTemplateData);
    }

    function getTypeMetadataFields($iDocumentTypeID) {
        global $default;
        /*ok*/ $sQuery = array("SELECT DF.id AS id " .
          "FROM document_fields AS DF LEFT JOIN document_type_fields_link AS DTFL ON DTFL.field_id = DF.id " .
          "WHERE DF.is_generic = ? " .
          "AND DTFL.document_type_id = ?", array(false, $iDocumentTypeID));

        $aIDs = DBUtil::getResultArray($sQuery);

        $aFields = array();
        foreach ($aIDs as $iID) {
            $aFields[] =& call_user_func(array('DocumentField', 'get'), $iID);
        }
        $aTemplateData = array(
            'caption' => _('Type specific meta data'),
            'empty_message' => _("No Type Specific Meta Data"),
            'fields' => $aFields,
        );
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fields");
        return $oTemplate->render($aTemplateData);
    }

    function getDocumentTypes() {
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

    function do_import() {
        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^emd(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $aOptions = array(
            'documenttype' => DocumentType::get($fDocumentTypeID),
            'metadata' => $aFields,
        );

        $fs =& new KTFSImportStorage($_REQUEST['fPath']);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
        DBUtil::startTransaction();
        $res = $bm->import();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $_SESSION["KTErrorMessage"][] = _("Bulk import failed") . ": " . $res->getMessage();
        } else {
            DBUtil::commit();
        }

        controllerRedirect("browse", 'fFolderID=' . $this->oFolder->getID());
        exit(0);
    }
}

$d =& new KTBulkImportDispatcher;
$d->dispatch();

?>

<?php

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_DIR . "/presentation/webpageTemplate.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/visualpatterns/PatternMetaData.inc");

require_once(KT_LIB_DIR . "/import/fsimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");

class KTBulkImportDispatcher extends KTStandardDispatcher {
    function do_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/import/fs_import');
        $oFolder = Folder::get($_REQUEST['fFolderID']);
        $aFields = array(
            'folder_id' => $oFolder->getID(),
            'folder_path_array' => $oFolder->getPathArray(),
            'document_type_choice' => $this->getDocumentTypeChoice('getMetadataForType(this.value);'),
            'generic_metadata_fields' => $this->getGenericMetadataFields(),
            'type_metadata_fields' => $this->getTypeMetadataFields(1),
        );
        return $oTemplate->render($aFields);
    }

    function getDocumentTypeChoice($onchange = "") {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/document_type_choice');
        $aFields = array(
            'document_types' => DocumentType::getList(),
            'onchange' => $onchange,
        );
        return $oTemplate->render($aFields);
    }

    function getGenericMetadataFields() {
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata_fields/editable_metadata_fields");
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
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata_fields/editable_metadata_fields");
        return $oTemplate->render($aTemplateData);
    }

    function do_import() {
        $oFolder =& Folder::get($_REQUEST['fFolderID']);
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
        $oUser =& User::get($_SESSION['userID']);
        $bm =& new KTBulkImportManager($oFolder, $fs, $oUser, $aOptions);
        DBUtil::startTransaction();
        $res = $bm->import();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $_SESSION["KTErrorMessage"][] = _("Bulk import failed") . ": " . $res->getMessage();
        } else {
            DBUtil::commit();
        }

        controllerRedirect("browse", 'fFolderID=' . $oFolder->getID());
        exit(0);
    }

    function handleOutput($data) {
        global $main;
        if (empty($data)) {
            $data = "";
        }
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }
}

$d =& new KTBulkImportDispatcher;
$d->dispatch();

?>

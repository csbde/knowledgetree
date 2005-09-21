<?php

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');

require_once(KT_LIB_DIR . '/visualpatterns/PatternMetaData.inc');

class GetTypeMetadataFieldsDispatcher extends KTDispatcher {
    function do_main() {
        return $this->getTypeMetadataFields($_REQUEST['fDocumentTypeID']);
    }

    function getTypeMetadataFields ($iDocumentTypeID) {
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
}

$f =& new GetTypeMetadataFieldsDispatcher;
$f->dispatch();


?>

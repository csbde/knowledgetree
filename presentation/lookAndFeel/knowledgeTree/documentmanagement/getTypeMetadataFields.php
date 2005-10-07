<?php

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');

require_once(KT_LIB_DIR . '/visualpatterns/PatternMetaData.inc');

class GetTypeMetadataFieldsDispatcher extends KTDispatcher {
    function do_main() {
        return $this->getTypeMetadataFieldsets ($_REQUEST['fDocumentTypeID']);
    }

    function getTypeMetadataFieldsets ($iDocumentTypeID) {
        $aTemplateData = array(
            'caption' => _('Type specific meta data'),
            'empty_message' => _("No Type Specific Meta Data"),
            'fieldsets' => KTFieldSet::getForDocumentType($iDocumentTypeID),
        );
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fieldsets");
        return $oTemplate->render($aTemplateData);
    }
}

$f =& new GetTypeMetadataFieldsDispatcher;
$f->dispatch();


?>

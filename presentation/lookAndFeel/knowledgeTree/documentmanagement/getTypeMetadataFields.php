<?php

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');

class KTSimplePage {
    function requireJSResource() {
    }
}

class GetTypeMetadataFieldsDispatcher extends KTDispatcher {
    function do_main() {
        $this->oPage = new KTSimplePage;
        return $this->getTypeMetadataFieldsets ($_REQUEST['fDocumentTypeID']);
    }

    function getTypeMetadataFieldsets ($iDocumentTypeID) {
        $fieldsets = array();
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        $activesets = KTFieldset::getForDocumentType($iDocumentTypeID);
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }
        $aTemplateData = array(
            'fieldsets' => $fieldsets,
        );
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fieldsets");
        return $oTemplate->render($aTemplateData);
    }
}

$f =& new GetTypeMetadataFieldsDispatcher;
$f->dispatch();


?>

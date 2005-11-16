<?php
require_once("../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class AjaxConditionalDispatcher extends KTStandardDispatcher {
    
    function do_main() {
        return "AJAX Error";
    }

    function handleOutput($data) {
        print $data;
    }

    function do_verifyAndUpdate() {
         header('Content-Type: text/xml');
         $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/conditional_ajax_verifyAndUpdate");
        $aTemplateData = array(
            
        );
        return $oTemplate->render($aTemplateData);
 
    }

    function do_updateFieldset() {
        $GLOBALS['default']->log->error(print_r($_REQUEST, true));
        header('Content-Type: application/xml');
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fieldset']);

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^emd(\d+)$/', $k, $matches)) {
                $aValues[$matches[1]] = $v;
            }
        }

        $aNextFieldValues =& KTMetadataUtil::getNext($oFieldset, $aValues);

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/metadata/chooseFromMetadataLookup');
        $oTemplate->setData(array('aFieldValues' => $aNextFieldValues));
        $GLOBALS['default']->log->debug(print_r($aNextFieldValues, true));
        return $oTemplate->render();
    }
}

$oDispatcher = new AjaxConditionalDispatcher();
$oDispatcher->dispatch();

?>

<?php
require_once("../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentFieldSet.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MDCondition.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class HandleConditionalDispatcher extends KTDispatcher {
    
    function do_main() {
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/widget_fieldset_conditional");
        $aTemplateData = array(
            "fieldset_id" => 3,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    function getMasterFieldInfo($oFieldSet) {
        global $default;
        $masterField = $oFieldSet->getMasterField();
        $sQuery = "SELECT md_cond.document_field_id AS document_field_id, md_cond.metadata_lookup_id AS val, md_lookup.name AS name FROM $default->md_condition_table AS md_cond LEFT JOIN $default->md_condition_chain_table AS md_chain ON (md_cond.id = md_chain.child_condition) LEFT JOIN $default->metadata_table AS md_lookup ON (md_cond.metadata_lookup_id = md_lookup.id) WHERE md_cond.document_field_id = ? ";
        return DBUtil::getResultArray(array($sQuery, array($masterField)));
    }

}

$oDispatcher = new HandleConditionalDispatcher();
$oDispatcher->dispatch();

?>

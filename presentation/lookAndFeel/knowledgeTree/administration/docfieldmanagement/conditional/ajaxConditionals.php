<?php
require_once("../../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");

class AjaxConditionalAdminDispatcher extends KTDispatcher {
    function do_main() {
        return "Ajax Error.";
    }

    // a lot simpler than the standard dispatcher, this DOESN'T include a large amount of "other" stuff ... we are _just_ called to handle 
    // input/output of simple HTML components.
    function handleOutput($data) {
        print $data;
    }

    function do_getMasterFieldForSet() {
        global $default;
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        if (empty($fieldset_id)) {
            return "Ajax error:  No fieldset specified.";
        }   
        
        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
            return "Ajax error:  No such fieldset (".$fieldset_id.")";
        }

        $oField = KTMetadataUtil::getMasterField($oFieldset);
        if (PEAR::isError($oField)) {
            return "Ajax Error (subselect check).";
        }
        $master_field_id = $oField->getId();

        $aFreeLookups = MetaData::getByDocumentField($oField);
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/conditional_ajax_masterfield");
        $aTemplateData = array(
            "master_field_id" => $master_field_id,
            "free_lookups" => $aFreeLookups,
        );

        return $oTemplate->render($aTemplateData);

    }


    function do_getFieldFromSet() {
        global $default;

        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');

        if (empty($fieldset_id)) {
            return "Ajax error:  No fieldset specified.";
        }   

        if (empty($field_id)) {
            return "Ajax error:  No field specified.";
        }   

        
        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
            return "Ajax error:  No such fieldset (".$fieldset_id.")";
        }

        $oField = DocumentField::get($field_id);
        if (PEAR::isError($oField)) {
            return "Ajax error:  No such field (".$field_id.")";
        }

        $master_field_id = $oFieldset->getMasterField();

        

        $sQuery = "SELECT md_look.id AS lookup_id, md_look.name AS lookup_val FROM $default->metadata_table AS md_look 
        WHERE md_look.document_field_id = ?";
        $aParams = array($field_id);
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return "Ajax Error (subselect for lookups).";
        }

        $sQuery = "SELECT md_cond.id AS rule_id, md_cond.name AS rule_name FROM $default->md_condition_table AS md_cond 
        WHERE md_cond.document_field_id = ? AND md_cond.name IS NOT NULL";
        $aParams = array($field_id);
        $rulesets = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($rulesets)) {
            return "Ajax Error (subselect for rulesets).";
        }


        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/conditional_ajax_subfield");
        $aTemplateData = array(
            "field_id" => $field_id,
            "fieldset_id" => $fieldset_id,
            "lookups" => $res,
            "rulesets" => $rulesets,
        );
        return $oTemplate->render($aTemplateData);

    }

}

$oDispatcher = new AjaxConditionalAdminDispatcher();
$oDispatcher->dispatch();

?>

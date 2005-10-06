<?php
require_once("../../../../../../config/dmsDefaults.php");
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
        return "Ajax Error: no action specified.";
    }

    // a lot simpler than the standard dispatcher, this DOESN'T include a large amount of "other" stuff ... we are _just_ called to handle 
    // input/output of simple HTML components.
    function handleOutput($data) {
        print $data;
    }

    /** lookup methods. */

    // get the list of free items for a given column, under a certain parent behaviour.
    function do_getItemList() {
        $parent_behaviour = KTUtil::arrayGet($_REQUEST, 'parent_behaviour'); 
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id'); // 
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id'); 
        
        header('Content-type: application/xml');
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_complex_get_item_list');
        return $oTemplate->render();
    } 
    
    function do_getBehaviourList() {
        $parent_behaviour = KTUtil::arrayGet($_REQUEST, 'parent_behaviour'); 
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id'); 
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id'); 
        
        header('Content-type: application/xml');
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_complex_get_behaviour_list');
        return $oTemplate->render();
    } 
    
    function do_getActiveFields() {
        $parent_behaviour = KTUtil::arrayGet($_REQUEST, 'parent_behaviour'); 
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id'); // 
        
        header('Content-type: application/xml');
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_complex_get_active_fields');
        return $oTemplate->render();
    }

    /** storage methods */
    function do_createBehaviourAndAssign() {
        $parent_behaviour = KTUtil::arrayGet($_REQUEST, 'parent_behaviour'); 
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id'); 
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');  
        $behaviour_name = KTUtil::arrayGet($_REQUEST, 'behaviour_name');  
        $lookups_to_assign = KTUtil::arrayGet($_REQUEST, 'lookups_to_assign'); // array
        
        header('Content-type: application/xml');
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_complex_create_behaviour_and_assign');
        return $oTemplate->render();
    }

    function do_useBehaviourAndAssign() {
        $parent_behaviour = KTUtil::arrayGet($_REQUEST, 'parent_behaviour'); 
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id'); 
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');  
        $behaviour_name = KTUtil::arrayGet($_REQUEST, 'behaviour_id');  
        $lookups_to_assign = KTUtil::arrayGet($_REQUEST, 'lookups_to_assign'); // array
        
        header('Content-type: application/xml');
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_complex_use_behaviour_and_assign');
        return $oTemplate->render();
    }


}

$oDispatcher = new AjaxConditionalAdminDispatcher();
$oDispatcher->dispatch();

?>

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

    function do_storeRelationship() {
        // handle the store, and DON'T give a 500 ;)  does not act on the information.
        $parent_field = KTUtil::arrayGet($_REQUEST, 'parent_field');
        $parent_lookup = KTUtil::arrayGet($_REQUEST, 'parent_lookup');
        $child_lookups = KTUtil::arrayGet($_REQUEST, 'child_lookups');
        
        // child lookups is a nested array. in python it would be:
        // child_lookups = 
        //  {
        //     field_id:[lookup_id, lookup_id],
        //     field_id:[lookup_id, lookup_id],
        //  }
    
    
        print "not implemented.";
        exit(1);
    }

    // do you want the fieldset_id here?
    function do_updateActiveFields() {
        $active_field = KTUtil::arrayGet($_REQUEST, 'active_field'); // field which is "active".
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        
        // REMEMBER TO SET CONTENT-TYPE application/xml
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_simple_update_active_fields');
        
    }
    
    // do you want the fieldset_id here?
    function do_updateActiveLookups() {
        $active_field = KTUtil::arrayGet($_REQUEST, 'active_field'); // field which is "active".
        $selected_lookup = KTUtil::arrayGet($_REQUEST, 'selected_lookup'); // selected value in said field.
        
        // REMEMBER TO SET CONTENT-TYPE application/xml
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_simple_update_active_lookups');
    }

}

$oDispatcher = new AjaxConditionalAdminDispatcher();
$oDispatcher->dispatch();

?>

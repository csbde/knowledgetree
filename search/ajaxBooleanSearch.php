<?php
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");

require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class AjaxBooleanSearchDispatcher extends KTDispatcher {

    function handle_output($data) {
        return $data;
    }
    
    function do_getNewCriteria() {
        $criteriaType = KTUtil::arrayGet($_REQUEST, 'type');
        if (empty($criteriaType)) {
            return 'AJAX Error:  no criteria type specified.';
        } 
        $critObj = Criteria::getCriterionByNumber($criteriaType);
        if (PEAR::isError($critObj)) {
           return 'AJAX Error:  failed to initialise critiria of type "'.$type.'".';
        }
        // NBM:  there appears to be no reason to take $aRequest into searchWidget...
        $noRequest = array();
        return $critObj->searchWidget($noRequest);
    }

    function do_main() {
        return "Ajax Error.  ajaxBooleanSearch::do_main should not be reachable."; 
    }
    
    
}

$oDispatcher = new AjaxBooleanSearchDispatcher();
$oDispatcher->dispatch();

?>

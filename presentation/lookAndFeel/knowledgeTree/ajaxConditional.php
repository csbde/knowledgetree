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

class AjaxConditionalDispatcher extends KTDispatcher {
    
    function do_main() {
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/widget_fieldset_conditional");
        $aTemplateData = array(
            "fieldset_id" => 3,
        );
        return $oTemplate->render($aTemplateData);
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
         return ;
    }

}

$oDispatcher = new AjaxConditionalDispatcher();
$oDispatcher->dispatch();

?>

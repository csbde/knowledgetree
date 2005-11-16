<?php
require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "dashboard";


/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class testKT3 extends KTStandardDispatcher {
    
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'dashboard', 'name' => 'Test the New UI'),
        array('action' => 'test', 'name' => 'Test the New UI'),
        array('action' => 'test', 'name' => 'UI2'),
    );
    
    function do_main() {
        return 'hello world';
    }

    function handleOutput($data) {
        global $main;
        $main->setBreadcrumbs($this->aBreadcrumbs);
        $main->setBreadcrumbDetails("i, for one, welcome our smarty overlords.");
        $main->setPageContents($data);
        $main->render();
    }

    function getMasterFieldInfo($oFieldSet) {
        global $default;
        $masterField = $oFieldSet->getMasterField();
        $sQuery = "SELECT md_cond.document_field_id AS document_field_id, md_cond.metadata_lookup_id AS val, md_lookup.name AS name FROM $default->md_condition_table AS md_cond LEFT JOIN $default->md_condition_chain_table AS md_chain ON (md_cond.id = md_chain.child_condition) LEFT JOIN $default->metadata_table AS md_lookup ON (md_cond.metadata_lookup_id = md_lookup.id) WHERE md_cond.document_field_id = ? ";
        return DBUtil::getResultArray(array($sQuery, array($masterField)));
    }

}

$oDispatcher = new testKT3();
$oDispatcher->dispatch();

?>

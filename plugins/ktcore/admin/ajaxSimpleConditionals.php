<?php
require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";


require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . '/metadata/fieldbehaviour.inc.php');

class AjaxConditionalAdminDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;
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
        global $default;
        $default->log->error(http_build_query($_REQUEST));
        $iFieldsetId = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $parent_field = KTUtil::arrayGet($_REQUEST, 'parent_field');
        $parent_lookup = KTUtil::arrayGet($_REQUEST, 'parent_lookup');
        $child_lookups = KTUtil::arrayGet($_REQUEST, 'child_lookups');
        
        // child lookups is a nested array. in python it would be:
        // child_lookups = 
        //  {
        //     field_id:[lookup_id, lookup_id],
        //     field_id:[lookup_id, lookup_id],
        //  }

        $oFieldset =& KTFieldset::get($iFieldsetId);
        $oFieldset->setIsComplete(false);
        $oFieldset->update();

        $oParentInstance = KTMetadataUtil::getOrCreateValueInstanceForLookup($parent_lookup);
        $iBehaviourId = $oParentInstance->getBehaviourId();
        $oParentMetadata =& MetaData::get($oParentInstance->getFieldValueId());
        if (is_null($iBehaviourId)) {
            $oBehaviour =& KTFieldBehaviour::createFromArray(array(
                'name' => 'autoinstance' . $oParentInstance->getId(),
                'humanname' => 'Auto instance' . $oParentMetadata->getName(),
                'fieldid' => $oParentInstance->getFieldId(),
            ));
        } else {
            $oBehaviour =& KTFieldBehaviour::get($iBehaviourId);
        }
        if (PEAR::isError($oBehaviour)) {
            var_dump($oBehaviour);
            return $oBehaviour;
        }
        $iBehaviourId = $oBehaviour->getId();
        $oParentInstance->setBehaviourId($iBehaviourId);
        $oParentInstance->update();
    
        $sTable = KTUtil::getTableName('field_behaviour_options');
        $aOptions = array('noid' => true);

        $aQuery = array(
            "DELETE FROM $sTable WHERE behaviour_id = ?",
            array($iBehaviourId),
        );
        $res = DBUtil::runQuery($aQuery);

        foreach ($child_lookups as $iFieldId => $aLookups) {
            foreach ($aLookups as $iLookupId) {
                $oValueInstance =& KTMetadataUtil::getOrCreateValueInstanceForLookup($iLookupId);
                if (PEAR::isError($oValueInstance)) {
                    var_dump($oValueInstance);
                    return $oValueInstance;
                }
                $res = DBUtil::autoInsert($sTable, array(
                    'behaviour_id' => $iBehaviourId,
                    'field_id' => $iFieldId,
                    'instance_id' => $oValueInstance->getId(),
                ), $aOptions);
                if (PEAR::isError($res)) {
                    var_dump($res);
                    return $res;
                }
            }
        }
    }

    // do you want the fieldset_id here?
    function do_updateActiveFields() {
        global $default;
        $default->log->error(http_build_query($_REQUEST));
        $active_field = KTUtil::arrayGet($_REQUEST, 'active_field'); // field which is "active".
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        
        // REMEMBER TO SET CONTENT-TYPE application/xml
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_simple_update_active_fields');
        $oTemplate->setData(array(
            'aFieldIds' => KTMetadataUtil::getChildFieldIds($active_field),
        ));
        header("Content-Type: application/xml");
        print $oTemplate->render();
        exit(0);
    }
    
    // do you want the fieldset_id here?
    function do_updateActiveLookups() {
        global $default;
        $default->log->error(http_build_query($_REQUEST));
        $active_field = KTUtil::arrayGet($_REQUEST, 'active_field'); // field which is "active".
        $selected_lookup = KTUtil::arrayGet($_REQUEST, 'selected_lookup'); // selected value in said field.
        
        // REMEMBER TO SET CONTENT-TYPE application/xml
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/ajax_simple_update_active_lookups');

        $aFieldLookups = KTMetadataUtil::getNextValuesForLookup($selected_lookup);

        $oTemplate->setData(array(
            'aFieldLookups' => $aFieldLookups,
        ));

        header("Content-Type: application/xml");
        print $oTemplate->render();
        exit(0);
    }

}

$oDispatcher = new AjaxConditionalAdminDispatcher();
$oDispatcher->dispatch();

?>

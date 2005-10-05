<?php
require_once("../../../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MDCondition.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");



class ManageConditionalDispatcher extends KTStandardDispatcher {
    function do_main() {

        $aFieldsets = KTFieldset::getList("is_conditional = 1");
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/select_fieldset");
        $aTemplateData = array(
            "available_fieldsets" => $aFieldsets,
        );
        return $oTemplate->render($aTemplateData);
    }

    // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editFieldset() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editsimple");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         *  FIXME we fake it here with nested arrays...
         */
        $oFieldset =& KTFieldset::get($fieldset_id);
        $aFields =& $oFieldset->getFields();
        $aTemplateData = array(
            "fieldset_id" => $fieldset_id,
            "aFields" => $aFields,
            "iMasterFieldId" => $aFields[0]->getId(),
        );
        return $oTemplate->render($aTemplateData);
    }



 /** DELETE FROM HERE. */


    function do_newMasterConditionSet() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');

        if (empty($fieldset_id)) {
            return $this->errorRedirectToMain("No fieldset specified.");
        }
        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
           return $this->errorRedirectToMain("Unable to open the specified fieldset.");
        }


        // now we need to get other fields in this set.
        $aOtherFields = DocumentField::getList('parent_fieldset = '.$oFieldset->getId().' AND id != '.$oFieldset->getMasterField());
        if (PEAR::isError($aOtherFields)) {
            $this->errorRedirectToMain("Failed to get field list for table.");
        }
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editsimple");
        $aTemplateData = array(
            "fieldset_id" => $fieldset_id,
            "other_fields" => $aOtherFields,
        );
        return $oTemplate->render($aTemplateData);        
    }
    
    function do_newSubConditionSet() {
        global $default;
        $default->log->debug('SUBCONDITION CREATION: starting.');
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $starting_id = KTUtil::arrayGet($_REQUEST, 'starting_field'); // populated elsewhere.

        if (empty($fieldset_id)) {
            return $this->errorRedirectToMain("No fieldset specified.");
        }
        if (empty($starting_id)) {
            return $this->errorRedirectToMain("No field specified to start from.");
        }
        $default->log->debug('SUBCONDITION CREATION: extracted.');
        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
           return $this->errorRedirectToMain("Unable to open the specified fieldset.");
        }
        $default->log->debug('SUBCONDITION CREATION: validated.');

        // now we need to get other fields in this set.
        $aOtherFields = DocumentField::getList('parent_fieldset = '.$oFieldset->getId().' AND id != '.$oFieldset->getMasterField().' AND id != '.$starting_id);
        if (PEAR::isError($aOtherFields)) {
            $this->errorRedirectToMain("Failed to get field list for table.");
        }
        // FIXME we tableMappings.
        $starting_lookup = DBUtil::getResultArray(array('SELECT id, name FROM metadata_lookup WHERE document_field_id = ? ', array($starting_id)));
        if (PEAR::isError($starting_lookup)) {
            return $this->errorRedirectToMain('invalid starting field.');
        }
        $default->log->debug('SUBCONDITION CREATION: rendering.');
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/new_subchain");
        $aTemplateData = array(
            "fieldset_id" => $fieldset_id,
            "starting_field" => $starting_id,
            "starting_lookup" => $starting_lookup,
            "other_fields" => $aOtherFields,
        );
        return $oTemplate->render($aTemplateData);        
    }
    

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    /* create a "master" (or root) condition for a fieldset.
     * this _must_ be for a "masterfield".
     */

    function do_createMasterChain() {
        global $default;
        $default->log->debug("CREATE MASTER CHAIN: starting.");


        // FIXME:  delete EVERYTHING that chains FROM this item.
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $rule_name = KTUtil::arrayGet($_REQUEST, 'rule_name');
        $base_lookup_id = KTUtil::arrayGet($_REQUEST, 'lookup_id');    // we ARE on the master field, this represents that.

        $default->log->debug("CREATE MASTER CHAIN: lookup_id " .print_r($lookup_id, true));

        $fields_to_attach = KTUtil::arrayGet($_REQUEST, 'fields_to_attach'); // listed as fields_to_attach[], a list.
        if (empty($fields_to_attach)) {
            return $this->errorRedirectToMain("No fields specified.");
        } else {
            // get the list of fields, and their "chained" values.
            // we key this by field.
            $chained_direct_values = array();
            foreach ($fields_to_attach as $field_id) {
                $chained_direct_values[$field_id] = array();
                // we now grab the appropriate values from the form.
                // these will have been input as "direct_values_{$field_id}
                $direct_values = KTUtil::arrayGet($_REQUEST, "direct_values_".$field_id);
                if (empty($direct_values)) {
                    return $this->errorRedirectToMain("Missing input for field ".$field_id);
                } else {
                    foreach ($direct_values as $lookup_id) {
                        // FIXME use MetaData::get() on these to verify their existence.
                        $chained_direct_values[$field_id][] = $lookup_id;
                    }
                }
            }
        }

        $default->log->debug("CREATE MASTER CHAIN: lookup_id (2) " .print_r($lookup_id, true));        
        $rulesets_to_attach = KTUtil::arrayGet($_REQUEST, 'rulesets_to_attach');

        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
            return $this->errorRedirectToMain("Invalid Fieldset.");
        }

        $resObj = MDConditionNode::createFromArray(array(
            "iFieldId" => $oFieldset->getMasterField(),
            "iLookupId" => $base_lookup_id,
            "sName" => $rule_name,
        ));

        $default->log->debug("CREATE MASTER CHAIN: created master chain node " .print_r($resObj, true));
    
        $resObj2 = MDConditionChain::createFromArray(array(
            "iParentCondition" => null, // this is a MASTER chain.
            "iChildCondition" => $resObj->getId(),
        ));
        
        // the id of this "master rule".  
        $master_rule_id = $resObj->getId(); 

        // walk the connections to make, and ...
        // NBM: please make this transactional...
        foreach ($chained_direct_values as $field_id => $lookup_ids) {
            foreach ($lookup_ids as $lookup_id) {
                $lookupCreation = MDConditionNode::createFromArray(array(
                    "iFieldId" => $field_id,
                    "iLookupId" => $lookup_id,
                ));
                if (PEAR::isError($lookupCreation)) {
                    return $this->errorRedirectToMain("Error creating link to ".$field_id." => ".$lookup_id);
                } 
                $lookupChain = MDConditionChain::createFromArray(array(
                    "iParentCondition" => $master_rule_id,
                    "iChildCondition" => $lookupCreation->getId(),
                ));
                if (PEAR::isError($lookupChain)) {
                    return $this->errorRedirectToMain("Error creating link to ".$field_id." => ".$lookup_id);
                }                 
            }
        }
        if (!empty($rulesets_to_attach)) {
            foreach ($rulesets_to_attach as $child_ruleset) {
                    $lookupChain = MDConditionChain::createFromArray(array(
                        "iParentCondition" => $master_rule_id,
                        "iChildCondition" => $child_ruleset,
                    ));
                    if (PEAR::isError($lookupChain)) {
                        return $this->errorRedirectToMain("Error creating link to ruleset ".$child_ruleset);
                    }                             
            }        
        }
        $default->log->debug("CREATE MASTER CHAIN: done.");
        return $this->errorRedirectToMain("Created ruleset.");
    }

    function do_createSubChain() {
        global $default;
        $default->log->debug("CREATE SB CHAIN: starting.");


        // FIXME:  delete EVERYTHING that chains FROM this item.
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $starting_id = KTUtil::arrayGet($_REQUEST, 'starting_field');
        $rule_name = KTUtil::arrayGet($_REQUEST, 'rule_name');
        
        if (empty($fieldset_id)) {
            return $this->errorRedirectToMain('No fieldset specified.');
        }
        if (empty($rule_name)) {
            return $this->errorRedirectToMain('Sub-rules MUST have a name specified.');
        }
        if (empty($starting_id)) {
            return $this->errorRedirectToMain('Must indicate which fieldset to start with.');
        }
        
        $base_lookup_id = KTUtil::arrayGet($_REQUEST, 'lookup_id');    // we NEED TO KNOW which is the "base" element.

        $default->log->debug("CREATE SB CHAIN: lookup_id " .print_r($base_lookup_id, true));

        // these are the next layers of rules to attach.
        $fields_to_attach = KTUtil::arrayGet($_REQUEST, 'fields_to_attach'); // listed as fields_to_attach[], a list.
        $default->log->debug("CREATE SB CHAIN: fields_to_attach " .print_r($fields_to_attach, true));
        if (empty($fields_to_attach)) {
            return $this->errorRedirectToMain("No fields specified.");
        } else {
            // get the list of fields, and their "chained" values.
            // we key this by field.
            $chained_direct_values = array();
            foreach ($fields_to_attach as $field_id) {
                $chained_direct_values[$field_id] = array();
                // we now grab the appropriate values from the form.
                // these will have been input as "direct_values_{$field_id}
                $direct_values = KTUtil::arrayGet($_REQUEST, "direct_values_".$field_id);
                if (empty($direct_values)) {
                    return $this->errorRedirectToMain("Missing input for field ".$field_id);
                } else {
                    foreach ($direct_values as $lookup_id) {
                        // FIXME use MetaData::get() on these to verify their existence.
                        $chained_direct_values[$field_id][] = $lookup_id;
                    }
                }
            }
        }

        $default->log->debug("CREATE SB CHAIN: lookup_id (2) " .print_r($lookup_id, true));        
        $rulesets_to_attach = KTUtil::arrayGet($_REQUEST, 'rulesets_to_attach');

        $oFieldset = KTFieldset::get($fieldset_id);
        if (PEAR::isError($oFieldset)) {
            return $this->errorRedirectToMain("Invalid Fieldset.");
        }

        $resObj = MDConditionNode::createFromArray(array(
            "iFieldId" => $starting_id,
            "iLookupId" => $base_lookup_id,
            "sName" => $rule_name,
        ));

        $default->log->debug("CREATE SB CHAIN: created master chain node " .print_r($resObj, true));
        
        // WE DON'T CREATE A PARENT-CHAIN ... this one must be picked up by other nodes.
        
        // the id of this "master rule".  
        $master_rule_id = $resObj->getId(); 

        // walk the connections to make, and ...
        // NBM: please make this transactional...
        foreach ($chained_direct_values as $field_id => $lookup_ids) {
            foreach ($lookup_ids as $lookup_id) {
                $lookupCreation = MDConditionNode::createFromArray(array(
                    "iFieldId" => $field_id,
                    "iLookupId" => $lookup_id,
                ));
                if (PEAR::isError($lookupCreation)) {
                    return $this->errorRedirectToMain("Error creating link to ".$field_id." => ".$lookup_id);
                } 
                $lookupChain = MDConditionChain::createFromArray(array(
                    "iParentCondition" => $master_rule_id,
                    "iChildCondition" => $lookupCreation->getId(),
                ));
                if (PEAR::isError($lookupChain)) {
                    return $this->errorRedirectToMain("Error creating link to ".$field_id." => ".$lookup_id);
                }                 
            }
        }
        if (!empty($rulesets_to_attach)) {
            foreach ($rulesets_to_attach as $child_ruleset) {
                    $lookupChain = MDConditionChain::createFromArray(array(
                        "iParentCondition" => $master_rule_id,
                        "iChildCondition" => $child_ruleset,
                    ));
                    if (PEAR::isError($lookupChain)) {
                        return $this->errorRedirectToMain("Error creating link to ruleset ".$child_ruleset);
                    }                             
            }        
        }
        $default->log->debug("CREATE SB CHAIN: done.");
        return $this->errorRedirectToMain("Created ruleset.");
    }

}

$oDispatcher = new ManageConditionalDispatcher();
$oDispatcher->dispatch();

?>

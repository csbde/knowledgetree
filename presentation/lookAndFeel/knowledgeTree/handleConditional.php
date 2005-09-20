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

class HandleConditionalDispatcher extends KTDispatcher {
    function do_main() {
        global $default;
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $active_fields = KTUtil::arrayGet($_REQUEST, 'fields');   


        if (empty($fieldset_id)) { 
            return 'invalid fieldset_id'; 
        }

        $fieldsToRender = array();      // contains "name" => rows.

        $oFieldSet = DocumentFieldSet::get($fieldset_id);
        if (empty($active_fields)) { 
            $res = $this->getMasterFieldInfo($oFieldSet);
            $fieldsToRender[$oFieldSet->getMasterField()] = $res;
        } else {   
            // urgh.  we use this to generate our list of things to extract from the input.
            $pairings = array();
            foreach ($active_fields as $field_id) {
                $current = KTUtil::arrayGet($_REQUEST, 'conditional_field_'.$field_id);
                if ($current === null) {
                    return 'invalid input sequence.';
                }
                else { 
                    $pairings[$field_id] = $current;
                }       
        
            }
            $res = $this->validatePath($oFieldSet, $pairings, true);
            if ($res === true) { 
                return 'validated input.'; 
            }
            else if ($res === false) { 
                return 'invalid input'; 
            }
            // quick collation process.
            foreach ($res as $aRow) {
                $fieldsToRender[$aRow["document_field_id"]][] = $aRow;
            }
        }

        $default->log->debug('validatePath: results '.print_r($fieldsToRender,true)); 

        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/handle_conditional");
        $aTemplateData = array(
            "fieldset" => $oFieldSet->getId(),
            "oldfields" => $pairings,
            "fieldsToRender" => $fieldsToRender,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        print $data;
    }

    function getMasterFieldInfo($oFieldSet) {
        global $default;
        $masterField = $oFieldSet->getMasterField();
        $sQuery = "SELECT md_cond.document_field_id AS document_field_id, md_cond.metadata_lookup_id AS val, md_lookup.name AS name FROM $default->md_condition_table AS md_cond LEFT JOIN $default->md_condition_chain_table AS md_chain ON (md_cond.id = md_chain.child_condition) LEFT JOIN $default->metadata_table AS md_lookup ON (md_cond.metadata_lookup_id = md_lookup.id) WHERE md_cond.document_field_id = ? ";
        return DBUtil::getResultArray(array($sQuery, array($masterField)));
    }

    // either returns the set of pathid's for the input,
    // or false (FIXME:  should be PEAR::raiseError)
    function validatePath($oFieldSet, $aPairings, $bPartial) {
        /* An explanation of what happens below, since NBM's comment was "Put that crack-pipe back in the freezer."
         *
         * $aPairings are the inputs we were handed.  Within these, there are 3 
         * important things:
         *   1.  we have a limited set of document_fields within the $oFieldSet.
         *   2.  one of these fields is the "master field" - the _ONLY_ one without
         *       any parent conditions.
         *   3.  some fields may get passed _out_ as <input type="hidden" name="conditional_field_x" value="-1" />
         *       which indicates that there is NO VALUE for that field (again, saved in the db as "-1" so we don't go mad.
         *
         * We essentially have 3 stacks:  inputs, parent_conditions, free_field_ids
         * 
         * $bPartial indicates what we need to do once we've run out of inputs to validate:  either fail (if we don't have coverage, and its not partial)
         * or return the possible next choices.
         *
         * We start with the master-field, and get its entry in the table.  If it isn't there, fail.
         *    i.  push its field onto the "parent_conditions" stack, and remove the field_id from the "option_fields".
         *    ii. remove its entry from the inputs.
         * While we have inputs ->
         *    find any matches that have a (field_id, lookup_id) in the input set, and parent_condition in the parent_conditions stack.
         *    if no values found, FAIL - invalid input (we can't match what we've been given).
         *    otherwise ->
         *      remove them from the input stack, push their id's onto the parent_condition stack, and remove their field_id's from the field_id stack.
         * If $bPartial == true ->
         *    get anything which has a parent_condition in the parent_condition set, and a field_id in the free_field_id's set -> this will give you
         *    the next set of "inputs" - (column, lookup) pairs with parent-rules that have been activated.
         * If $bPartial == false and free_field_id's still has anything in it WE FAIL OUT.
         */
         global $default;
         $free_field_ids = array();
         $parent_conditions = array();
         $inputs = $aPairings;   // please tell me this does a copy ... 

         // step 1:  generate free_field_ids.
         $childFields = DocumentField::getList('parent_fieldset = '.$oFieldSet->getId());
         foreach ($childFields as $oField) {
            $free_field_ids[$oField->getId()] = 1; // placeholder.
         }
         $master_field = $oFieldSet->getMasterField(); // this is the id of the master field.
         if (!array_key_exists($master_field, $inputs)) {
            return false;   // no master field in the input.
         }
         
         // step 2:  get the first parent, to get the ball rolling.
         $sQuery = "SELECT * FROM $default->md_condition_table WHERE document_field_id = ? and metadata_lookup_id = ? ";
         $aParams = array($master_field, $inputs[$master_field]);
         $res = DBUtil::getOneResult(array($sQuery, $aParams));

         if (PEAR::isError($res)) {
            return false;   // no value matched on the master field input, the rest MUST fail. 
         }
         else {
            unset($free_field_ids[$master_field]); // master is no longer free.
            $rule_id = $res["id"];
            $parent_conditions[$rule_id] = 1;
            unset($inputs[$master_field]);
         }

         $default->log->debug('validatePath: parent_conditions '.print_r($parent_conditions,true)); 


         while (count($inputs) != 0) { // we'll return out inside here if necessary.
            // check for items in inputs, with parents in parent_conditions.

            // $testStr = "";
            // $testarr = array();
            // for ($i=0; $i<3; $i++) {
            //     $testarr[] = "( ? )";
            //  }
            // $testStr = "(".join(" OR ", $testarr).")";
            // return $testStr;

            // we need something like "parent_conditions IN (1,2,3) AND ((f=1 AND v=2) OR (f=2 AND v=5) OR (f=4 AND v=7))
            $sParentClause = "md_chain.parent_condition IN (";
            $aInputParts = array();
            for ($i=0; $i<count($parent_conditions); $i++) {  
                if ($i == count($parent_conditions)-1) {
                    $sParentClause .= '?';
                } else {
                    $sParentClause .= '? ,';
                }
            }
            $sParentClause .= ')';

            $aInputs = array();

            foreach ($inputs as $fid => $lookid) {
                $aInputs[] = $fid; 
                $aInputs[] = $lookid;
                $aInputParts[] = '(md_cond.document_field_id = ? AND md_cond.metadata_lookup_id = ?)';
            }
            $sInputs = join(" OR ", $aInputParts);
            $sInputs = '(' . $sInputs . ')';


            $default->log->debug('validatePath: parent_conditions '.print_r($parent_conditions,true));

            $sFieldClause = "md_cond.document_field_id IN (";
            for ($i=0; $i<count($free_field_ids); $i++) {  
                if ($i == count($free_field_ids)-1) {
                    $sFieldClause .= '?';
                } else {
                    $sFieldClause .= '? ,';
                }
            }
            $sFieldClause .= ')';
            $default->log->debug('validatePath: sParentClause '.print_r($sParentClause,true));

            $sWhere = KTUtil::whereToString(array(array($sParentClause, array_keys($parent_conditions)), array($sInputs, $aInputs)));
            $sQuery = "SELECT md_cond.id as rule_id, md_cond.document_field_id AS field_id FROM $default->md_condition_table AS md_cond LEFT JOIN $default->md_condition_chain_table AS md_chain ON (md_cond.id = md_chain.child_condition) WHERE ";
            $default->log->debug('validatePath: '.print_r(array($sQuery . $sWhere[0], $sWhere[1]),true));
            $res = DBUtil::getResultArray(array($sQuery . $sWhere[0], $sWhere[1]));  
            if (PEAR::isError($res)) {
                return false;
            }
            
            // if there's anything is $res, its a match (must be - we can't have crossed chains.)
            if (count($res) == 0) {
                return false;  // fail - no matches from inputs against parent_conditions.
            } else {
                // we must have a match - MAY have multiple matches.
                foreach ($res as $aRow) {
                    $default->log->debug('validatePath: output_row '.print_r($aRow,true));
                    $parent_conditions[$aRow["rule_id"]] = 1; // add this as a possible parent condition.
                    unset($free_field_ids[$aRow["field_id"]]); // no longer free
                    unset($inputs[$aRow["field_id"]]); // no longer an un-processed input, so reduce the input-count.
                }
            }
         }

         // ok:  we got this far, and have run out of inputs without running out of matches.
         // IF we're looking for a partial match, and still have free fields, return the set of free-and-parent matches.
         // OTHERWISE if we have free fields, fail
         // finally, pass the input as valid.

         if (($bPartial === true) and (count($free_field_ids) > 0)) {
            // generate the set of matches for free_fields with appropriate parents.
            // UNFORTUNATELY, there is no "nice" way to do this.
            // Wax On.  Wax Off.

            $sParentClause = "md_chain.parent_condition IN (";
            for ($i=0; $i<count($parent_conditions); $i++) {  
                if ($i == count($parent_conditions)-1) {
                    $sParentClause .= '?';
                } else {
                    $sParentClause .= '? ,';
                }
            }
            $sParentClause .= ')';
            $default->log->debug('validatePath: parent_conditions '.print_r($parent_conditions,true));

            $sFieldClause = "md_cond.document_field_id IN (";
            for ($i=0; $i<count($free_field_ids); $i++) {  
                if ($i == count($free_field_ids)-1) {
                    $sFieldClause .= '?';
                } else {
                    $sFieldClause .= '? ,';
                }
            }
            $sFieldClause .= ')';
            $default->log->debug('validatePath: sParentClause '.print_r($sParentClause,true));
              
            $aWhere = KTUtil::whereToString(array(array($sParentClause, array_keys($parent_conditions)), array($sFieldClause,array_keys($free_field_ids))));
            $sQuery = "SELECT md_cond.document_field_id AS document_field_id, md_cond.metadata_lookup_id AS val, md_lookup.name as name FROM $default->md_condition_table AS md_cond LEFT JOIN $default->md_condition_chain_table AS md_chain ON (md_cond.id = md_chain.child_condition) LEFT JOIN $default->metadata_table AS md_lookup ON (md_cond.metadata_lookup_id = md_lookup.id) WHERE ";
            $default->log->debug('validatePath: sParentClause '.print_r(array($sQuery . $aWhere[0], $aWhere[1]), true));
            $sOrderClause = " ORDER BY document_field_id ASC, name ASC";
            return DBUtil::getResultArray(array($sQuery . $aWhere[0] . $sOrderClause, $aWhere[1]));  // FIXME catch errors?
         } else if (count($free_field_ids) != 0) {
            return false; // incomplete - could actually catch this at the start.
         } else {
            return true; // note - this ALSO matches whenever $bPartial is true, but we have completed. UP THE STACK: true => valid and committable. 
         }
    }

    // FIXME:  this need s to move into MDCondition.inc, or manageConditionalMetadata.php
    // actually, this needs to die - its DEBUG only, really.
    function do_createCondition() {
        global $default;
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
        $parent_id = KTUtil::arrayGet($_REQUEST, 'parent_id');
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');
        $lookup_id = KTUtil::arrayGet($_REQUEST, 'lookup_id');
        
        $resObj = MDConditionNode::createFromArray(array(
            "iFieldId" => $field_id,
            "iLookupId" => $lookup_id,
        ));
    
        $default->log->debug("CREATE_CONDITION_DEBUG: ".print_r($resObj,true));

        $resObj2 = MDConditionChain::createFromArray(array(
            "iParentCondition" => $parent_id, // may be null. 
            "iChildCondition" => $resObj->getId(),
        ));

        $default->log->debug("CREATE_CONDITION_DEBUG: ".print_r($resObj2,true));
    }

}

$oDispatcher = new HandleConditionalDispatcher();
$oDispatcher->dispatch();

?>

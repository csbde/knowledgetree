<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');
require_once(KT_LIB_DIR . "/documentmanagement/MDTree.inc");

require_once(KT_DIR . "/plugins/ktcore/admin/fieldsets/basic.inc.php");

class ConditionalFieldsetManagementDispatcher extends BasicFieldsetManagementDispatcher {

    var $oMasterfield;
    var $aFreeFields;
    var $bIncomplete;

    function predispatch() {
        // do the other stuff.
        parent::predispatch();
        
    }
    
    function statuswarnings() {
    
        // master field
    
        $master_field = DocumentField::get($this->oFieldset->getMasterFieldId());
        if (!PEAR::isError($master_field)) {
            $this->oMasterfield = $master_field;
        }

        // ordering
        
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($this->oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);
        $aFields = $this->oFieldset->getFields();

        $aFreeFieldIds = array();
        foreach ($aFields as $oField) {
            $aFreeFieldIds[] = $oField->getId();
        }
        if ($oMasterField) {
            $aParentFieldIds = array($oMasterField->getId());
            foreach ($aFieldOrders as $aRow) {
                $aParentFieldIds[] = $aRow['child_field_id'];
            }
            $aParentFields = array();
            foreach (array_unique($aParentFieldIds) as $iId) {
                $aParentFields[] =& DocumentField::get($iId);
            }
            $aFreeFields = array();
            foreach ($aFreeFieldIds as $iId) {
                if (in_array($iId, $aParentFieldIds)) {
                    continue;
                }
                $aFreeFields[] =& DocumentField::get($iId);
            }
        }
        $this->aFreeFields = $aFreeFields;
        
        // general completeness.
        
        $res = KTMetadataUtil::checkConditionalFieldsetCompleteness($this->oFieldset);
        if (PEAR::isError($res)) {
            $sIncomplete = $res->getMessage();
            $this->bIncomplete = true;
        } else {
            $sIncomplete = null;
            $this->bIncomplete = false;
        }        
    
        // now prep for the warnings.
        if ($this->oMasterfield) {
            if (!empty($this->aFreeFields)) {
                $this->addErrorMessage(_kt("Al fields must be assigned to an order in the conditional system.  To correct this, please use the \"manage field ordering\" link below.  <b>This fieldset will display as a normal, non-conditional fieldset until this problem is corrected.</b>"));
                $this->oPage->booleanLink = true;                
            } else if ($this->bIncomplete) {
                $this->addErrorMessage(sprintf(_kt("This fieldset is incomplete: %s <b>This fieldset will display as a normal, non-conditional fieldset until this problem is corrected.</b>"), $sIncomplete));
                $this->oPage->booleanLink = true;
            }    
        } else {
            $this->addErrorMessage(_kt("A conditional fieldset must have a master field before it can be used. To correct this, please use the \"manage field ordering\" link below.  <b>This fieldset will display as a normal, non-conditional fieldset until this problem is corrected.</b>"));
            $this->oPage->booleanLink = true;        
        }
    
    }

    // API:  this provides information about the fieldset, including which actions are available.
    function describe_fieldset($oFieldset) {    
        $this->oFieldset = $oFieldset;
    
        // don't let people think this fieldset will work when it won't.
        $this->statuswarnings();    
        
        $this->persistParams(array('fFieldsetId','action'));    
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/metadata/conditional/conditional_admin_overview');
        $oTemplate->setData(array(
            'context' => $this,
            'fields' => $oFieldset->getFields(),
            'oFieldset' => $oFieldset,
        ));
        return $oTemplate->render();
    }
    
    // overrides
    function getFieldTypeVocab() {
        $types = array(
            'lookup' => _kt("Lookup"),                           
        );        
        return $types;
    }
    
    // overrides
    function getDefaultType() {
        return 'lookup';
    }
    
    /* ------------------ prime evil ------------------------------------ */
    
    function do_editconditional() {
        if ($this->oFieldset->getIsComplex()) {
            return $this->do_editComplexFieldset();
        } else {
            return $this->do_editFieldset();        
        }
    }
    
    // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editFieldset() {
        $fieldset_id = $this->oFieldset->getId();
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editsimple");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         */
        
        $oFieldset =& $this->oFieldset;
        $aFields =& $oFieldset->getFields();

        $this->oPage->setBreadcrumbDetails(_kt('Manage simple conditional'));
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);        
        $aOrders = array();
        foreach ($aFieldOrders as $row) {
            $aChildren = KTUtil::arrayGet($aOrders, $row['parent_field_id'], array());
            $aChildren[] = $row['child_field_id'];
            $aOrders[$row['parent_field_id']] = $aChildren;
        } 
        
        // for useability, they can go in any order
        // but master field should be first.  beyond that 
        // it can get odd anyway. 
        
        $aKeyedFields = array();
        $aOrderedFields = array();
        $aStack = array($oFieldset->getMasterFieldId());
        
        // first, key
        foreach ($aFields as $oField) {
            $aKeyedFields[$oField->getId()] = $oField;
        }
        
        while (!empty($aStack)) {
            $iKey = array_shift($aStack);
            // this shouldn't happen, but avoid it anyway.
            if (!is_null($aKeyedFields[$iKey])) {
                $aOrderedFields[] = $aKeyedFields[$iKey];
                unset($aKeyedFields[$iKey]);
            }
            // add children to stack
            $aStack = kt_array_merge($aStack, $aOrders[$iKey]);
        }
        
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "ordering" => $aOrders,
            "aFields" => $aOrderedFields,
            "iMasterFieldId" => $oFieldset->getMasterFieldId(),
        );
        return $oTemplate->render($aTemplateData);
    }
    
        // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editComplexFieldset() {
        $fieldset_id = $this->oFieldset->getId();
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editcomplex");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         *  FIXME we fake it here with nested arrays...
         */
        $oFieldset =& $this->oFieldset;
        $aFields =& $oFieldset->getFields();        
        
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);        
        $aOrders = array();
        foreach ($aFieldOrders as $row) {
            $aChildren = KTUtil::arrayGet($aOrders, $row['parent_field_id'], array());
            $aChildren[] = $row['child_field_id'];
            $aOrders[$row['parent_field_id']] = $aChildren;
        } 
        

        $aKeyedFields = array();
        $aOrderedFields = array();
        $aStack = array($oFieldset->getMasterFieldId());
        
        // first, key
        foreach ($aFields as $oField) {
            $aKeyedFields[$oField->getId()] = $oField;
        }
        
        while (!empty($aStack)) {
            $iKey = array_shift($aStack);
            // this shouldn't happen, but avoid it anyway.
            if (!is_null($aKeyedFields[$iKey])) {
                $aOrderedFields[] = $aKeyedFields[$iKey];
                unset($aKeyedFields[$iKey]);
            }
            // add children to stack
            $aStack = kt_array_merge($aStack, $aOrders[$iKey]);
        }        
        
        $this->oPage->setBreadcrumbDetails(_kt('Manage complex conditional'));
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "ordering" => $aOrders,
            "aFields" => $aOrderedFields,
            "iMasterFieldId" => $oFieldset->getMasterFieldId(),
        );
        return $oTemplate->render($aTemplateData);
    }    

    /* ------------------ conditional behaviour code. ------------------- */

    function form_setmasterfield() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Select Master Field"),
            'action' => 'setmasterfield',
            'cancel_url' => $this->sParentUrl,
            'fail_action' => 'manageordering',
            'submit_label' => _kt("Set Master Field"),
            'context' => $this,
        ));
        
        if (!is_null($this->oFieldset->getMasterFieldId())) {
            $change_warning = _kt("Changing the master field set will remove all existing field
ordering!");
        }
        
        $oForm->setWidgets(array(
            array('ktcore.widgets.entityselection', array(
                'name' => 'master_field',
                'label' => _kt("Master Field"),
                'description' => _kt('In order to have a chain of conditions, one initial field must be shown to the user. This is called the master field.'),
                'important_description' => $change_warning,
                'value' => $this->oFieldset->getMasterFieldId(),
                'vocab' => $this->oFieldset->getFields(),
                'label_method' => 'getName',
                'use_simple' => false,
                'required' => true,
            )),
        ));
        
        $oForm->setValidators(array(
            array('ktcore.validators.entity',array(
                'class' => 'DocumentField',
                'test' => 'master_field',
                'output' => 'master_field',
            )),
        ));
        
        return $oForm;
    }

    function do_manageordering() {
        $oTemplate =& $this->oValidator->validateTemplate("ktcore/metadata/conditional/manage_ordering");
        $this->oPage->setBreadcrumbDetails(_kt("Manage Field Ordering"));
        
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($this->oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);
        $aFields = $this->oFieldset->getFields();

        $aFreeFieldIds = array();
        foreach ($aFields as $oField) {
            $aFreeFieldIds[] = $oField->getId();
        }
        if ($this->oFieldset->getMasterFieldId()) {
            $aParentFieldIds = array($this->oFieldset->getMasterFieldId());
            foreach ($aFieldOrders as $aRow) {
                $aParentFieldIds[] = $aRow['child_field_id'];
            }
            $aParentFields = array();
            foreach (array_unique($aParentFieldIds) as $iId) {
                $aParentFields[] =& DocumentField::get($iId);
            }
            $aFreeFields = array();
            foreach ($aFreeFieldIds as $iId) {
                if (in_array($iId, $aParentFieldIds)) {
                    continue;
                }
                $aFreeFields[] =& DocumentField::get($iId);
            }
        }    
        
        $oTemplate->setData(array(
            'context' => $this,
            'parent_fields' => $aParentFields,
            'free_fields' => $aFreeFields,
            'aFieldOrders' => $aFieldOrders,
            'master_form' => $this->form_setmasterfield(),
            'orderingargs' => $this->meldPersistQuery("", "orderfields",true),
        ));
        return $oTemplate->render();
    }

    // {{{ do_orderFields
    function do_orderfields() {
        $oFieldset =& $this->oFieldset;
        $aFreeFieldIds = $_REQUEST['fFreeFieldIds'];

        if (empty($aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', 'No children fields selected', 'fFieldsetId=' . $oFieldset->getId());
        }
        $iParentFieldId = $_REQUEST['fParentFieldId'];
        if (in_array($iParentFieldId, $aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', _kt('Field cannot be its own parent field'), 'fFieldsetId=' . $oFieldset->getId());
        }
        foreach ($aFreeFieldIds as $iChildFieldId) {
            $res = KTMetadataUtil::addFieldOrder($iParentFieldId, $iChildFieldId, $oFieldset);
            $this->oValidator->notError($res, array(
                'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
                'message' => _kt('Error adding Fields'),
            ));
        }
        
        $this->commitTransaction();
        
        $this->addInfoMessage(_kt("Fields ordered."));
        redirect($this->sParentUrl);
        exit(0);
    }
    // }}}

    // {{{ do_setMasterField
    function do_setmasterfield() {
        $oForm = $this->form_setmasterfield();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }
        $data = $res['results'];

        $oField = $data['master_field'];

	// remove all existing behaviors
	$aFieldIds = array();
	foreach($this->oFieldset->getFields() as $i) {
	    $aFieldIds[] = $i->getId();
	}

	$sTable = KTUtil::getTableName('field_behaviours');
	$aQuery = array("DELETE FROM $sTable WHERE field_id IN (" . DBUtil::paramArray($aFieldIds) . ")", $aFieldIds);
	$res = DBUtil::runQuery($aQuery);		

        $res = KTMetadataUtil::removeFieldOrdering($this->oFieldset);
        $this->oFieldset->setMasterFieldId($oField->getId());
        $res = $this->oFieldset->update();

        $this->commitTransaction();
        redirect($this->sParentUrl);
        exit(0);
    }
    // }}}

    // {{{ do_checkComplete
    /**
     * Checks whether the fieldset is complete, and if it is, sets it to
     * be complete in the database.  Otherwise, set it to not be
     * complete in the database (just in case), and set the error
     * messages as to why it isn't.
     */
    function do_checkComplete() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $res = KTMetadataUtil::checkConditionalFieldsetCompleteness($oFieldset);
        if ($res === true) {
            $oFieldset->setIsComplete(true);
            $oFieldset->update();
            $this->successRedirectTo('manageConditional', _kt('Set to complete'), 'fFieldsetId=' . $oFieldset->getId());
        }
        $oFieldset->setIsComplete(false);
        $oFieldset->update();
        // Success, as we want to save the incompleteness to the
        // database...
        $this->successRedirectTo('manageConditional', _kt('Could not to complete'), 'fFieldsetId=' . $oFieldset->getId());
    }
    // }}}

    // {{{ do_changeToSimple
    function do_changeToSimple() {
        $this->startTransaction();
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oFieldset->setIsComplex(false);
        $res = $oFieldset->update();
        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => _kt('Error changing to simple'),
        ));
        
		$aFields = DocumentField::getByFieldset($oFieldset);
		if (!empty($aFields)) {
		    $aFieldIds = array();
			foreach ($aFields as $oField) { $aFieldIds[] = $oField->getId(); }
			
			// value instances
		    $sTable = KTUtil::getTableName('field_value_instances');
			$aQuery = array(
			    "DELETE FROM $sTable WHERE field_id IN (" . DBUtil::paramArray($aFieldIds) . ")",
				$aFieldIds,
			);
			$res = DBUtil::runQuery($aQuery);		
			//$this->addInfoMessage('value instances: ' . print_r($res, true));
			
			// behaviours
		    $sTable = KTUtil::getTableName('field_behaviours');
			$aQuery = array(
			    "DELETE FROM $sTable WHERE field_id IN (" . DBUtil::paramArray($aFieldIds) . ")",
				$aFieldIds,
			);
			$res = DBUtil::runQuery($aQuery);	
			//$this->addInfoMessage('behaviours: ' . print_r($res, true));
		}        
        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => _kt('Error changing to simple'),
        ));
        KTEntityUtil::clearAllCaches('KTFieldBehaviour');        
        KTEntityUtil::clearAllCaches('KTValueInstance');                

        $this->commitTransaction();        
        $this->addInfoMessage(_kt('Changed to simple'));
        redirect($this->sParentUrl); exit(0);
    }
    // }}}
    

    // {{{ do_changeToComplex
    function do_changeToComplex() {
        $oFieldset =& $this->oFieldset;
        $oFieldset->setIsComplex(true);
        $res = $oFieldset->update();
        
        $this->commitTransaction();
        $this->addInfoMessage(_kt('Changed to simple'));
        redirect($this->sParentUrl); exit(0);
    }
    // }}}
    

    function do_viewOverview() {
        $fieldset_id = $this->oFieldset->getId();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/conditional_overview");

        
        $oFieldset =& $this->oFieldset;
        $aFields =& $oFieldset->getFields();
        
        $this->oPage->setBreadcrumbDetails(_kt("Behaviour Overview"));
        
        $aBehaviours = array();
		foreach ($aFields as $oField) {
		    $aOpts = KTFieldBehaviour::getByField($oField);
		    $aBehaviours = kt_array_merge($aBehaviours, $aOpts);
		}
        
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "aFields" => $aFields,
            "behaviours" => $aBehaviours,
            "iMasterFieldId" => $oFieldset->getMasterFieldId(),
        );
        return $oTemplate->render($aTemplateData);
    }
	
	function getSetsForBehaviour($oBehaviour, $fieldset_id) {
	    $oFieldset =& $this->oFieldset;
		if (is_null($oBehaviour)) {
		    $fid = $oFieldset->getMasterFieldId();
			$aQuery = array(
			    sprintf('SELECT df.name as field_name, ml.name as lookup_name, fb.id as behaviour_id, fb.name as behaviour_name FROM 
				    %s as fvi
					LEFT JOIN %s as fb ON (fvi.behaviour_id = fb.id) 
					LEFT JOIN %s AS df ON (fvi.field_id = df.id) 
					LEFT JOIN metadata_lookup AS ml ON (fvi.field_value_id = ml.id) 
					WHERE fvi.field_id = ?
					ORDER BY df.name ASC, ml.name ASC', 
					KTUtil::getTableName('field_value_instances'),
					KTUtil::getTableName('field_behaviours'),
					KTUtil::getTableName('document_fields'),
					KTUtil::getTableName('metadata')),
				array($fid),
			);
			$res = DBUtil::getResultArray($aQuery);
			return $res;
		} else {
		    $bid = $oBehaviour->getId();
			$aQuery = array(
			    sprintf('SELECT df.name as field_name, ml.name as lookup_name, fb.id as behaviour_id, fb.name as behaviour_name FROM 
			        %s AS fbo 
					LEFT JOIN %s as fvi ON (fbo.instance_id = fvi.id) 
					LEFT JOIN %s as fb ON (fvi.behaviour_id = fb.id) 
					LEFT JOIN %s AS df ON (fvi.field_id = df.id) 
					LEFT JOIN metadata_lookup AS ml ON (fvi.field_value_id = ml.id) 
					WHERE fbo.behaviour_id = ?
					ORDER BY df.name ASC, ml.name ASC', 
					KTUtil::getTableName('field_behaviour_options'),
					KTUtil::getTableName('field_value_instances'),
					KTUtil::getTableName('field_behaviours'),
					KTUtil::getTableName('document_fields'),
					KTUtil::getTableName('metadata')),
				array($bid),
			);
			
			$res = DBUtil::getResultArray($aQuery);
			return $res;
		}

		return $aNextFieldValues;
	}
	
	function do_renameBehaviours() {
        $fieldset_id = $this->oFieldset->getId();
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/conditional_rename_behaviours");

        
        $oFieldset =& $this->oFieldset;
        $aFields =& $oFieldset->getFields();
        
        $aBehaviours = array();
		foreach ($aFields as $oField) {
		    $aOpts = KTFieldBehaviour::getByField($oField);
		    $aBehaviours = kt_array_merge($aBehaviours, $aOpts);
		}
        
        $aTemplateData = array(
            "context" => &$this,
            'args' => $this->meldPersistQuery("","finalRename", true),
            "fieldset_id" => $fieldset_id,
			"behaviours" => $aBehaviours,
        );
        return $oTemplate->render($aTemplateData);
    }
	
	function do_finalRename() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
	    $aRenamed = (array) KTUtil::arrayGet($_REQUEST, "renamed");
				
		$this->startTransaction(); 
		
		foreach ($aRenamed as $bid => $new_name) {
			$oBehaviour = KTFieldBehaviour::get($bid);
			if (PEAR::isError($oBehaviour)) { continue; } // skip it...
			$oBehaviour->setName(trim($new_name));
			$res = $oBehaviour->update();
			if (PEAR::isError($res)) { 
			    $this->errorRedirectToMain(_kt('Failed to change name of behaviour.'), sprintf('action=edit&fFieldsetId=%s',$fieldset_id)); 
			}
		}

		$this->addInfoMessage(_kt("Behaviour names changed."));
		$this->commitTransaction();
		redirect($this->sParentUrl); exit(0);
		
	}
	
	function getIncomplete($oFieldset) {
        $res = KTMetadataUtil::checkConditionalFieldsetCompleteness($oFieldset);
        if (PEAR::isError($res)) {
            $sIncomplete = $res->getMessage();
        } else {
            $sIncomplete = null;
        }	
        return $sIncomplete;
        
	}
    


}

?>

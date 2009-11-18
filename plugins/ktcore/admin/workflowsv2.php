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
 */

// core
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

// workflow
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstatepermissionsassignment.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowadminutil.inc.php');

// other
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
//require_once(KT_DIR . "/thirdparty/pear/GraphViz.php");

class WorkflowNavigationPortlet extends KTPortlet {
    var $oWorkflow;
    var $sHelpPage = 'ktcore/admin/workflow.html';
    var $bActive = true;

    function WorkflowNavigationPortlet($sTitle, $oWorkflow = null) {
        $this->oWorkflow = $oWorkflow;
        parent::KTPortlet($sTitle);
    }

    function render() {
        if (is_null($this->oWorkflow)) { return _kt('No Workflow Selected.'); }

        $aAdminPages = array();
        $aAdminPages[] = array('name' => _kt('Overview'), 'query' => 'action=view&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _kt('States and Transitions'), 'query' => 'action=basic&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _kt('Security'), 'query' => 'action=security&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _kt('Workflow Effects'), 'query' => 'action=effects&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _kt('Select different workflow'), 'query' => 'action=main');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/workflow/admin_portlet");
        $aTemplateData = array(
            "context" => $this,
            "aAdminPages" => $aAdminPages,
        );

        return $oTemplate->render($aTemplateData);
    }
}

class KTWorkflowAdminV2 extends KTAdminDispatcher {
    var $oWorkflow;
    var $oState;
    var $oTransition;
    var $HAVE_GRAPHVIZ;

    function predispatch() {
        $this->persistParams(array('fWorkflowId', 'fStateId', 'fTransitionId'));

        $iWorkflowId = KTUtil::arrayGet($_REQUEST, 'fWorkflowId');
        $iStateId = KTUtil::arrayGet($_REQUEST, 'fStateId');
        $iTransitionId = KTUtil::arrayGet($_REQUEST, 'fTransitionId');

        if (!is_null($iWorkflowId)) {
            $oWorkflow =& KTWorkflow::get($iWorkflowId);
            if (!PEAR::isError($oWorkflow)) {
                $this->oWorkflow =& $oWorkflow;
            }
        }

        if (!is_null($iStateId)) {
            $oState =& KTWorkflowState::get($iStateId);
            if (!PEAR::isError($oState)) {
                $this->oState =& $oState;
            }
        }

        if (!is_null($iTransitionId)) {
            $oTransition =& KTWorkflowTransition::get($iTransitionId);
            if (!PEAR::isError($oTransition)) {
                $this->oTransition =& $oTransition;
            }
        }

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Workflows'),
        );

        if (!is_null($this->oWorkflow)) {
            $this->oPage->addPortlet(new WorkflowNavigationPortlet(_kt("Workflow Administration"), $this->oWorkflow));

            $this->aBreadcrumbs[] = array(
                'url' => KTUtil::addQueryStringSelf(sprintf('action=view&fWorkflowId=%d', $iWorkflowId)),
                'name' => $this->oWorkflow->getName(),
            );
        }

        $this->HAVE_GRAPHVIZ = false;
/*        $dotCommand = KTUtil::findCommand("ui/dot", 'dot');
        if (!empty($dotCommand)) {
            $this->HAVE_GRAPHVIZ = true;
            $this->dotCommand = $dotCommand;
        }
*/
    }

    function do_main() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/list');

        $aWorkflows = KTWorkflow::getList();

        $oTemplate->setData(array(
            'context' => $this,
            'workflows' => $aWorkflows,
        ));
        return $oTemplate->render();
    }

    function do_branchConfirm() {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit' , array());
        if (array_key_exists('copy',$submit)) {
            $selection = KTUtil::arrayGet($_REQUEST, 'workflowSelect' , array());
            if(empty($selection)){
            	$this->errorRedirectToMain(_kt('No workflow selected.'));
            }
            return $this->do_copy();
        }
        if (array_key_exists('confirmCopy',$submit)) {
            $workflowId = KTUtil::arrayGet($_REQUEST, 'workflowId' , array());
            if(empty($workflowId)){
            	$this->errorRedirectToMain(_kt('An unexpected error has occured.'));
            }
            return $this->do_confirmCopy();
        }
        $this->errorRedirectToMain(_kt('No action specified.'));
    }

    function do_copy() {
    	$this->aBreadcrumbs[] = array('url' =>  $_SERVER['PHP_SELF'], 'name' => _kt('Copy Workflow'));
        $selection = KTUtil::arrayGet($_REQUEST, 'workflowSelect' , array());
        $this->oPage->setTitle('Copy Workflow');

        // get selected workflow from database
        $oSelWorkflow = KTWorkflow::get($selection);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/workflow/admin/copy');
        $oTemplate->setData(array(
            'context' => $this,
            'workFlowName' => $oSelWorkflow->getName(),
            'workFlowId' => $oSelWorkflow->getId(),

        ));
        return $oTemplate;
    }

    /*
     * Copies state notifications
     *
     * @params  KTWorkflowState $oldState to copy from
     *          KTWorkflowState $newState to copy to
     *
     * @return true on success or PEAR error
     */
    function copyStateNotifications ($oldState, $newState) {
        // we need the old one
        $aAllowed = KTWorkflowUtil::getInformedForState($oldState);
        // FIXME check that these are all users.
        $res = KTWorkflowUtil::setInformedForState($newState, $aAllowed);
        if (PEAR::isError($res)) {
            return $oForm->handleError($res->getMessage());
        }

        return true;
    }

    function do_confirmCopy(){
    	$oSelWorkflow = KTWorkflow::get(KTUtil::arrayGet($_REQUEST, 'workflowId' , array()));
    	$sWorkflowName = KTUtil::arrayGet($_REQUEST, 'workflowName' , array());

    	// Check that the workflow does not exist already
    	$sWorkflowName = str_replace(array('   ', '  '), array(' ', ' '), $sWorkflowName);
        $oWorkflow = KTWorkflow::getByName($sWorkflowName);
        if (!PEAR::isError($oWorkflow)) {
            return $this->errorRedirectToMain(_kt("A workflow with that name already exists.  Please choose a different name for this workflow."));
        }

    	// create the initial workflow
        $oNewWorkflow = KTWorkflow::createFromArray(array(
            'name' => $sWorkflowName,
            'humanname' => $sWorkflowName,
            'enabled' => true,
        ));

    	// get selected workflow states from database
        $oSelWorkflowStates = KTWorkflowState::getByWorkflow($oSelWorkflow);

        // array to store map of old and new states
        $aStatesMap = array();

        // create new states and build old-to-new map
        foreach ($oSelWorkflowStates as $oOldState) {
            $oNewState = KTWorkflowState::createFromArray(array(
                'workflowid' => $oNewWorkflow->getId(),
                'name' => $oOldState->getName(),
                'humanname' => $oOldState->getName(),
            ));
            $aStatesMap[oldId][] = $oOldState->getId();
            $aStatesMap[newId][] = $oNewState->getId();
            if (PEAR::isError($oNewState)) {
                $oForm->errorRedirectToMain(sprintf(_kt("Unexpected failure cloning state: %s"), $oNewState->getMessage()));
            }

            // Get all state permission assignments for old workflow transitions
            // and copy for copied workflow state permission assignments
	        $aPermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($oOldState);
	        if(count($aPermissionAssignments) > 0){
		        foreach ($aPermissionAssignments as $oPermAssign) {
		            for($i=0;$i<count($aStatesMap[oldId]);$i++){
			        	if($aStatesMap[oldId][$i] == $oPermAssign->getStateId()){
			        		$iStateId = $aStatesMap[newId][$i];

			        		$res = KTWorkflowStatePermissionAssignment::createFromArray(array(
					            'iStateId' => $iStateId,
			                    'iPermissionId' => $oPermAssign->getPermissionId(),
			                    'iDescriptorId' => $oPermAssign->getDescriptorId(),
			                ));

				        	if (PEAR::isError($res)) {
				            	return $this->errorRedirectToMain(sprintf(_kt("Unable to copy state permission assignment: %s"), $res->getMessage()));
				        	}
			        	}
			        }
	        	}
	        }

	        // Copy all disabled actions for states
	        $aDisabled = KTWorkflowUtil::getDisabledActionsForState($oOldState);
	        $res = KTWorkflowUtil::setDisabledActionsForState($oNewState, $aDisabled);

	        // Copy all enabled actions for states
	        $aDisabled = KTWorkflowUtil::getEnabledActionsForState($oOldState);
	        $res = KTWorkflowUtil::setEnabledActionsForState($oNewState, $aDisabled);

	        if (PEAR::isError($res)) {
            	return $this->errorRedirectToMain(sprintf(_kt("Unable to copy disabled state actions: %s"), $res->getMessage()));
        	}
            
            $this->copyStateNotifications ($oOldState, $oNewState);
        }

        // update workflow and set initial state
        for($i=0;$i<count($aStatesMap[oldId]);$i++){
        	if($oSelWorkflow->getStartStateId() == $aStatesMap[oldId][$i]){
        		$oNewWorkflow->setStartStateId($aStatesMap[newId][$i]);
        		$res = $oNewWorkflow->update();
		        if (PEAR::isError($res)) {
		            $this->errorRedirectToMain(sprintf(_kt("Failed to update workflow: %s"), $res->getMessage()));
		        }
        	}
        }

        // set controlled workflow actions
        $aWFActions = KTWorkflowUtil::getControlledActionsForWorkflow($oSelWorkflow);
        $res = KTWorkflowUtil::setControlledActionsForWorkflow($oNewWorkflow, $aWFActions);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to copy workflow controlled actions: %s"), $res->getMessage()));
        }

        // get selected workflow transitions from database
        $oSelWorkflowTransitions = KTWorkflowTransition::getByWorkflow($oSelWorkflow);

        // array to store map of old and new transitions
        $aTransitionsMap = array();

        // copy transitions for workflow
        foreach ($oSelWorkflowTransitions as $oOldTransition) {
            for($i=0;$i<count($aStatesMap[oldId]);$i++){
	        	if($oOldTransition->getTargetStateId() == $aStatesMap[oldId][$i]){
	        		$iDestState = $aStatesMap[newId][$i];
	        	}
	        }
            $oNewTransition = KTWorkflowTransition::createFromArray(array(
                'workflowid' => $oNewWorkflow->getId(),
                'Name' => $oOldTransition->getName(),
                'HumanName' => $oOldTransition->getName(),
                'TargetStateId' => $iDestState,
                'GuardPermissionId' => null,
                'GuardGroupId' => null,
                'GuardRoleId' => null,
                'GuardConditionId' => null,
            ));

            $aTransitionsMap[oldId][] = $oOldTransition->getId();
            $aTransitionsMap[newId][] = $oNewTransition->getId();

            if (PEAR::isError($oNewTransition)) {
                $this->errorRedirectToMain(sprintf(_kt("Failed to copy transition: %s"), $oTransition->getMessage()));
            }

            // map source transitions onto states
            $aOldTransitionSources = KTWorkflowAdminUtil::getSourceStates($oOldTransition);
            $aSourceStates = array();
            for($j=0;$j<count($aOldTransitionSources);$j++){
	            for($i=0;$i<count($aStatesMap[oldId]);$i++){
		        	if($aStatesMap[oldId][$i] == $aOldTransitionSources[$j]->getId()){
		        		$aSourceStates[] = $aStatesMap[newId][$i];
		        		continue;
		        	}
		        }
            }
            $res = KTWorkflowAdminUtil::saveTransitionSources($oNewTransition, $aSourceStates);
	        if (PEAR::isError($res)) {
	            $this->errorRedirectToMain(sprintf(_kt("Failed to set transition origins: %s"), $res->getMessage()));
	        }

	        // Get all triggers for old workflow transitions and
            // copy for copied workflow transitions
	        $aTriggers = KTWorkflowTriggerInstance::getByTransition($oOldTransition);
	        if(count($aTriggers) > 0){
		        foreach ($aTriggers as $oTrigger) {
		            for($i=0;$i<count($aTransitionsMap[oldId]);$i++){
			        	if($aTransitionsMap[oldId][$i] == $oTrigger->getTransitionId()){
			        		$iTransitionId = $aTransitionsMap[newId][$i];

			        		$res = KTWorkflowTriggerInstance::createFromArray(array(
				            'transitionid' => $iTransitionId,
				            'namespace' =>  $oTrigger->getNamespace(),
				            'config' => $oTrigger->getConfigArrayText(),
				        	));

				        	if (PEAR::isError($res)) {
				            	return $this->errorRedirectToMain(sprintf(_kt("Unable to add trigger: %s"), $res->getMessage()));
				        	}
			        	}
			        }
	        	}
	        }
        }

        return $this->successRedirectToMain(sprintf(_kt("%s successfully copied as %s"), $oSelWorkflow->getName(), $oNewWorkflow->getName()));
    }

    function do_newWorkflow() {
        // subdispatch this to the NewWorkflowWizard.
        require_once(dirname(__FILE__) . '/workflow/newworkflow.inc.php');

        $oSubDispatcher =& new KTNewWorkflowWizard;
        $oSubDispatcher->redispatch('wizard', null, $this);
        exit(0);
    }

    // -------------------- Overview -----------------
    // basic view page.

    function do_view() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/view');

        $this->oPage->setBreadcrumbDetails(_kt("Overview"));

        if (!$this->oWorkflow->getIsEnabled()) {
            $this->addInfoMessage(_kt("This workflow is currently marked as disabled.  No new documents can be assigned to this workflow until it is enabled.  To change this, please edit the workflow's base properties."));
        }

        if ($this->oWorkflow->getStartStateId() == false) {
            $this->addErrorMessage(_kt("No start state is specified for this workflow.  No new documents can be assigned to this workflow until one is assigned. To change this, please edit the workflow's base properties."));
        }

        // for the basic view
        $start_state_id = $this->oWorkflow->getStartStateId();
        $oState = KTWorkflowState::get($start_state_id);

        if (PEAR::isError($oState)) {
            $state_name = _kt('No starting state.');
        } else {
            $state_name = $oState->getName();
        }

        // we want to "outsource" some of the analysis

        if ($this->HAVE_GRAPHVIZ) {
            $graph_data = $this->get_graph($this->oWorkflow);
            if (!empty($graph_data['errors'])) {
                foreach ($graph_data['errors'] as $error) {
                    $this->addErrorMessage($error);
                }
            }

            if (!empty($graph_data['info'])) {
                foreach ($graph_data['info'] as $info) {
                    $this->addInfoMessage($info);
                }
            }
        }

        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
            'state_name' => $state_name,
            'workflow' => $this->oWorkflow,
            'have_graphviz' => $this->HAVE_GRAPHVIZ,
        ));
        return $oTemplate->render();
    }

    function form_coreedit() {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => $this,
            'action' => 'setcore',
            'fail_action' => 'editcore',
            'cancel_action' => 'view',
            'label' => _kt('Edit Workflow Details'),
            'submit_label' => _kt('Update Workflow Details'),
        ));

        $oForm->setWidgets(array(
            array('ktcore.widgets.string',array(
                'label' => _kt("Workflow Name"),
                'description' => _kt("Each workflow must have a unique name."),
                'name' => 'workflow_name',
                'required' => true,
                'value' => sanitizeForHTML($this->oWorkflow->getName()),
            )),
            array('ktcore.widgets.entityselection', array(
                'label' => _kt("Starting State"),
                'description' => _kt('When a document has this workflow applied to it, which state should it initially have.'),
                'name' => 'start_state',
                'label_method' => 'getHumanName',
                'vocab' => KTWorkflowState::getByWorkflow($this->oWorkflow),
                'value' => $this->oWorkflow->getStartStateId(),
                'required' => true,
            )),
            array('ktcore.widgets.boolean', array(
                'label' => _kt('Enabled'),
                'description' => _kt('If a workflow is disabled, no new documents may be placed in it.  Documents which were previously in the workflow continue to be able to change state, however.'),
                'name' => 'enabled',
                'value' => $this->oWorkflow->getIsEnabled(),
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'workflow_name',
                'output' => 'workflow_name',
            )),
            array('ktcore.validators.entity', array(
                'test' => 'start_state',
                'class' => 'KTWorkflowState',
                'output' => 'start_state',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'enabled',
                'output' => 'enabled',
            ))
        ));

        return $oForm;
    }

    function do_editcore() {

        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/edit_core');
        $this->oPage->setBreadcrumbDetails(_kt("Edit Details"));

        $oForm = $this->form_coreedit();

        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
            'edit_form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_setcore() {
        $oForm = $this->form_coreedit();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        if (!empty($errors)) {
            $oForm->handleError();
        }

        $this->startTransaction();
        $this->oWorkflow->setName($data['workflow_name']);
        $this->oWorkflow->setHumanName($data['workflow_name']);
        $this->oWorkflow->setStartStateId($data['start_state']->getId());
        $this->oWorkflow->setIsEnabled($data['enabled']);
        $res = $this->oWorkflow->update();
        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Failed to update workflow: %s"), $res->getMessage()));
        }

        $this->successRedirectTo("view",_kt("Workflow updated."));
    }

    // ----------------- Basic - States & Transition ---------------------
    function breadcrumbs_basic() {
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("", "basic")),
            'name' => _kt("States and Transitions"),
        );
    }

    function do_basic() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/basic_overview');
        $this->breadcrumbs_basic();
        $this->oPage->setBreadcrumbDetails(_kt("Overview"));

        $aStates = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $aTransitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);


        if ($this->HAVE_GRAPHVIZ) {
            $graph_data = $this->get_graph($this->oWorkflow);
            if (!empty($graph_data['errors'])) {
                foreach ($graph_data['errors'] as $error) {
                    $this->addErrorMessage($error);
                }
            }

            if (!empty($graph_data['info'])) {
                foreach ($graph_data['info'] as $info) {
                    $this->addInfoMessage($info);
                }
            }
        }

        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
            'states' => $aStates,
            'transitions' => $aTransitions,
        ));
        return $oTemplate->render();
    }

    function form_transitionconnections() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Configure Workflow Process'),
            'description' => _kt('The process a document follows is controlled by the way that the transitions between states are setup.  A document starts the workflow in the initial state, and then follows transitions between states.  Which users can perform these transitions can be configured in the "Security" section.'),
            'submit_label' => _kt('Update Process'),
            'cancel_action' => 'basic',
            'action' => 'setconnections',
            'fail_action' => 'transitionconnections', // consistency - this is not really used.
            'context' => $this,
        ));

        return $oForm;
    }

    function do_transitionconnections() {
        // we don't use a traditional form here, since the grid is too complex
        // and its essentially one-shot.
        //
        //
        $oForm = $this->form_transitionconnections();
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/configure_process');

        $this->breadcrumbs_basic();
        $this->oPage->setBreadcrumbDetails(_kt("Edit Transition Connections"));

        // we want to re-use this for *subsets*.
        $transition_ids = KTUtil::arrayGet($_REQUEST, 'transition_ids');
        $bRestrict = is_array($transition_ids);

        $transitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);
        $availability = array();
        foreach ($transitions as $oTransition) {
            if ($bRestrict) {
                if ($transition_ids[$oTransition->getId()]) {
                    $final_transitions[] = $oTransition;
                } else {
                    continue;
                }
            }

            $sources = KTWorkflowAdminUtil::getSourceStates($oTransition, array('ids' => true));
            $aSources = array();
            foreach ($sources as $source) { $aSources[$source] = $source; }
            $availability[$oTransition->getId()] = $aSources;
        }


        if ($bRestrict) {
            $transitions = $final_transitions;
        }


        if ($this->HAVE_GRAPHVIZ) {
            $graph_data = $this->get_graph($this->oWorkflow);
            if (!empty($graph_data['errors'])) {
                foreach ($graph_data['errors'] as $error) {
                    $this->addErrorMessage($error);
                }
            }

            if (!empty($graph_data['info'])) {
                foreach ($graph_data['info'] as $info) {
                    $this->addInfoMessage($info);
                }
            }
        }

        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm,
            'states' => KTWorkflowState::getByWorkflow($this->oWorkflow),
            'transitions' => $transitions,
            'availability' => $availability,
        ));

        return $oTemplate->render();
    }

    function do_setconnections() {
        // we *must* ensure that transitions are not set to originate from their
        // destination.
        //
        // we can ignore it here, because its dealt with in workflowadminutil

        $to = (array) KTUtil::arrayGet($_REQUEST, 'fTo');
        $from = (array) KTUtil::arrayGet($_REQUEST, 'fFrom');

        // we do not trust any of this data.
        $states = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $states = KTUtil::keyArray($states);
        $transitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);

        $this->startTransaction();

        foreach ($transitions as $oTransition) {
            $dest_id = $to[$oTransition->getId()];
            $oDestState = $states[$dest_id];

            if (!is_null($oDestState)) {
                $oTransition->setTargetStateId($dest_id);
                $res = $oTransition->update();
                if (PEAR::isError($res)) {
                    $this->errorRedirectTo('basic', sprintf(_kt("Unexpected error updating transition: %s"), $res->getMessage()));
                }
            }

            // hook up source states.
            $source_state_ids = array();
            $sources = (array) $from[$oTransition->getId()];

            foreach ($sources as $state_id => $discard) {
                // test existence
                $oState = $states[$state_id];
                if (!is_null($oState) && ($dest_id != $state_id)) {
                    $source_state_ids[] = $oState->getId();
                }
            }

			$aFromTransitionID = array_keys($_REQUEST['fFrom']);
			//run through all transitions to change
			foreach ($aFromTransitionID as $iCurrentId)
			{
            	if($oTransition->getId() == $iCurrentId)
            	{
            		$res = KTWorkflowAdminUtil::saveTransitionSources($oTransition, $source_state_ids);
            	}
			}
            if (PEAR::isError($res)) {
                $this->errorRedirectTo('basic', sprintf(_kt("Failed to set transition origins: %s"), $res->getMessage()));
            }
        }

        $this->successRedirectTo('basic', _kt("Workflow process updated."));
    }

    function form_addstates() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'context' => $this,
            'label' => _kt("Add New States"),
            'submit_label' => _kt("Add States"),
            'action' => 'createstates',
            'cancel_action' => 'basic',
            'fail_action' => 'addstates',
        ));

        $oForm->setWidgets(array(
            array('ktcore.widgets.text',array(
                'label' => _kt('New States'),
                'description' => _kt('As documents progress through their lifecycle, they pass through a number of <strong>states</strong>.  These states describe a step in the process the document must follow.  Examples of states include "reviewed","submitted" or "pending".  Note that the first state you list is the one in which documents will start the workflow - this can be changed later on.'),
                'important_description' => _kt('Please enter a list of states, one per line.  State names must be unique, and this includes states already in this workflow.'),
                'required' => true,
                'name' => 'states',
                'rows' => 15,
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'states',
                'output' => 'states',
                'max_length' => 9999,
            )),
        ));

        return $oForm;
    }

    function do_addstates() {
        $oForm = $this->form_addstates();
        $this->breadcrumbs_basic();
        $this->oPage->setBreadcrumbDetails(_kt("Add States"));
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/add_states');
        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm
        ));
        return $oTemplate->render();
    }

    function do_createstates() {
        $oForm = $this->form_addstates();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        // we want to check for duplicates, empties, etc.

        $initial_states = (array) explode("\n", $data['states']);
        $failed = array();
        $old_states = array();
        $states = array();
        foreach ($initial_states as $sName) {
            $state_name = trim($sName);
            if (empty($state_name)) {
                continue;
            }

            if ($states[$state_name]) {
                $failed[] = $state_name;
                continue;
            }

            // check for pre-existing states.
            $exists = KTWorkflowState::nameExists($sName, $this->oWorkflow);
            if ($exists) {
                $old_states[] = $sName;
            }

            $states[$state_name] = $state_name;
        }
        if (empty($states)) {
            $extra_errors['states'][] = _kt('You must provide at least one state name.');
        }
        if (!empty($failed)) {
            $extra_errors['states'][] = sprintf(_kt("You cannot have duplicate state names: %s"), implode(', ', $failed));
        }
        if (!empty($old_states)) {
            $extra_errors['states'][] = sprintf(_kt("You cannot use state names that are in use: %s"), implode(', ', $old_states));
        }

        // handle any errors.
        if (!empty($errors) || !empty($extra_errors)) {
            $oForm->handleError(null, $extra_errors);
        }

        $this->startTransaction();
        // now act
        foreach ($states as $state_name) {
            $oState = KTWorkflowState::createFromArray(array(
                'workflowid' => $this->oWorkflow->getId(),
                'name' => $state_name,
                'humanname' => $state_name,
            ));
            if (PEAR::isError($oState)) {
                $oForm->handleError(sprintf(_kt("Unexpected failure creating state: %s"), $oState->getMessage()));
            }
        }

        $this->successRedirectTo('basic', _kt("New States Created."));
    }


    function form_addtransitions() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'context' => $this,
            'label' => _kt("Add New Transitions"),
            'submit_label' => _kt("Add Transitions"),
            'action' => 'createtransitions',
            'cancel_action' => 'basic',
            'fail_action' => 'addtransitions',
        ));

        $oForm->setWidgets(array(
            array('ktcore.widgets.text',array(
                'label' => _kt('Transitions'),
                'description' => _kt('In order to move between states, users will cause "transitions" to occur.  These transitions represent processes followed, e.g. "review document", "distribute invoice" or "publish".  You\'ll assign transitions to states in the next step.'  ),
                'important_description' => _kt('Please enter a list of transitions, one per line.  Transition names must be unique.'),
                'required' => false,
                'name' => 'transitions',
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'transitions',
                'output' => 'transitions',
                'max_length' => 9999,
            )),
        ));

        return $oForm;
    }

    function do_addtransitions() {
        $oForm = $this->form_addtransitions();
        $this->breadcrumbs_basic();
        $this->oPage->setBreadcrumbDetails(_kt("Add Transitions"));
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/add_transitions');
        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm
        ));
        return $oTemplate->render();
    }

    function do_createtransitions() {
        $oForm = $this->form_addtransitions();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        // we want to check for duplicates, empties, etc.

        $initial_transitions = (array) explode("\n", $data['transitions']);
        $failed = array();
        $old_transitions = array();
        $transitions = array();
        foreach ($initial_transitions as $sName) {
            $transition_name = trim($sName);
            if (empty($transition_name)) {
                continue;
            }

            if ($transitions[$transition_name]) {
                $failed[] = $transition_name;
                continue;
            }

            // check for pre-existing states.
            $exists = KTWorkflowTransition::nameExists($sName, $this->oWorkflow);
            if ($exists) {
                $old_transitions[] = $sName;
            }

            $transitions[$transition_name] = $transition_name;
        }
        if (empty($transitions)) {
            $extra_errors['transitions'][] = _kt('You must provide at least one transition name.');
        }
        if (!empty($failed)) {
            $extra_errors['transitions'][] = sprintf(_kt("You cannot have duplicate transition names: %s"), implode(', ', $failed));
        }
        if (!empty($old_states)) {
            $extra_errors['transitions'][] = sprintf(_kt("You cannot use transition names that are in use: %s"), implode(', ', $old_transitions));
        }

        // handle any errors.
        if (!empty($errors) || !empty($extra_errors)) {
            $oForm->handleError(null, $extra_errors);
        }

        $this->startTransaction();
        $transition_ids = array();
        $oState = KTWorkflowState::get($this->oWorkflow->getStartStateId());
        foreach ($transitions as $transition_name) {
            $oTransition = KTWorkflowTransition::createFromArray(array(
                "WorkflowId" => $this->oWorkflow->getId(),
                "Name" => $transition_name,
                "HumanName" => $transition_name,
                "TargetStateId" => $oState->getId(),
                "GuardPermissionId" => null,
                "GuardGroupId" => null,
                "GuardRoleId" => null,
                "GuardConditionId" => null,
            ));
            if (PEAR::isError($oTransition)) {
                $oForm->handleError(sprintf(_kt("Unexpected failure creating transition: %s"), $oTransition->getMessage()));
            }
            $transition_ids[] = $oTransition->getId();
        }

        $transition_ids_query = array();
        foreach ($transition_ids as $id) {
            $transition_ids_query[] = sprintf('transition_ids[%s]=%s',$id,  $id);
        }
        $transition_ids_query = implode('&', $transition_ids_query);

        $this->successRedirectTo('transitionconnections', _kt("New Transitions Created."), $transition_ids_query);
    }

    function form_editstate($oState) {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => $this,
            'label' => _kt('Edit State'),
            'submit_label' => _kt('Update State'),
            'action' => 'savestate',
            'fail_action' => 'editstate',
            'cancel_action' => 'basic',
        ));

        $oForm->setWidgets(array(
            array('ktcore.widgets.string', array(
                'name' => 'name',
                'label' => _kt('State Name'),
                'description' => _kt('As documents progress through their lifecycle, they pass through a number of <strong>states</strong>.  These states describe a step in the process the document must follow.  Examples of states include "reviewed","submitted" or "pending".  State names must be unique, and this includes states already in this workflow.'),
                'required' => true,
                'value' => sanitizeForHTML($oState->getName()),
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name',
            )),
        ));

        return $oForm;
    }

    function do_editstate() {
        $this->aBreadcrumbs[] = array(
            'name' => $this->oState->getHumanName(),
        );

        // remember that we check for state,
        // and its null if none or an error was passed.
        if (is_null($this->oState)) {
            $this->errorRedirectTo('basic', _kt("No state specified."));
        }

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/admin/edit_state');
        $this->oPage->setBreadcrumbDetails(_kt('Manage State'));

        $oForm = $this->form_editstate($this->oState);

        $oTemplate->setData(array(
            'context' => $this,
            'edit_form' => $oForm,
        ));

        return $oTemplate->render();
    }

    function do_savestate() {
        $oForm = $this->form_editstate($this->oState);
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        // check if any *other* states have this name.
        if ($data['name'] == $this->oState->getName()) {
            $this->successRedirectTo('editstate',_kt("No change in name."));
        }

        // otherwise we're looking for something different if there's a conflict.

        if (KTWorkflowState::nameExists($data['name'], $this->oWorkflow)) {
            $extra_errors['name'][] = _kt('There is already a state with that name in this workflow.');
        }

        if (!empty($errors) || !empty($extra_errors)) {
            $oForm->handleError(null, $extra_errors);
        }

        $this->startTransaction();

        $this->oState->setName($data['name']);
        $this->oState->setHumanName($data['name']);
        $res = $this->oState->update();

        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Unable to update state: %s"), $res->getMessage()));
        }

        $this->successRedirectTo('basic', _kt("State updated."));
    }

    function form_edittransition($oTransition) {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => $this,
            'label' => _kt('Edit Transition'),
            'submit_label' => _kt('Update Transition'),
            'action' => 'savetransition',
            'fail_action' => 'edittransition',
            'cancel_action' => 'basic',
        ));

        $oForm->setWidgets(array(
            array('ktcore.widgets.string', array(
                'name' => 'name',
                'label' => _kt('Transition Name'),
                'description' => _kt('In order to move between states, users will cause "transitions" to occur.  These transitions represent processes followed, e.g. "review document", "distribute invoice" or "publish".  Transition names must be unique within the workflow (e.g. within this workflow, you can only have one transition called "publish")'),
                'required' => true,
                'value' => sanitizeForHTML($oTransition->getName()),
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name',
            )),
        ));

        return $oForm;
    }

    function do_edittransition() {
        $this->aBreadcrumbs[] = array(
            'name' => $this->oTransition->getHumanName(),
        );

        // remember that we check for state,
        // and its null if none or an error was passed.
        if (is_null($this->oTransition)) {
            $this->errorRedirectTo('basic', _kt("No transition specified."));
        }

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/admin/edit_transition');
        $this->oPage->setBreadcrumbDetails(_kt('Manage Transition'));

        $oForm = $this->form_edittransition($this->oTransition);

        $oTemplate->setData(array(
            'context' => $this,
            'edit_form' => $oForm,
        ));

        return $oTemplate->render();
    }

    function do_savetransition() {
        $oForm = $this->form_edittransition($this->oTransition);
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        // check if any *other* states have this name.
        if ($data['name'] == $this->oTransition->getName()) {
            $this->successRedirectTo('edittransition',_kt("No change in name."));
        }

        // otherwise we're looking for something different if there's a conflict.

        if (KTWorkflowTransition::nameExists($data['name'], $this->oWorkflow)) {
            $extra_errors['name'][] = _kt('There is already a transition with that name in this workflow.');
        }

        if (!empty($errors) || !empty($extra_errors)) {
            $oForm->handleError(null, $extra_errors);
        }

        $this->startTransaction();

        $this->oTransition->setName($data['name']);
        $this->oTransition->setHumanName($data['name']);
        $res = $this->oTransition->update();

        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Unable to update transition: %s"), $res->getMessage()));
        }

        $this->successRedirectTo('basic', _kt("Transition updated."));
    }


    function do_deletetransition() {
        $this->startTransaction();

        if (is_null($this->oTransition)) {
            return $this->errorRedirectTo("basic", _kt("No transition selected"));
        }

        // grab all the triggers
        $aTriggers = KTWorkflowTriggerInstance::getByTransition($this->oTransition);
        foreach ($aTriggers as $oTrigger) {
            $res = $oTrigger->delete();
            if (PEAR::isError($res)) {
                $this->errorRedirectTo("basic", sprintf(_kt("Failed to clear trigger: %s"), $res->getMessage()));
            }
        }

        $res = $this->oTransition->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo("basic", sprintf(_kt("Failed to clear transition: %s"), $res->getMessage()));
        }

        $this->successRedirectTo('basic', _kt("Transition deleted."));
    }

    function form_deletestate() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Delete Existing State"),
            'identifier' => 'ktcore.workflow.deletestate',
            'action' => 'deletestate',
            'cancel_action' => 'basic',
            'fail_action' => 'replacestate',
            'submit_label' => _kt("Delete State"),
            'context' => $this,
        ));
        $other_states = sprintf('id != %d', $this->oState->getId());
        $other_states .= sprintf(' AND workflow_id = %d', $this->oWorkflow->getId());

        $oForm->setWidgets(array(
            array('ktcore.widgets.entityselection', array(
                'vocab' => KTWorkflowState::getList($other_states),
                'label' => _kt("Replacement State"),
                'description' => _kt("In order to remove this state from the system, please select a new state which will take its place.  All references to the state you are deleting will be replaced by this new state."),
                'important_description' => _kt("All references will be changed, including on old documents."),
                'label_method' => 'getName',
                'name' => 'replacement',
                'required' => true,
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.entity', array(
                'test' => 'replacement',
                'output' => 'replacement',
                'class' => 'KTWorkflowState',
            )),
        ));
        return $oForm;
    }

    function do_replacestate() {
        $this->breadcrumbs_basic();
        $this->oPage->setBreadcrumbDetails(_kt("Delete State"));
        $oForm = $this->form_deletestate();
        return $oForm->renderPage(_kt("Delete State"));
    }

    function do_deletestate() {
        $oForm = $this->form_deletestate();
        $res = $oForm->validate();

        $errors = $res['errors'];
        $data = $res['results'];

        if (!empty($errors)) {
            return $oForm->handleError();
        }

        $this->startTransaction();

        if (is_null($this->oState)) {
            return $this->errorRedirectTo("basic", _kt("No state selected"));
        }

        $replacement = $data['replacement'];

        KTWorkflowUtil::replaceState($this->oState, $replacement);

        if ($this->oWorkflow->getStartStateId() == $this->oState->getId()) {
            $this->oWorkflow->setStartStateId($replacement->getId());
            $res = $this->oWorkflow->update();
            if (PEAR::isError($res)) {
                $this->errorRedirectTo("basic", sprintf(_kt("Failed to update workflow: %s"), $res->getMessage()));
            }
        }

        $res = $this->oState->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo("basic", sprintf(_kt("Failed to delete state: %s"), $res->getMessage()));
        }

        $this->successRedirectTo('basic', _kt("State deleted."));
    }

    function breadcrumbs_security() {
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("fTransitionId=&fStateId=","security", true)),
            'name' => _kt("Security"),
        );
    }

    // ----------------- Security ---------------------
    function do_security() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/security_overview');
        $this->breadcrumbs_security();


        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
        ));
        return $oTemplate->render();
    }


    // == PERMISSIONS
    function do_permissionsoverview() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/permissions_overview');
        $this->breadcrumbs_security();
        $this->oPage->setBreadcrumbDetails(_kt("Permissions Overview"));
        // we want to give a complete overview.
        // this involves a grid of:
        //          permission permissions
        //   state      x           -
        //   state      -           x

        $aStates = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $aUsefulPermissions = KTPermission::getDocumentRelevantList();
        $aPermissionGrid = array();
        $aControllers = array();
        foreach ($aStates as $oState) {
            $perms = array();
            $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($oState);
            $aControllers[$oState->getId()] = (!empty($aStatePermAssigns));

            foreach ($aStatePermAssigns as $oPermAssign) {
                $perms[$oPermAssign->getPermissionId()] = $oPermAssign; // we only care about non-null in *this* map.
            }

            $aPermissionGrid[$oState->getId()] = $perms;
        }

        $oTemplate->setData(array(
            'context' => $this,
            'controllers' => $aControllers,
            'perm_grid' => $aPermissionGrid,
            'perms' => $aUsefulPermissions,
            'states' => $aStates,
        ));
        return $oTemplate->render();
    }

    function form_managepermissions() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Controlled Permissions"),
            'submit_label' => _kt("Set controlled permissions"),
            'action' => 'setcontrolledpermissions',
            'fail_action' => 'managepermissions',
            'cancel_action' => 'permissionsoverview',
            'context' => $this,
        ));

        return $oForm;
    }

    // == PERMISSIONS
    function do_managepermissions() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/managepermissions');

        $oForm = $this->form_managepermissions();

        $this->breadcrumbs_security();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Document Permissions"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("fStateId=","permissionsoverview",true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oState->getHumanName(),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","managepermissions",true)),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Manage Permissions"));

        $aUsefulPermissions = KTPermission::getDocumentRelevantList();
        $aPermissionGrid = array();
        $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($this->oState);

        foreach ($aStatePermAssigns as $oPermAssign) {
            $aPermissionGrid[$oPermAssign->getPermissionId()] = $oPermAssign;
        }

        $oTemplate->setData(array(
            'context' => $this,
            'perm_grid' => $aPermissionGrid,
            'perms' => $aUsefulPermissions,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_setcontrolledpermissions() {
        $active = (array) KTUtil::arrayGet($_REQUEST, 'fControlled');

        $aUsefulPerms = KTPermission::getDocumentRelevantList();
        $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($this->oState);
        $aStatePermAssigns = KTUtil::keyArray($aStatePermAssigns, 'getPermissionId');
        $assigns = array();

        $this->startTransaction();
        // delete those who don't know want
        // create those we don't have.

        foreach ($aStatePermAssigns as $perm_id => $assign) {
            if (!$active[$perm_id]) {
                $assign->delete();
            }
        }
        $emptydescriptor = KTPermissionUtil::getOrCreateDescriptor(array());
        if (PEAR::isError($emptydescriptor)) {
            $this->errorRedirectTo("managepermissions", sprintf(_kt("Failed to create assignment: %s"), $emptydescriptor->getMessage()));
        }
        foreach ($active as $perm_id => $discard) {
            if (!$aStatePermAssigns[$perm_id]) {
                $assign = KTWorkflowStatePermissionAssignment::createFromArray(array(
                    "iStateId" => $this->oState->getId(),
                    "iPermissionId" => $perm_id,
                    "iDescriptorId" => $emptydescriptor->getId(),
                ));
                if (PEAR::isError($assign)) {
                    $this->errorRedirectTo("managepermissions", sprintf(_kt("Failed to create assignment: %s"), $assign->getMessage()));
                }
            }
        }

        $this->successRedirectTo("managepermissions", _kt("Controlled permission updated."));
    }

    // == PERMISSIONS
    function do_allocatepermissions() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/allocate_permissions');

        $oForm = $this->form_managepermissions();

        $this->breadcrumbs_security();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Document Permissions"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("fStateId=","permissionsoverview",true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oState->getHumanName(),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","managepermissions",true)),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Allocate Permissions"));

        $aUsefulPermissions = KTPermission::getDocumentRelevantList();
        $aPermissionGrid = array();
        $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($this->oState);

        foreach ($aStatePermAssigns as $oPermAssign) {
            $aPermissionGrid[$oPermAssign->getPermissionId()] = $oPermAssign;
        }

        $aPermissionsToJSON = array();
        foreach($aUsefulPermissions as $oP) {
            $perm_id = $oP->getId();
            if ($aPermissionGrid[$perm_id]) {
                $aPermissionsToJSON[] = array('id'=>$oP->getId(), 'name'=>$oP->getHumanName());
            }
        }

        $oJSON = new Services_JSON;
        $sJSONPermissions = $oJSON->encode($aPermissionsToJSON);

        $oTemplate->setData(array(
            'context' => $this,
            'perm_grid' => $aPermissionGrid,
            'perms' => $aUsefulPermissions,
            'form' => $oForm,
            'jsonpermissions' => $sJSONPermissions,
            'args' => $this->meldPersistQuery("","setpermissionallocations",true),
        ));
        return $oTemplate->render();
    }

    // JSON helper. from permissions.

    function &_getPermissionsMap() {
        $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($this->oState);
        $aPermissionsMap = array('role'=>array(), 'group'=>array());

        foreach ($aStatePermAssigns as $oPermAssign) {
            $oDescriptor = KTPermissionDescriptor::get($oPermAssign->getDescriptorId());
            $iPermissionId = $oPermAssign->getPermissionId();

	    // groups
            $aGroupIds = $oDescriptor->getGroups();
            foreach ($aGroupIds as $iId) {
                $aPermissionsMap['group'][$iId][$iPermissionId] = true;
            }

	    // roles
            $aRoleIds = $oDescriptor->getRoles();
            foreach ($aRoleIds as $iId) {
                $aPermissionsMap['role'][$iId][$iPermissionId] = true;
            }
        }
    	return $aPermissionsMap;
    }

    function json_getEntities($optFilter = null) {
        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        if($sFilter == false && $optFilter != null) {
            $sFilter = $optFilter;
        }

        $bSelected = KTUtil::arrayGet($_REQUEST, 'selected', false);

        $aEntityList = array('off' => _kt('-- Please filter --'));

        // get permissions map
        $aPermissionsMap =& $this->_getPermissionsMap();

        if($bSelected || $sFilter && trim($sFilter)) {
            if(!$bSelected) {
                $aEntityList = array();
            }

            $aGroups = Group::getList(sprintf('name like "%%%s%%"', $sFilter));
            foreach($aGroups as $oGroup) {
                $aPerm = @array_keys($aPermissionsMap['group'][$oGroup->getId()]);
                if(!is_array($aPerm)) {
                    $aPerm = array();
                }

                if($bSelected) {
                    if(count($aPerm))
                        $aEntityList['g'.$oGroup->getId()] = array('type' => 'group',
                                   'display' => 'Group: ' . $oGroup->getName(),
                                   'name' => $oGroup->getName(),
                                   'permissions' => $aPerm,
                                   'id' => $oGroup->getId(),
                                   'selected' => true);
                } else {
                    $aEntityList['g'.$oGroup->getId()] = array('type' => 'group',
                               'display' => 'Group: ' . $oGroup->getName(),
                               'name' => $oGroup->getName(),
                               'permissions' => $aPerm,
                               'id' => $oGroup->getId());
                }
            }

            $aRoles = Role::getList(sprintf('name like "%%%s%%"', $sFilter));
            foreach($aRoles as $oRole) {
                $aPerm = @array_keys($aPermissionsMap['role'][$oRole->getId()]);
                if(!is_array($aPerm)) {
                    $aPerm = array();
                }

            if($bSelected) {
                if(count($aPerm))
                    $aEntityList['r'.$oRole->getId()] = array('type' => 'role',
                                      'display' => 'Role: ' . $oRole->getName(),
                                      'name' => $oRole->getName(),
                                      'permissions' => $aPerm,
                                      'id' => $oRole->getId(),
                                      'selected' => true);
            } else {
                $aEntityList['r'.$oRole->getId()] = array('type' => 'role',
                                      'display' => 'Role: ' . $oRole->getName(),
                                      'name' => $oRole->getName(),
                                      'permissions' => $aPerm,
                                      'id' => $oRole->getId());
            }
            }
        }
        return $aEntityList;
    }


    function do_setpermissionallocations() {
        $aPermissionAllowed = (array) KTUtil::arrayGet($_REQUEST, 'foo'); // thanks BD.

        $this->startTransaction();

        $aStatePermAssigns = KTWorkflowStatePermissionAssignment::getByState($this->oState);

        // we now walk the alloc'd perms, and go.
        foreach ($aStatePermAssigns as $oPermAssign) {
            $aAllowed = (array) $aPermissionAllowed[$oPermAssign->getPermissionId()]; // is already role, group, etc.
            $oDescriptor = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
            if (PEAR::isError($oDescriptor)) { $this->errorRedirectTo('allocatepermissions', _kt('Failed to allocate as specified.')); }

            $oPermAssign->setDescriptorId($oDescriptor->getId());
            $res = $oPermAssign->update();
            if (PEAR::isError($res)) { $this->errorRedirectTo('allocatepermissions', _kt('Failed to allocate as specified.')); }
        }

        KTPermissionUtil::updatePermissionLookupForState($this->oState);

        $this->successRedirectTo('managepermissions', _kt('Permissions Allocated.'));
    }

    // ACTIONS

    function do_actionsoverview() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/actions_overview');
        $this->oPage->setBreadcrumbDetails(_kt("Actions"));
        $this->breadcrumbs_security();
        $actions = KTUtil::keyArray(KTDocumentActionUtil::getAllDocumentActions(), 'getName');
        $blacklist = array('ktcore.actions.document.displaydetails');

        foreach ($blacklist as $name) {
            unset($actions[$name]);
        }

        $states = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $action_grid = array();
        foreach ($states as $oState) {
            $state_actions = array();
            $disabled = KTWorkflowUtil::getDisabledActionsForState($oState);

            foreach ($disabled as $name) {
                $state_actions[$name] = $name;
            }

            $action_grid[$oState->getId()] = $state_actions;
        }

        $oTemplate->setData(array(
            'context' => $this,
            'states' => $states,
            'actions' => $actions,
            'grid' => $action_grid,
        ));
        return $oTemplate->render();
    }

    function do_editactions() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/actions_edit');
        $this->oPage->setBreadcrumbDetails(_kt("Edit Actions"));
        $actions = KTUtil::keyArray(KTDocumentActionUtil::getAllDocumentActions(), 'getName');
        $blacklist = array('ktcore.actions.document.displaydetails');
        $this->breadcrumbs_security();
        foreach ($blacklist as $name) {
            unset($actions[$name]);
        }

        $states = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $action_grid = array();
        foreach ($states as $oState) {
            $state_actions = array();
            $disabled = KTWorkflowUtil::getDisabledActionsForState($oState);

            foreach ($disabled as $name) {
                $state_actions[$name] = $name;
            }

            $action_grid[$oState->getId()] = $state_actions;
        }

        $oTemplate->setData(array(
            'context' => $this,
            'states' => $states,
            'actions' => $actions,
            'grid' => $action_grid,
            'args' => $this->meldPersistQuery("","saveactions", true),
        ));
        return $oTemplate->render();
    }

    function do_saveactions() {
        $disabled_actions = (array) $_REQUEST['fActions'];


        $states = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $actions = KTUtil::keyArray(KTDocumentActionUtil::getAllDocumentActions(), 'getName');

        $this->startTransaction();

        foreach ($states as $oState) {
            $disable = array();
            $state_disabled = (array) $disabled_actions[$oState->getId()];
            if (!empty($state_disabled)) {
                foreach ($actions as $name => $oAction) {
                    if ($state_disabled[$name]) {
                        $disable[] = $name;
                    }
                }
            }

            $res = KTWorkflowUtil::setDisabledActionsForState($oState, $disable);
        }

        $this->successRedirectTo('actionsoverview', _kt('Disabled actions updated.'));
    }

    function do_transitionsecurityoverview() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/transition_guards_overview');
        $this->oPage->setBreadcrumbDetails(_kt("Overview"));
        $this->oPage->setTitle(_kt("Transition Restrictions Overview"));
        $this->breadcrumbs_security();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Restrictions"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("", "transitionsecurityoverview", true)),
        );

        $transitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);

        $oTemplate->setData(array(
            'context' => $this,
            'transitions' => $transitions,
        ));
        return $oTemplate->render();
    }

    // helper
    function describeTransitionGuards($oTransition) {
        $restrictions = KTWorkflowUtil::getGuardTriggersForTransition($oTransition);

        if (empty($restrictions)) {
            return _kt("No restrictions in place for this transition.");
        }

        $restriction_text = array();
        foreach ($restrictions as $oGuard) {
            $restriction_text[] = $oGuard->getConfigDescription();
        }

        return implode('. ', $restriction_text);
    }

    function form_addtransitionguard() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.admin.workflow.addguard',
            'label' => _kt("Add New Transition Restriction"),
            'action' => 'addguard',
            'cancel_action' => 'manageguards',
            'fail_action' => 'manageguards',
            'submit_label' => _kt("Add Restriction"),
            'context' => $this,
        ));

        $oTriggerSingleton =& KTWorkflowTriggerRegistry::getSingleton();
        $aTriggerList = $oTriggerSingleton->listWorkflowTriggers();
        $vocab = array();
        foreach ($aTriggerList as $ns => $aTriggerInfo) {
            $aInfo = $aTriggerInfo; // i am lazy.
            //var_dump($aInfo);
            $actions = array();
            if ($aInfo['guard']) {
                $actions[] = _kt('Guard');
            } else {
                continue;
            }
            if ($aInfo['action']) {
                $actions[] = _kt('Action');
            }
            $sActStr = implode(', ', $actions);
            $vocab[$ns] = sprintf(_kt("%s (%s)"), $aInfo['name'], $sActStr);
        }

        $oForm->setWidgets(array(
            array('ktcore.widgets.selection', array(
                'label' => _kt("Restriction Type"),
                'name' => 'guard_name',
                'vocab' => $vocab,
                'simple_select' => false,
                'required' => true,
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'guard_name',
                'output' => 'guard_name',
            )),
        ));
        return $oForm;
    }

    function do_manageguards() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/restrictions_edit');
        $this->oPage->setBreadcrumbDetails(_kt("Manage Restrictions"));
        $this->breadcrumbs_security();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Restrictions"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("", "transitionsecurityoverview", true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oTransition->getHumanName(),
        );
        $restrictions = KTWorkflowUtil::getGuardTriggersForTransition($this->oTransition);
        $add_form = $this->form_addtransitionguard();

        $oTemplate->setData(array(
            'context' => $this,
            'add_form' => $add_form,
            'aGuardTriggers' => $restrictions,
        ));
        return $oTemplate->render();
    }

    function do_addguard() {
        $oForm = $this->form_addtransitionguard();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];

        if (!empty($errors)) {
            return $oForm->handleError();
        }

        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger(KTUtil::arrayGet($data, 'guard_name'));
        if (PEAR::isError($oTrigger)) {
            return $oForm->handleError(_kt('Unable to add trigger.'));
        }

        $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
            'transitionid' => KTUtil::getId($this->oTransition),
            'namespace' =>  KTUtil::arrayGet($data, 'guard_name'),
            'config' => array(),
        ));

        if (PEAR::isError($oTriggerConfig)) {
            return $oForm->handleError(_kt('Unable to add trigger.') . $oTriggerConfig->getMessage());
        }

        // now, if the trigger is editable...
        $oTrigger->loadConfig($oTriggerConfig);
        if ($oTrigger->bIsConfigurable) {
            $this->successRedirectTo('editguardtrigger', _kt("New restriction added. This restriction requires configuration:  please specify this below."), array('fTriggerInstanceId' => $oTriggerConfig->getId()));
        } else {
            $this->successRedirectTo('manageguards', _kt("New restriction added."));
        }
        exit(0);
    }


    function do_editguardtrigger() {
        $this->oPage->setBreadcrumbDetails(_kt("Edit Restriction"));
        $this->breadcrumbs_security();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Restrictions"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("", "transitionsecurityoverview", true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oTransition->getHumanName(),
        );

        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            return $this->errorRedirectTo('manageguards', _kt('Unable to load trigger.'));
        }

        // grab the transition ns from the request.
        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('editTransition', _kt('Unable to add trigger.'), 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        return $oTrigger->displayConfiguration($this->meldPersistQuery(array('fTriggerInstanceId' => $oTriggerInstance->getId()), 'saveguardtrigger', true));
    }

    // }}}

    function do_saveguardtrigger() {
        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            $this->errorRedirectTo('manageguards', _kt('Unable to load trigger.'));
            exit(0);
        }

        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('manageguards', _kt('Unable to load trigger.'));
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        $res = $oTrigger->saveConfiguration();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo('manageguards', _kt('Unable to save trigger: ') . $res->getMessage());
            exit(0);
        }

        $this->successRedirectTo('manageguards', _kt('Trigger saved.'));
        exit(0);
    }

    function do_deleteguardtrigger() {
        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            return $this->errorRedirectTo('manageguards', _kt('Unable to load trigger.'));
        }

        // grab the transition ns from the request.
        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();
        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('manageguards', _kt('Unable to load trigger.'));
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        $res = $oTriggerInstance->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo('editTransition', _kt('Unable to delete trigger: ') . $res->getMessage(), 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
            exit(0);
        }

        $this->successRedirectTo('manageguards', _kt('Trigger deleted.'));
        exit(0);
    }


    // ----------------- Effects ---------------------
    function breadcrumb_effects() {
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Workflow Effects"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","effects",true)),
        );
    }

    function do_effects() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/effects_overview');
        $this->breadcrumb_effects();


        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
        ));
        return $oTemplate->render();
    }


    function form_addtransitionaction() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.admin.workflow.addaction',
            'label' => _kt("Add New Transition Action"),
            'action' => 'addactiontrigger',
            'cancel_action' => 'managetransitionactions',
            'fail_action' => 'managetransitionactions',
            'submit_label' => _kt("Add Action"),
            'context' => $this,
        ));

        $oTriggerSingleton =& KTWorkflowTriggerRegistry::getSingleton();
        $aTriggerList = $oTriggerSingleton->listWorkflowTriggers();
        $vocab = array();
        foreach ($aTriggerList as $ns => $aTriggerInfo) {
            $aInfo = $aTriggerInfo; // i am lazy.
            //var_dump($aInfo);
            $actions = array();
            if ($aInfo['guard']) {
                $actions[] = _kt('Guard');
            }
            if ($aInfo['action']) {
                $actions[] = _kt('Action');
            } else {
                continue;
            }
            $sActStr = implode(', ', $actions);
            $vocab[$ns] = sprintf(_kt("%s (%s)"), $aInfo['name'], $sActStr);
        }

        $oForm->setWidgets(array(
            array('ktcore.widgets.selection', array(
                'label' => _kt("Action/Effect Type"),
                'name' => 'action_name',
                'vocab' => $vocab,
                'simple_select' => false,
                'required' => true,
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'action_name',
                'output' => 'action_name',
            )),
        ));
        return $oForm;
    }

    function do_transitionactions() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/transition_effects_overview');
        $this->breadcrumb_effects();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Effects"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","transitionactions",true)),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Overview"));
        $this->oPage->setTitle(_kt("Transition Effects Overview"));

        $aTransitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);

        $oTemplate->setData(array(
            'context' => $this,
            'transitions' => $aTransitions,
       ));
        return $oTemplate->render();
    }


    // helper
    function describeTransitionActions($oTransition) {
        $actions = KTWorkflowUtil::getActionTriggersForTransition($oTransition);

        if (empty($actions)) {
            return '&mdash;';
        }

        $action_text = array();
        foreach ($actions as $oAction) {
            $action_text[] = $oAction->getConfigDescription();
        }

        return implode('. ', $action_text);
    }


    function do_managetransitionactions() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/transition_actions_edit');
        $this->breadcrumb_effects();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Effects"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","transitionactions",true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oTransition->getHumanName(),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Manage Transition Actions"));

        $actions = KTWorkflowUtil::getActionTriggersForTransition($this->oTransition);
        $add_form = $this->form_addtransitionaction();

        $oTemplate->setData(array(
            'context' => $this,
            'add_form' => $add_form,
            'aActionTriggers' => $actions,
        ));
        return $oTemplate->render();
    }

    function do_addactiontrigger() {
        $oForm = $this->form_addtransitionaction();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];

        if (!empty($errors)) {
            return $oForm->handleError();
        }

        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger(KTUtil::arrayGet($data, 'action_name'));
        if (PEAR::isError($oTrigger)) {
            return $oForm->handleError(_kt('Unable to add trigger.'));
        }

        $oTriggerConfig = KTWorkflowTriggerInstance::createFromArray(array(
            'transitionid' => KTUtil::getId($this->oTransition),
            'namespace' =>  KTUtil::arrayGet($data, 'action_name'),
            'config' => array(),
        ));

        if (PEAR::isError($oTriggerConfig)) {
            return $oForm->handleError(_kt('Unable to add trigger.') . $oTriggerConfig->getMessage());
        }

        // now, if the trigger is editable...
        $oTrigger->loadConfig($oTriggerConfig);
        if ($oTrigger->bIsConfigurable) {
            $this->successRedirectTo('editactiontrigger', _kt("New action added. This action requires configuration:  please specify this below."), array('fTriggerInstanceId' => $oTriggerConfig->getId()));
        } else {
            $this->successRedirectTo('managetransitionactions', _kt("New restriction added."));
        }
        exit(0);
    }


    function do_editactiontrigger() {
        $this->breadcrumb_effects();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Transition Effects"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","transitionactions",true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oTransition->getHumanName(),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Edit Transition Action"));

        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            return $this->errorRedirectTo('managetransitionactions', _kt('Unable to load trigger.'));
        }

        // grab the transition ns from the request.
        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to add trigger.'), 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        return $oTrigger->displayConfiguration($this->meldPersistQuery(array('fTriggerInstanceId' => $oTriggerInstance->getId()), 'saveactiontrigger', true));
    }

    // }}}

    function do_saveactiontrigger() {
        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to load trigger.'));
            exit(0);
        }

        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();

        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to load trigger.'));
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        $res = $oTrigger->saveConfiguration();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to save trigger: ') . $res->getMessage());
            exit(0);
        }

        $this->successRedirectTo('managetransitionactions', _kt('Trigger saved.'));
        exit(0);
    }

    function do_deleteactiontrigger() {
        $oTriggerInstance =& KTWorkflowTriggerInstance::get($_REQUEST['fTriggerInstanceId']);
        if (PEAR::isError($oTriggerInstance)) {
            return $this->errorRedirectTo('managetransitionactions', _kt('Unable to load trigger.'));
        }

        // grab the transition ns from the request.
        $KTWFTriggerReg =& KTWorkflowTriggerRegistry::getSingleton();
        $this->startTransaction();

        $oTrigger = $KTWFTriggerReg->getWorkflowTrigger($oTriggerInstance->getNamespace());
        if (PEAR::isError($oTrigger)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to load trigger.'));
            exit(0);
        }
        $oTrigger->loadConfig($oTriggerInstance);

        $res = $oTriggerInstance->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo('managetransitionactions', _kt('Unable to delete trigger: ') . $res->getMessage(), 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
            exit(0);
        }

        $this->successRedirectTo('managetransitionactions', _kt('Trigger deleted.'));
        exit(0);
    }

    function do_managenotifications() {
        $this->breadcrumb_effects();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Notifications"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","managenotifications",true)),
        );

        $oTemplate =& $this->oValidator->validateTemplate("ktcore/workflow/admin/manage_notifications");
        $oTemplate->setData(array(
            'context' => $this,
            'states' => KTWorkflowState::getByWorkflow($this->oWorkflow),
        ));
        return $oTemplate->render();
    }

    function describeStateNotifications($oState) {
        $aAllowed = KTWorkflowUtil::getInformedForState($oState);

        $aUsers = array();
        $aGroups = array();
        $aRoles = array();

        foreach (KTUtil::arrayGet($aAllowed,'user',array()) as $iUserId) {
            $oU = User::get($iUserId);
            if (PEAR::isError($oU) || ($oU == false)) {
                continue;
            } else {
                $aUsers[] = $oU->getName();
            }
        }

        foreach (KTUtil::arrayGet($aAllowed,'group',array()) as $iGroupId) {
            $oG = Group::get($iGroupId);
            if (PEAR::isError($oG) || ($oG == false)) {
                continue;
            } else {
                $aGroups[] = $oG->getName();
            }
        }

        foreach (KTUtil::arrayGet($aAllowed,'role',array()) as $iRoleId) {
            $oR = Role::get($iRoleId);
            if (PEAR::isError($oR) || ($oR == false)) {
                continue;
            } else {
                $aRoles[] = $oR->getName();
            }
        }

        $sNotify = '';
        if (!empty($aUsers)) {
            $sNotify .= '<em>' . _kt('Users') . ':</em> ';
            $sNotify .= implode(', ', $aUsers);
        }

        if (!empty($aGroups)) {
            if (!empty($sNotify)) { $sNotify .= ' &mdash; '; }
            $sNotify .= '<em>' . _kt('Groups') . ':</em> ';
            $sNotify .= implode(', ', $aGroups);
        }

        if (!empty($aRoles)) {
            if (!empty($sNotify)) { $sNotify .= ' &mdash; '; }
            $sNotify .= '<em>' . _kt('Roles') . ':</em> ';
            $sNotify .= implode(', ', $aRoles);
        }

        if (empty($sNotify)) { $sNotify = _kt('No notifications.'); }

        return $sNotify;
    }

    function descriptorToJSON($aAllowed) {
        $values = array();

        foreach (KTUtil::arrayGet($aAllowed,'user',array()) as $oU) {
            if (!is_object($oU)) {
                $iUserId = $oU;
                $oU = User::get($iUserId);
            } else {
                $iUserId = $oU->getId();
            }

            if (PEAR::isError($oU) || ($oU == false)) {
                continue;
            } else {
                $values[sprintf("users[%d]", $iUserId)] = sprintf(_kt('User: %s'), $oU->getName());
            }
        }

        foreach (KTUtil::arrayGet($aAllowed,'group',array()) as $oG) {
            if (!is_object($oG)) {
                $iGroupId = $oG;
                $oG = Group::get($iGroupId);
            } else {
                $iGroupId = $oG->getId();
            }
            if (PEAR::isError($oG) || ($oG == false)) {
                continue;
            } else {
                $values[sprintf("groups[%d]", $iGroupId)] = sprintf(_kt('Group: %s'), $oG->getName());
            }
        }

        foreach (KTUtil::arrayGet($aAllowed,'role',array()) as $oR) {
            if (!is_object($oR)) {
                $iRoleId = $oR;
                $oR = Role::get($iRoleId);
            } else {
                $iRoleId = $oR->getId();
            }

            if (PEAR::isError($oR) || ($oR == false)) {
                continue;
            } else {
                $values[sprintf("roles[%d]", $iRoleId)] = sprintf(_kt('Role: %s'), $oR->getName());
            }
        }

        return $values;
    }

    function form_editnotifications($oState) {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'context' => $this,
            'label' => _kt("Edit State Notifications."),
            'identifier' => 'ktcore.workflow.notifications',
            'submit_label' => _kt("Update Notifications"),
            'cancel_action' => 'managenotifications',
            'action' => 'savenotifications',
            'fail_action' => 'editnotifications',
        ));
        $preval = KTWorkflowUtil::getInformedForState($oState);
        $oForm->setWidgets(array(
            array('ktcore.widgets.descriptorselection', array(
                'label' => _kt("Users to inform"),
                'description' => _kt("Select which users, groups and roles to be notified."),
                'name' => 'users',
                'src' => KTUtil::addQueryStringSelf($this->meldPersistQuery(array('json_action'=> 'notificationusers'), "json")),
                'value' => $this->descriptorToJSON($preval),
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.array', array(
                'test' => 'users',
                'output' => 'users',
            )),
        ));
        return  $oForm;
    }

    function do_editnotifications() {
        $this->breadcrumb_effects();
        $this->aBreadcrumbs[] = array(
            'name' => _kt("Notifications"),
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","managenotifications",true)),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $this->oState->getHumanName(),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Edit State Notifications"));

        $oForm = $this->form_editnotifications($this->oState);
        return $oForm->renderPage();
    }

    function do_savenotifications() {


        $oForm = $this->form_editnotifications($this->oState);
        $res = $oForm->validate();

        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }

        $data = $res['results'];
        // now, an annoying problem is that we do *not* have the final set.
        // so we need to get the original, add the new ones, remove the old ones.
        //
        // because its not *really* isolated properly, we need to post-process
        // the data.

        // we need the old one
        $aAllowed = KTWorkflowUtil::getInformedForState($this->oState);

        $user_pattern = '|users\[(.*)\]|';
        $group_pattern = '|groups\[(.*)\]|';
        $role_pattern = '|roles\[(.*)\]|';

        $user = KTUtil::arrayGet($aAllowed, 'user', array());
        $group = KTUtil::arrayGet($aAllowed, 'group', array());
        $role = KTUtil::arrayGet($aAllowed, 'role', array());

        // do a quick overpass
        $newAllowed = array();
        if (!empty($user)) { $newAllowed['user'] = array_combine($user, $user); }
        else { $newAllowed['user'] = array(); }
        if (!empty($group)) { $newAllowed['group'] = array_combine($group, $group); }
        else { $newAllowed['group'] = array(); }
        if (!empty($role)) { $newAllowed['role'] = array_combine($role, $role); }
        else { $newAllowed['role'] = array(); }

        $added = explode(',', $data['users']['added']);
        $removed = explode(',', $data['users']['removed']);

        foreach ($added as $akey) {
            $matches = array();
            if (preg_match($user_pattern, $akey, $matches)) { $newAllowed['user'][$matches[1]] = $matches[1]; }
            else if (preg_match($group_pattern, $akey, $matches)) { $newAllowed['group'][$matches[1]] = $matches[1]; }
            else if (preg_match($role_pattern, $akey, $matches)) { $newAllowed['role'][$matches[1]] = $matches[1]; }
        }

        foreach ($removed as $akey) {
            $matches = array();
            if (preg_match($user_pattern, $akey, $matches)) { unset($newAllowed['user'][$matches[1]]); }
            else if (preg_match($group_pattern, $akey, $matches)) { unset($newAllowed['group'][$matches[1]]); }
            else if (preg_match($role_pattern, $akey, $matches)) { unset($newAllowed['role'][$matches[1]]); }
        }

        // FIXME check that these are all users.

        $res = KTWorkflowUtil::setInformedForState($this->oState, $newAllowed);
        if (PEAR::isError($res)) {
            return $oForm->handleError($res->getMessage());
        }

        $this->successRedirectTo("managenotifications", _kt("Notifications updated."));
    }

    function json_notificationusers() {
        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        if ($sFilter == false) {
        	$values = array('off' => _kt('-- Please filter --')); // default
        }
        $sFilter = trim($sFilter);
    	$values = array('off' => _kt('-- Please filter --')); // default

    	if (!empty($sFilter)) {
    	    $allowed = array();
            // Modified Jarrett Jordaan Only notify enabled users
    	    $q = sprintf('name like "%%%s%%" AND disabled = 0', DBUtil::escapeSimple($sFilter));
    	    $aUsers = User::getList($q);
    	    $q = sprintf('name like "%%%s%%"', DBUtil::escapeSimple($sFilter));
        	$aGroups = Group::getList($q);
            $aRoles = Role::getList($q);
            $empty = true;

            if (!PEAR::isError($aUsers)) {
                $allowed['user'] = $aUsers;
                if (!empty($aUsers)) {
                    $empty = false;
                }
            }

            if (!PEAR::isError($aGroups)) {
                $allowed['group'] = $aGroups;
                if (!empty($aGroups)) {
                    $empty = false;
                }
            }

            if (!PEAR::isError($aRole)) {
                $allowed['role'] = $aRoles;
                if (!empty($aRoles)) {
                    $empty = false;
                }

            }

            if ($empty) {
            	$values = array('off'=>'-- No results --'); // default
            } else {
                $values = $this->descriptorToJSON($allowed);
            }
    	}

    	return $values;
    }

    /* ---------------- GraphViz / DOT support --------------- */
    //
    // FIXME detect and handle the support issues sanely.

    var $state_names;
    var $transition_names;

    function get_graph($oWorkflow) {

        $fontsize = 11.0;
        $fontname = "Times-Roman";

        $opts = array(
            'fontsize' => $fontsize,
            'fontname' => $fontname,
        );

        $graph = new Image_GraphViz(true, $opts);
        $graph->dotCommand = $this->dotCommand;

        // we need all states & transitions
        // FIXME do we want guards?

        // we want to enable link-editing, and indicate that transitions "converge"
        // so we use a temporary "node" for transitions
        // we also use a "fake" URL which we catch later
        // so we can give good "alt" tags.

        $states = KTWorkflowState::getByWorkflow($oWorkflow);
        $transitions = KTWorkflowTransition::getByWorkflow($oWorkflow);

        $this->state_names = array();
        $this->transition_names = array();

        $state_opts = array(
            'shape' => 'box',
            'fontsize' => $fontsize,
            'fontname' => $fontname,
        );

        $transition_opts = array(
            'shape' => 'box',
            'color' => '#ffffff',
            'fontsize' => $fontsize,
            'fontname' => $fontname,
        );

        $finaltransition_opts = array(
            'color' => '#333333',
        );

        $sourcetransition_opts = array(
            'color' => '#999999',
        );

        // to make this a little more useful, we want to cascade our output from
        // start to end states - this will tend to give a better output.
        //
        // to do this, we need to order our nodes in terms of "nearness" to the
        // initial node.

        $processing_nodes = array();
        $sorted_ids = array();

        $availability = array();
        $sources = array();
        $destinations = array();

        $states = KTUtil::keyArray($states);
        $transitions = KTUtil::keyArray($transitions);

        foreach ($transitions as $tid => $oTransition) {
            $sources[$tid] = KTWorkflowAdminUtil::getSourceStates($oTransition, array('ids' => true));
            $destinations[$tid] = $oTransition->getTargetStateId();
            foreach ($sources[$tid] as $sourcestateid) {
                $av = (array) KTUtil::arrayGet($availability, $sourcestateid, array());
                $av[] = $tid;
                $availability[$sourcestateid] = $av;
            }
        }

        //var_dump($sources); exit(0);

        //var_dump($availability); exit(0);

        $processing = array($oWorkflow->getStartStateId());
        while (!empty($processing)) {
            $active = array_shift($processing);

            if (!$processing_nodes[$active]) {
                // mark that we've seen this node

                $processing_nodes[$active] = true;
                $sorted[] = $active;

                // now add all reachable nodes to the *end* of the queue.
                foreach ((array) $availability[$active] as $tid) {
                    $next = $destinations[$tid];
                    if (!$processing_nodes[$next]) {
                        $processing[] = $next;
                    }
                }
            }
            //var_dump($processing);
        }

        //var_dump($sorted); exit(0);

        foreach ($sorted as $sid) {

            $oState = $states[$sid];

            $this->state_names[$oState->getId()] = $oState->getHumanName();

            $local_opts = array(
                'URL'   => sprintf("s%d", $oState->getId()),
                'label' => $oState->getHumanName(),
                'color' => '#666666',
            );
            if ($oState->getId() == $oWorkflow->getStartStateId()) {
                $local_opts['color'] = '#000000';
                $local_opts['style'] = 'filled';
                $local_opts['fillcolor'] = '#cccccc';
            }

            $graph->addNode(
                sprintf('state%d', $oState->getId()),
                KTUtil::meldOptions($state_opts, $local_opts));
        }

        foreach ($transitions as $tid => $oTransition) {
            $name = sprintf('transition%d', $tid);
            $this->transition_names[$oTransition->getId()] = $oTransition->getHumanName();
            // we "cheat" and use

            $graph->addNode(
                $name,
                KTUtil::meldOptions($transition_opts,
                    array(
                      'URL'   => sprintf("t%d", $tid),
                      'label' => $oTransition->getHumanName(),
                    )
                ));

            $dest = sprintf("state%d", $oTransition->getTargetStateId());


            $graph->addEdge(
                array($name => $dest),
                $finaltransition_opts
            );

            foreach ($sources[$tid] as $source_id) {
                $source_name = sprintf("state%d", $source_id);
                $graph->addEdge(
                    array($source_name => $name),
                    $sourcetransition_opts
                );
            }
        }

        // some simple analysis

        $errors = array();
        $info = array();

        $sourceless_transitions = array();
        foreach ($transitions as $tid => $oTransition) {
            if (empty($sources[$tid])) {
                $sourceless_transitions[] = $oTransition->getHumanName();
            }
        }

        if (!empty($sourceless_transitions)) {
            $errors[] = sprintf(_kt("Some transitions have no source states: %s"), implode(', ', $sourceless_transitions));
        }

        $unlinked_states = array();
        foreach ($states as $sid => $oState) {
            if (!$processing_nodes[$sid]) {  // quick sanity check
                $unlinked_states[] = $oState->getHumanName();
            }
        }
        if (!empty($unlinked_states)) {
            $errors[] = sprintf(_kt("Some states cannot be reached from the initial state (<strong>%s</strong>): %s"), $states[$oWorkflow->getStartStateId()]->getHumanName() , implode(', ', $unlinked_states));
        }

        $data = array(
            'graph' => $graph,
            'errors' => $errors,
            'info' => $info,
        );

        return $data;
    }

    function do_graphimage() {
        header('Content-Type: image/jpeg');
        $graph = $this->get_graph($this->oWorkflow);
        $graph['graph']->image('jpeg');
        exit(0);
    }

    function do_graphrepresentation() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/graphrep');

        // this is not ideal
        // is there no way to get graphviz to give us this more "usefully"
        $graph = $this->get_graph($this->oWorkflow);
        $rdata = $graph['graph']->fetch("imap");

        // we can skip some of this.
        $data = explode("\n", $rdata);
        $data = array_slice($data, 1, -1);

        if (false) {
            print '<pre>';
            print print_r($data, true); exit(0);
        }
        $pat = '|^([\w]+).    # rect, circle, etc.
            ([^ ]+).       # href
            ([\d]+),        # x0
            ([\d]+).         # x1
            ([\d]+),        # y0
            ([\d]+)         # y1
        |x';

        $coords = array();
        foreach ($data as $row) {
            $matches = array();
            if (preg_match($pat, $row, $matches)) {
                $rowdata = array_slice($matches, 1);
                list($shape, $href, $x0, $y0, $x1, $y1) = $rowdata;

                // FIXME sanity check, we only handle "rect"

                $real_href = null;
                $m = array();
                $alt = null;
                if (preg_match('|^(\w)([\d]+)$|', $href, $m)) {
                    if ($m[1] == 's') {
                        $real_href = KTUtil::addQueryStringSelf($this->meldPersistQuery(array('fStateId' => $m[2]), "editstate"));
                        $alt = sprintf('Edit State "%s"', $this->state_names[$m[2]]);
                    } else {
                        $real_href = KTUtil::addQueryStringSelf($this->meldPersistQuery(array('fTransitionId' => $m[2]), "edittransition"));
                        $alt = sprintf('Edit Transition "%s"', $this->transition_names[$m[2]]);
                    }
                }
                $coords[] = array(
                    'shape' => $shape,
                    'href' => $real_href,
                    'coords' => sprintf("%d,%d,%d,%d", $x0, $y0, $x1, $y1),
                    'alt' => $alt,
                );


            }
        }
        if (false) {
            print '<pre>'; var_dump($coords); exit(0);
        }
        $oTemplate->setData(array(
            'context' => $this,
            'coords' => $coords,
        ));
        print $oTemplate->render();
        exit(0);
    }


}

?>

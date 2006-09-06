<?php

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
        $aAdminPages[] = array('name' => _kt('Select different workflow'), 'query' => 'action=main&fWorkflowId=' . $this->oWorkflow->getId());        
    
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

    function check() {
        $res = parent::check();
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
        
        return $res;
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
            $this->addInfoMessage(sprintf(_kt("This workflow is currently marked as disabled.  No new documents can be assigned to this workflow until it is enabled.  To change this, please <a href=\"%s\">edit</a> the workflow's base properties."), KTUtil::addQueryString($_SERVER['PHP_SELF'], $this->meldPersistQuery("","editcore"))));
        }

        if ($this->oWorkflow->getStartStateId() == false) {
            $this->addErrorMessage(sprintf(_kt("No start state is specified for this workflow.  No new documents can be assigned to this workflow until one is assigned. To change this, please <a href=\"%s\">edit</a> the workflow's base properties."), KTUtil::addQueryString($_SERVER['PHP_SELF'], $this->meldPersistQuery("","editcore"))));
        }
        
        // for the basic view
        $start_state_id = $this->oWorkflow->getStartStateId();
        $oState = KTWorkflowState::get($start_state_id);

        if (PEAR::isError($oState)) {
            $state_name = _kt('No starting state.');            
        } else {
            $state_name = $oState->getName();
        }
        

        
        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
            'state_name' => $state_name,
            'workflow' => $this->oWorkflow,
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
                'value' => $this->oWorkflow->getName(),        
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
    function do_basic() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/basic_overview');            
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("", "basic")),
            'name' => _kt("States and Transitions"),
        );
        $this->oPage->setBreadcrumbDetails(_kt("Overview"));
        
        $aStates = KTWorkflowState::getByWorkflow($this->oWorkflow);
        $aTransitions = KTWorkflowTransition::getByWorkflow($this->oWorkflow);        
        
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

            $res = KTWorkflowAdminUtil::saveTransitionSources($oTransition, $source_state_ids);
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
                'description' => _kt('As documents progress through their lifecycle, they pass through a number of <strong>states</strong>.  These states describe a step in the process the document must follow.  Examples of states include "reviewed","submitted" or "pending".  Note that the first state you list is the one in which documents will start the workflow - this can be changed later on. Please enter a list of states, one per line.  State names must be unique, and this includes states already in this workflow.'),
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
                'description' => _kt('In order to move between states, users will cause "transitions" to occur.  These transitions represent processes followed, e.g. "review document", "distribute invoice" or "publish".  Please enter a list of transitions, one per line.  Transition names must be unique.  You\'ll assign transitions to states in the next step.'),
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
        
        $this->successRedirectTo('transitionconnections', _kt("New States Created."), $transition_ids_query);
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
                'description' => _kt('As documents progress through their lifecycle, they pass through a number of <strong>states</strong>.  These states describe a step in the process the document must follow.  Examples of states include "reviewed","submitted" or "pending".  Note that the first state you list is the one in which documents will start the workflow - this can be changed later on. Please enter a list of states, one per line.  State names must be unique, and this includes states already in this workflow.'),
                'required' => true,
                'value' => $oState->getName(),
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
        // remember that we check for state,
        // and its null if none or an error was passed.
        if (is_null($this->oState)) {
            $this->errorRedirectTo('basic', _kt("No state specified."));
        }
        
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/admin/edit_state');
        $this->oPage->setBreadcrumbDetails(_kt('Manage State Details'));
        
        $oForm = $this->form_editstate($this->oState);
        
        $oTemplate->setData(array(
            'context' => $this,
            'edit_form' => $oForm,        
        ));
        
        return $oTemplate->render();
    }
    
    function do_savestate() {
        $oForm = $this->form_editstate();
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
        
        $this->successRedirectTo('editstate', _kt("State updated."));
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
                'value' => $oTransition->getName(),
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
        $oForm = $this->form_edittransition();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();
        
        // check if any *other* states have this name.
        if ($data['name'] == $this->oTransition->getName()) {
            $this->successRedirectTo('edittransition',_kt("No change in name."));
        } 
        
        // otherwise we're looking for something different if there's a conflict.
        
        if (KTWorkflowTransitions::nameExists($data['name'], $this->oWorkflow)) {
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
        
        $this->successRedirectTo('transition', _kt("Transition updated."));
    }
    
    // ----------------- Security ---------------------
    function do_security() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/security_overview');            
        $this->oPage->setBreadcrumbDetails(_kt("Security"));
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
        ));
        return $oTemplate->render();    
    }
    
    
    // == PERMISSIONS
    function do_permissionsoverview() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/permissions_overview');
        
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
    
        $aEntityList = array('off'=>'-- Please filter --');
    
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
        
        KTPermissionUtil::updatePermissionLookupForState($oState);
        
        $this->successRedirectTo('managepermissions', _kt('Permissions Allocated.'));
    }
    
    // ACTIONS
    
    function do_actionsoverview() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/actions_overview');            
        $this->oPage->setBreadcrumbDetails(_kt("Actions"));
        
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
        $this->oPage->setBreadcrumbDetails(_kt("Actions"));
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
        $this->oPage->setBreadcrumbDetails(_kt("Transition Guards"));

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
        $this->oPage->setBreadcrumbDetails(_kt("Actions"));
        
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
        $this->oPage->setBreadcrumbDetails(_kt('editing restriction'));
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
    function do_effects() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/effects_overview');            
        $this->oPage->setBreadcrumbDetails(_kt("Workflow Effects"));
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'workflow_name' => $this->oWorkflow->getName(),
        ));
        return $oTemplate->render();    
    }    
}

?>

<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');

require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

class WorkflowNavigationPortlet extends KTPortlet {   
    var $oWorkflow;

    function WorkflowNavigationPortlet($sTitle, $oWorkflow = null) {
        $this->oWorkflow = $oWorkflow;
        parent::KTPortlet($sTitle);
    }

    function render() {
        if (is_null($this->oWorkflow)) { return _('No Workflow Selected.'); }
    
        $aAdminPages = array();
        $aAdminPages[] = array('name' => _('Overview'), 'url' => $_SERVER['PHP_SELF'] . '?action=editWorkflow&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _('States'), 'url' => $_SERVER['PHP_SELF'] . '?action=manageStates&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _('Transitions'), 'url' => $_SERVER['PHP_SELF'] . '?action=manageTransitions&fWorkflowId=' . $this->oWorkflow->getId());
        $aAdminPages[] = array('name' => _('Actions'), 'url' => $_SERVER['PHP_SELF'] . '?action=manageActions&fWorkflowId=' . $this->oWorkflow->getId());

    
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/workflow/admin_portlet");
        $aTemplateData = array(
            "context" => $this,
            "aAdminPages" => $aAdminPages,
        );

        return $oTemplate->render($aTemplateData);     
    }
}

class KTWorkflowDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
    var $sHelpPage = 'ktcore/admin/workflow/overview.html';
    var $aWorkflowInfo;
    var $oWorkflow;

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _('Workflows'),
        );
        $this->oWorkflow =& KTWorkflow::get($_REQUEST['fWorkflowId']);
        if (!PEAR::isError($this->oWorkflow)) {
            $this->aBreadcrumbs[] = array(
               'url' => $_SERVER['PHP_SELF'],
               'query' => 'action=editWorkflow&fWorkflowId=' . $this->oWorkflow->getId(),
               'name' => $this->oWorkflow->getName(),
            );
            $this->oPage->addPortlet(new WorkflowNavigationPortlet(_('Workflow'), $this->oWorkflow));        
        }
    
        return true;
    }
    
    // helper function to construct the set of workflow information
    function buildWorkflowInfo($oWorkflow) {
        if ($this->aWorkflowInfo != null) { return $this->aWorkflowInfo; }
    
        $aInfo = array();
        $aInfo['workflow'] = $oWorkflow;
        
        // roles
        $aRoles = Role::getList();
        $aKeyRoles = array();
        foreach ($aRoles as $oRole) { $aKeyRoles[$oRole->getId()] = $oRole; }        
        $aInfo['roles'] = $aKeyRoles;
        
        // groups
        $aGroups = Group::getList();
        $aKeyGroups = array();
        foreach ($aGroups as $oGroup) { $aKeyGroups[$oGroup->getId()] = $oGroup; }
        $aInfo['groups'] = $aKeyGroups;
        
        // states.
        $aStates = KTWorkflowState::getByWorkflow($oWorkflow);
        $aKeyStates = array();
        foreach ($aStates as $oState) { $aKeyStates[$oState->getId()] = $oState; }
        $aInfo['states'] = $aKeyStates;
        
        // transitions
        $aTransitions = KTWorkflowTransition::getByWorkflow($oWorkflow);
        $aKeyTransitions = array();
        foreach ($aTransitions as $oTransition) { $aKeyTransitions[$oTransition->getId()] = $oTransition; }
        $aInfo['transitions'] = $aKeyTransitions;
        
        // permissions
        $aPermissions = KTPermission::getList();
        $aKeyPermissions = array();
        foreach ($aPermissions as $oPermission) { $aKeyPermissions[$oPermission->getId()] = $oPermission; }
        $aInfo['permissions'] = $aKeyPermissions;
        
        // actions
        $aInfo['actions'] = KTDocumentActionUtil::getAllDocumentActions();
        $aKeyActions = array();
        foreach ($aInfo['actions'] as $oAction) { $aKeyActions[$oAction->getName()] = $oAction; }
        $aInfo['actions_by_name'] = $aKeyActions;
        
        $aInfo['controlled_actions'] = KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow);
        
        /*
         * now we need to do the crossmappings.
         */
        
        $aActionsByState = array();
        foreach ($aInfo['states'] as $oState) {
            $aActionsByState[$oState->getId()] = KTWorkflowUtil::getEnabledActionsForState($oState);;
        }
        $aInfo['actions_by_state'] = $aActionsByState;
        
        // FIXME handle notified users and groups
        $aTransitionsFromState = array();
        foreach ($aInfo['states'] as $oState) {
            $aTransitionsFromState[$oState->getId()] = KTWorkflowUtil::getTransitionsFrom($oState);
        }
        $aInfo['transitions_from_state'] = $aTransitionsFromState;
        
        $aTransitionsToState = array();
        foreach ($aInfo['states'] as $oState) {
            $aTransitionsToState[$oState->getId()] = KTWorkflowTransition::getByTargetState($oState);
        }
        $aInfo['transitions_to_state'] = $aTransitionsToState;
        
        $this->aWorkflowInfo = $aInfo;
        
        return $aInfo;
    }
    
    
    function getActionStringForState($oState) {
        $aInfo = $this->aWorkflowInfo;
        
        // no controlled actions => all available
        if (empty($aInfo['controlled_actions'])) { return _('All actions available.'); }
        
        
        $aAlways = array();
        /*
        foreach ($aInfo['actions'] as $iActionId => $aAction) {
            if (!array_key_exists($iActionId, $aInfo['controlled_actions'])) {
                $aAlways[$iActionId] = $aAction; 
            }
        }
        */
        
        $aNamedActions = array();
        foreach ($aInfo['actions_by_state'][$oState->getId()] as $sName) {
            $aNamedActions[] = $aInfo['actions_by_name'][$sName];
        }
        
        $aThese = array_merge($aAlways, $aNamedActions);
        // some controlled.  we need to be careful here:  list actions that _are always_ available
        if (empty($aThese)) { return _('No actions available.'); }
    
        // else
        $aActions = array();
        foreach ($aThese as $oAction) { $aActions[] = $oAction->getDisplayName(); }
        return  implode(', ', $aActions);
    }
    
    function getTransitionToStringForState($oState) {
        $aInfo = $this->aWorkflowInfo;
        //var_dump($aInfo['transitions_to_state'][$oState->getId()]);
        if (($aInfo['workflow']->getStartStateId() != $oState->getId()) && (empty($aInfo['transitions_to_state'][$oState->getId()]))) {
            return '<strong>' . _('This state is unreachable.') . '</strong>';
        }
        
        
        if ($aInfo['workflow']->getStartStateId() == $oState->getId() && (empty($aInfo['transitions_to_state'][$oState->getId()]))) {
            return '<strong>' . _('Documents start in this state') . '</strong>';            
        }
        $aT = array();
        if ($aInfo['workflow']->getStartStateId() == $oState->getId()) {
            $aT[] = '<strong>' . _('Documents start in this state') . '</strong>';            
        }
        
        foreach ($aInfo['transitions_to_state'][$oState->getId()] as $aTransition) { 
            $aT[] = sprintf('<a href="%s?action=editTransition&fWorkflowId=%d&fTransitionId=%d">%s</a>', $_SERVER['PHP_SELF'], $aInfo['workflow']->getId(), $aTransition->getId() ,$aTransition->getName()); 
        }
        
        return implode(', ',$aT);
    }
    
    function getNotificationStringForState($oState) {
        return _('No roles and groups notified.');
    }
  
    function transitionAvailable($oTransition, $oState) {
        $aInfo = $this->aWorkflowInfo;
        
        $val = false;
        foreach ($aInfo['transitions_from_state'][$oState->getId()] as $oT) {
            if ($oTransition->getId() == $oT->getId()) { $val = true; }
        }
        
        return $val;
    }
    
    function actionAvailable($sAction, $oState) {
        $aInfo = $this->aWorkflowInfo;
        
        $val = false;
        
        foreach ($aInfo['actions_by_state'][$oState->getId()] as $oA) {
            
            if ($sAction == $oA) { $val = true; }
        }
        
        return $val;
    }
    
    function getTransitionFromStringForState($oState) {
        $aInfo = $this->aWorkflowInfo;      
        
        if (empty($aInfo['transitions_from_state'][$oState->getId()])) {
            return '<strong>' . _('No transitions available') . '</strong>';
        }
        
        $aT = array();
        foreach ($aInfo['transitions_from_state'][$oState->getId()] as $aTransition) { 
            $aT[] = sprintf('<a href="%s?action=editTransition&fWorkflowId=%d&fTransitionId=%d">%s</a>', $_SERVER['PHP_SELF'], $aInfo['workflow']->getId(), $aTransition->getId() ,$aTransition->getName()); 
        }
        return implode(', ', $aT);
    }
    
    // {{{ WORKFLOW HANDLING
    // {{{ do_main
    function do_main () {
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_('Name'),_('A human-readable name for the workflow.'), 'fName', null, $this->oPage, true);
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/workflow/listWorkflows');
        $oTemplate->setData(array(
            'context' => $this,
            'aWorkflow' => KTWorkflow::getList(),
            'add_fields' => $add_fields,            
        ));
        return $oTemplate;
    }
    // }}}
    
    // {{{ do_editWorkflow
    function do_editWorkflow() {
        
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editWorkflow');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        
        
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        $aPermissions = $aInfo['permissions'];
        $aStates = $aInfo['states'];
        
        $edit_fields = array();
        $edit_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the workflow.'), 'fName', $oWorkflow->getName(), $this->oPage, true);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None - documents cannot use this workflow.';
        foreach($aStates as $state) {
            $vocab[$state->getId()] = $state->getName();
        } 
        $aOptions['vocab'] = $vocab;        
        $edit_fields[] = new KTLookupWidget(_('Starting State'), _('When a document has this workflow applied to it, to which state should it initially be set.  <strong>Note that workflows without a starting state cannot be applied to documents.</strong>'), 'fStartStateId', $oWorkflow->getStartStateId(), $this->oPage, false, null, null, $aOptions);
        if (is_null($oWorkflow->getStartStateId())) {
            $this->oPage->addInfo(_('This workflow is currently disabled.  To enable it, please assign a starting state in the "Edit workflow properties" box.'));
        }
        
        /*
        $add_state_fields = array();
        $add_state_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the state.'), 'fName', null, $this->oPage, true);

        
        */
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,
            
            'aStates' => $aStates,
            'aTransitions' => $aInfo['transitions'],
            'aPermissions' => $aPermissions,
            'aActions' => $aInfo['actions'],
            'aActionsSelected' => $aInfo['controlled_actions'],
            
            // info
            'workflow_info' => $aInfo,
            
            // subform
            'edit_fields' => $edit_fields,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_saveWorkflow
    function do_saveWorkflow() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        
        $aOptions = array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' .  $oWorkflow->getId()),
        );
        
        $sName = $this->oValidator->validateString($_REQUEST['fName'], $aOptions);
        
        $oWorkflow->setName($sName);
        $oWorkflow->setHumanName($sName);
        
        if (!empty($_REQUEST['fStartStateId'])) {
            $oWorkflow->setStartStateId($_REQUEST['fStartStateId']);
        } else {
            $oWorkflow->setStartStateId(null);
        }
        
        $res = $oWorkflow->update();
        
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' . $oWorkflow->getId()),
            'message' => _('Error saving workflow'),
        ));
        
        $this->successRedirectTo('editWorkflow', _('Changes saved'), 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // {{{ do_newWorkflow
    function do_newWorkflow() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        
        $sName = KTUtil::arrayGet($_REQUEST, 'fName');
        $sName = $this->oValidator->validateEntityName('KTWorkflow', 'workflow', $sName, $aErrorOptions);
            

/*        if(!PEAR::isError(KTWorkflow::getByName($sName))) {
            $this->errorRedirectToMain(_("A state with that name already exists"));
        }*/
            
        $res = KTWorkflow::createFromArray(array(
            'name' => $sName,
            'humanname' => $sName,
        ));
        $this->oValidator->notError($res, array(
            'redirect_to' => array('main'),
            'message' => _('Could not create workflow'),
        ));
        $this->successRedirectTo('editWorkflow', _('Workflow created'), 'fWorkflowId=' . $res->getId());
        exit(0);
    }
    // }}}

    function do_manageActions() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/manageActions');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);     
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,

            'aActions' => $aInfo['actions'],
            'aActionsSelected' => $aInfo['controlled_actions'],
                       
            // info
            'workflow_info' => $aInfo,
        ));
        return $oTemplate;        
        
    }

    function do_manageStates() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/manageStates');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);        
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,
            
            // info
            'workflow_info' => $aInfo,
        ));
        return $oTemplate;
    }

    function do_createState() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/createState');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);        
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the state.'), 'fName', null, $this->oPage, true);
        
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,
            
            // info
            'workflow_info' => $aInfo,

            'aActions' => KTDocumentActionUtil::getDocumentActionsByNames(KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow)),
            'aActionsSelected' => KTWorkflowUtil::getEnabledActionsForState($oState),
            'aGroups' => Group::getList(),
            'aRoles' => Role::getList(),
            'aUsers' => User::getList(),            
            
            // subform
            'add_fields' => $add_fields,
        ));
        return $oTemplate;
    }

    function do_manageTransitions() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/manageTransitions');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);        
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
            
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,
                       
            // info
            'workflow_info' => $aInfo,
            
            // subform
            'add_fields' => $add_transition_fields,
        ));
        return $oTemplate;
    }

    function do_createTransition() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/createTransition');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);        
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        $aPermissions = $aInfo['permissions'];
        $aGroups = $aInfo['groups'];
        $aRoles = $aInfo['roles'];
        $aConditions = KTSavedSearch::getConditions();
        
        $add_transition_fields = array();
        $add_transition_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the transition.'), 'fName', null, $this->oPage, true);
        $aOptions = array();
        $vocab = array();
        foreach($aInfo['states'] as $state) {
            $vocab[$state->getId()] = $state->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Destination State'), _('Once this transition is complete, which state should the document be in?'), 'fTargetStateId', $oWorkflow->getStartStateId(), $this->oPage, true, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        foreach($aInfo['permissions'] as $permission) {
            $vocab[$permission->getId()] = $permission->getHumanName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Guard Permission.'), _('Which permission must the user have in order to follow this transition?'), 'fPermissionId', NULL, $this->oPage, true, null, null, $aOptions);
        
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aGroups as $group) {
            $vocab[$group->getId()] = $group->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Guard Group.'), _('Which group must the user belong to in order to follow this transition?'), 'fGroupId', NULL, $this->oPage, false, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aRoles as $role) {
            $vocab[$role->getId()] = $role->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Guard Role.'), _('Which role must the user have in order to follow this transition?'), 'fRoleId', NULL, $this->oPage, false, null, null, $aOptions);
        
        if (!empty($aConditions)) {
            $aOptions = array();
            $vocab = array();
            $vocab[0] = 'None';
            foreach($aConditions as $condition) {
                $vocab[$condition->getId()] = $condition->getName();
            } 
            $aOptions['vocab'] = $vocab;
            $edit_fields[] = new KTLookupWidget(_('Guard Condition.'), _('Which condition (stored search) must be satisfied before the transition can take place?'), 'fConditionId', NULL, $this->oPage, false, null, null, $aOptions);
        }
        
        
        
        $oTemplate->setData(array(
            'context' => $this,
            'oWorkflow' => $oWorkflow,
                       
            // info
            'workflow_info' => $aInfo,
            
            // subform
            'add_fields' => $add_transition_fields,
        ));
        return $oTemplate;
    }

    function do_setTransitionAvailability() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editWorkflow');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        
        $transitionMap = (array) KTUtil::arrayGet($_REQUEST, 'fTransitionAvailability');
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        $this->startTransaction();
        foreach ($aInfo['states'] as $oState) {
            
            $a = (array) $transitionMap[$oState->getId()];
            $transitions = array();
            foreach ($a as $tid => $on) { $transitions[] = $tid; }
            
            $res = KTWorkflowUtil::saveTransitionsFrom($oState, $transitions);
            if (PEAR::isError($res)) {
                $this->errorRedirectTo('manageTransitions', _('Error updating transitions:') . $res->getMessage(), sprintf('fWorkflowId=%d', $oWorkflow->getId()));
            }
        }
        $this->commitTransaction();
        
        $this->successRedirectTo('manageTransitions', _('Transition Availability updated.'), sprintf('fWorkflowId=%d', $oWorkflow->getId()));
    }
    
    
    function do_updateActionAvailability() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        
        $actionMap = (array) KTUtil::arrayGet($_REQUEST, 'fAvailableActions');
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        $this->startTransaction();
        foreach ($aInfo['states'] as $oState) {
            
            $a = (array) $actionMap[$oState->getId()];
            $actions = array_keys($a);
            
            
            
            $res = KTWorkflowUtil::setEnabledActionsForState($oState, $actions);
            if (PEAR::isError($res)) {
                $this->errorRedirectTo('manageActions', _('Error updating actions:') . $res->getMessage(), sprintf('fWorkflowId=%d', $oWorkflow->getId()));
            }
        }
        $this->commitTransaction();
        
        $this->successRedirectTo('manageActions', _('Action availability updated.'), sprintf('fWorkflowId=%d', $oWorkflow->getId()));
    }
    
    // {{{ do_setWorkflowActions
    function do_setWorkflowActions() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::setControlledActionsForWorkflow($oWorkflow, $_REQUEST['fActions']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' . $oWorkflow->getId()),
            'message' => _('Error saving workflow controlled actions'),
        ));
        $this->successRedirectTo('manageActions', _('Controlled actions changed.'), 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // }}}

    // {{{ STATE HANDLING
    //
    // {{{ do_newState
    function do_newState() {
        $iWorkflowId = (int) $_REQUEST['fWorkflowId'];
        
        $aErrorOptions = array(
            'redirect_to' => array('editWorkflow', sprintf('fWorkflowId=%d', $iWorkflowId)),
        );

        $oWorkflow =& $this->oValidator->validateWorkflow($iWorkflowId);
        
        // validate name
        $sName = $this->oValidator->validateString($_REQUEST['fName'], $aErrorOptions);
        
        // check there are no other states by that name in this workflow
        $aStates = KTWorkflowState::getList(sprintf("workflow_id = %d and name = '%s'", $iWorkflowId, $sName));
        if(count($aStates)) {
            $this->errorRedirectTo(implode('&', $aErrorOptions['redirect_to']), _("A state by that name already exists"));
        }
        
        $oState = KTWorkflowState::createFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $sName,
            'humanname' => $sName,
        ));
        
        $this->oValidator->notError($oState, array(
            'redirect_to' => array('createState', 'fWorkflowId=' .  $oWorkflow->getId()),
            'message' => _('Could not create workflow state'),
        ));
        
        $res = KTWorkflowUtil::setEnabledActionsForState($oState, $_REQUEST['fActions']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId(), '&fStateId=' .  $oState->getId()),
            'message' => _('Error saving state enabled actions'),
        ));
        
        $this->successRedirectTo('editState', _('Workflow state created'), 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}

    // {{{ do_editState
    function do_editState() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editState');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fStateId']);
        $aTransitionsTo =& KTWorkflowTransition::getByTargetState($oState);
        $aTransitionIdsTo = array();
        foreach ($aTransitionsTo as $oTransition) {
            $aTransitionIdsTo[] = $oTransition->getId();
        }
        $aAllTransitions =& KTWorkflowTransition::getByWorkflow($oWorkflow);
        $aTransitions = array();
        foreach ($aAllTransitions as $oTransition) {
            if (!in_array($oTransition->getId(), $aTransitionIdsTo)) {
                $aTransitions[] = $oTransition;
            }
        }
        $aTransitionsSelected = KTWorkflowUtil::getTransitionsFrom($oState, array('ids' => true));

        $this->oPage->setBreadcrumbDetails(_('State') . ': ' . $oState->getName());
        
        $aInformed = KTWorkflowUtil::getInformedForState($oState);
        
        
        $editForm = array();
        $editForm[] = new KTStringWidget(_('Name'), _('A human-readable name for this state.  This is shown on the "Browse" page, as well as on the user\'s workflow page.'), 'fName', $oState->getName(), $this->oPage, true);
        
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'oState' => $oState,
            'oNotifyRole' => $oRole,
            'aTransitionsTo' => $aTransitionsTo,
            'aTransitions' => $aTransitions,
            'aTransitionsSelected' => $aTransitionsSelected,
            'aActions' => KTDocumentActionUtil::getDocumentActionsByNames(KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow)),
            'aActionsSelected' => KTWorkflowUtil::getEnabledActionsForState($oState),
            'aGroups' => Group::getList(),
            'aRoles' => Role::getList(),
            'aUsers' => User::getList(),
            'aInformed' => $aInformed,
            'editForm' => $editForm,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_saveState
    function do_saveState() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fStateId']);
        $oState->setName($_REQUEST['fName']);
        $oState->setHumanName($_REQUEST['fName']);
        $res = $oState->update();
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' . $oState->getId()),
            'message' => _('Error saving state'),
        ));
        $this->successRedirectTo('editState', _('Changes saved'), 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}

    // {{{ do_saveTransitions
    function do_saveTransitions() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fStateId']);
        $res = KTWorkflowUtil::saveTransitionsFrom($oState, $_REQUEST['fTransitionIds']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' . $oState->getId()),
            'message' => _('Error saving transitions'),
        ));
        $this->successRedirectTo('editState', _('Changes saved'), 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}
    
    // {{{ do_setStateActions
    function do_setStateActions() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fStateId']);
        $res = KTWorkflowUtil::setEnabledActionsForState($oState, $_REQUEST['fActions']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId(), '&fStateId=' .  $oState->getId()),
            'message' => _('Error saving state enabled actions'),
        ));
        $this->successRedirectTo('manageActions', _('Controlled Actions changed.'), 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}

    // {{{ do_saveInform
    function do_saveInform() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fStateId']);
        $sTargetAction = 'editState';
        $sTargetParams = 'fWorkflowId=' . $oWorkflow->getId() .  '&fStateId=' .  $oState->getId();
        $aRoleIds = KTUtil::arrayGet($_REQUEST, 'fRoleIds');
        if (empty($aRoleIds)) {
            $aRoleIds = array();
        }
        if (!is_array($aRoleIds)) {
            $this->errorRedirectTo($sTargetAction, _('Invalid roles specified'), $sTargetParams);
        }
        $aGroupIds = KTUtil::arrayGet($_REQUEST, 'fGroupIds');
        if (empty($aGroupIds)) {
            $aGroupIds = array();
        }
        if (!is_array($aGroupIds)) {
            $this->errorRedirectTo($sTargetAction, _('Invalid groups specified'), $sTargetParams);
        }
        $aUserIds = KTUtil::arrayGet($_REQUEST, 'fUserIds');
        if (empty($aUserIds)) {
            $aUserIds = array();
        }
        if (!is_array($aUserIds)) {
            $this->errorRedirectTo($sTargetAction, _('Invalid users specified'), $sTargetParams);
        }
        $aAllowed = array(
            'role' => $aRoleIds,
            'group' => $aGroupIds,
            'user' => $aUserIds,
        );
        KTWorkflowUtil::setInformedForState($oState, $aAllowed);
        $this->successRedirectTo($sTargetAction, _('Changes saved'), $sTargetParams);
    }
    // }}}

    // }}}

    // {{{ TRANSITION HANDLING
    //
    // {{{ do_newTransition
    function do_newTransition() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fTargetStateId']);
        
        $aInfo = $this->buildWorkflowInfo($oWorkflow);
        
        // setup error options for later
        $aErrorOptions = array(
            'redirect_to' => array('editWorkflow', sprintf('fWorkflowId=%d', $oWorkflow->getId())),
        );

        $iPermissionId = KTUtil::arrayGet($_REQUEST, 'fPermissionId');
        $iGroupId = KTUtil::arrayGet($_REQUEST, 'fGroupId');
        $iRoleId = KTUtil::arrayGet($_REQUEST, 'fRoleId');
        $iConditionId = KTUtil::arrayGet($_REQUEST, 'fConditionId', null);
        
        // validate name
        $sName = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'fName'), $aErrorOptions);
        

        // check there are no other transitions by that name in this workflow
        $aTransitions = KTWorkflowTransition::getList(sprintf("workflow_id = %d and name = '%s'", $oWorkflow->getId(), $sName));
        if(count($aTransitions)) {
            $this->errorRedirectTo(implode('&', $aErrorOptions['redirect_to']), _("A transition by that name already exists"));
        }


        // validate permissions, roles, and group
        if ($iPermissionId) {
            $this->oValidator->validatePermission($_REQUEST['fPermissionId']);
        }
        if ($iGroupId) {
            $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        }
        if ($iRoleId) {
            $this->oValidator->validateRole($_REQUEST['fRoleId']);
        }
        if ($iConditionId) {
            $this->oValidator->validateCondition($_REQUEST['fConditionId']);
        }
        
        $res = KTWorkflowTransition::createFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
            'targetstateid' => $oState->getId(),
            'guardpermissionid' => $iPermissionId,
            'guardgroupid' => $iGroupId,
            'guardroleid' => $iRoleId,
        ));
        $this->oValidator->notError($res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' .  $oWorkflow->getId()),
            'message' => _('Could not create workflow transition'),
        ));
        
        // now attach it to the appropriate states.
        $aStateId = (array) KTUtil::arrayGet($_REQUEST, 'fStatesAvailableIn');
        $aStateId = array_keys($aStateId);
        $newTransition = $res;
        
        foreach ($aStateId as $iStateId) {
            if ($iStateId == $res->getTargetStateId()) { continue; }
            $oState = $aInfo['states'][$iStateId];
            
            $aTransitions = KTWorkflowTransition::getBySourceState($oState);
            $aTransitions[] = $res;
            $aTransitionIds = array();
            foreach ($aTransitions as $oTransition) {
                $aTransitionIds[] = $oTransition->getId();
            }
            $res = KTWorkflowUtil::saveTransitionsFrom($oState, $aTransitionIds);
            if (PEAR::isError($res)) {
                $this->errorRedirectTo('manageTransitions',sprintf(_('Unable to assign new transition to state %s'),$oState->getName()), sprintf('fWorkflowId=%d', $oWorkflow->getId()));
            }
        }
        
        
        $this->successRedirectTo('editTransition', _('Workflow transition created'), sprintf('fWorkflowId=%d&fTransitionId=%d', $oWorkflow->getId(), $newTransition->getId()));
        exit(0);
    }
    // }}}

    // {{{ do_editTransition
    function do_editTransition() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editTransition');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oTransition =& $this->oValidator->validateWorkflowTransition($_REQUEST['fTransitionId']);
        
        $aStates = KTWorkflowState::getByWorkflow($oWorkflow);
        $aPermissions = KTPermission::getList();
        $aGroups = Group::getList();
        $aRoles = Role::getList();
        $aConditions = KTSavedSearch::getConditions();
        
        $edit_fields = array();
        $edit_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the state.'), 'fName', $oTransition->getName(), $this->oPage, true);
        $aOptions = array();
        $vocab = array();
        foreach($aStates as $state) {
            $vocab[$state->getId()] = $state->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $edit_fields[] = new KTLookupWidget(_('Destination State'), _('Once this transition is complete, which state should the document be in?'), 'fTargetStateId', $oTransition->getTargetStateId(), $this->oPage, true, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aPermissions as $permission) {
            $vocab[$permission->getId()] = $permission->getHumanName();
        } 
        $aOptions['vocab'] = $vocab;
        $edit_fields[] = new KTLookupWidget(_('Guard Permission.'), _('Which permission must the user have in order to follow this transition?'), 'fPermissionId', $oTransition->getGuardPermissionId(), $this->oPage, true, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aGroups as $group) {
            $vocab[$group->getId()] = $group->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $edit_fields[] = new KTLookupWidget(_('Guard Group.'), _('Which group must the user belong to in order to follow this transition?'), 'fGroupId', $oTransition->getGuardGroupId(), $this->oPage, false, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aRoles as $role) {
            $vocab[$role->getId()] = $role->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $edit_fields[] = new KTLookupWidget(_('Guard Role.'), _('Which role must the user have in order to follow this transition?'), 'fRoleId', $oTransition->getGuardRoleId(), $this->oPage, false, null, null, $aOptions);
        
        if (!empty($aConditions)) {
            $aOptions = array();
            $vocab = array();
            $vocab[0] = 'None';
            foreach($aConditions as $condition) {
                $vocab[$condition->getId()] = $condition->getName();
            } 
            $aOptions['vocab'] = $vocab;
            $edit_fields[] = new KTLookupWidget(_('Guard Condition.'), _('Which condition (stored search) must be satisfied before the transition can take place?'), 'fConditionId', $oTransition->getGuardConditionId(), $this->oPage, false, null, null, $aOptions);
        }
        
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=editTransition&fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' . $oTransition->getId(),
            'name' => $oTransition->getName(),
        );
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'oTransition' => $oTransition,
            'aStates' => $aStates,
            'aPermissions' => $aPermissions,
            'aGroups' => $aGroups,
            'aRoles' => $aRoles,
            'aConditions' => $aConditions,
            
            // fields 
            
            'edit_fields' => $edit_fields,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_saveTransition
    function do_saveTransition() {
        $aRequest = $this->oValidator->validateDict($_REQUEST, array(
            'fWorkflowId' => array('type' => 'workflow'),
            'fTransitionId' => array('type' => 'workflowtransition'),
        ));
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oTransition =& $this->oValidator->validateWorkflowTransition($_REQUEST['fTransitionId']);
        $oState =& $this->oValidator->validateWorkflowState($_REQUEST['fTargetStateId']);
        $iPermissionId = KTUtil::arrayGet($_REQUEST, 'fPermissionId', null);
        $iGroupId = KTUtil::arrayGet($_REQUEST, 'fGroupId', null);
        $iRoleId = KTUtil::arrayGet($_REQUEST, 'fRoleId', null);
        $iConditionId = KTUtil::arrayGet($_REQUEST, 'fConditionId', null);
        if ($iPermissionId) {
            $this->oValidator->validatePermission($_REQUEST['fPermissionId']);
        }
        if ($iGroupId) {
            $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        }
        if ($iRoleId) {
            $this->oValidator->validateRole($_REQUEST['fRoleId']);
        }
        if ($iConditionId) {
            $this->oValidator->validateCondition($_REQUEST['fConditionId']);
        }
        $oTransition->updateFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
            'targetstateid' => $oState->getId(),
            'guardpermissionid' => $iPermissionId,
            'guardgroupid' => $iGroupId,
            'guardroleid' => $iRoleId,
            'guardconditionid' => $iConditionId,
        ));
        $res = $oTransition->update();
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editTransition', 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' . $oTransition->getId()),
            'message' => _('Error saving transition'),
        ));
        $this->successRedirectTo('editTransition', _('Changes saved'), 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
        exit(0);
    }
    // }}}

    // }}}

}

?>

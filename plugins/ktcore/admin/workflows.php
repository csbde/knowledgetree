<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

class KTWorkflowDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    // {{{ WORKFLOW HANDLING
    // {{{ do_main
    function do_main () {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _('Workflows'),
        );
        
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
        
        $aStates = KTWorkflowState::getByWorkflow($oWorkflow);
        $aPermissions = KTPermission::getList();
        
        $edit_fields = array();
        $edit_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the workflow.'), 'fName', $oWorkflow->getName(), $this->oPage, true);
        $aOptions = array();
        $vocab = array();
        $vocab[0] = 'None';
        foreach($aStates as $state) {
            $vocab[$state->getId()] = $state->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $edit_fields[] = new KTLookupWidget(_('Starting State'), _('When a document has this workflow applied to it, to which state should it initially be set'), 'fStartStateId', $oWorkflow->getStartStateId(), $this->oPage, false, null, null, $aOptions);
        
        $add_state_fields = array();
        $add_state_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the state.'), 'fName', null, $this->oPage, true);

        
        $add_transition_fields = array();
        $add_transition_fields[] = new KTStringWidget(_('Name'), _('A human-readable name for the state.'), 'fName', null, $this->oPage, true);
        $aOptions = array();
        $vocab = array();
        foreach($aStates as $state) {
            $vocab[$state->getId()] = $state->getName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Destination State'), _('Once this transition is complete, which state should the document be in?'), 'fTargetStateId', $oWorkflow->getStartStateId(), $this->oPage, true, null, null, $aOptions);
        $aOptions = array();
        $vocab = array();
        foreach($aPermissions as $permission) {
            $vocab[$permission->getId()] = $permission->getHumanName();
        } 
        $aOptions['vocab'] = $vocab;
        $add_transition_fields[] = new KTLookupWidget(_('Guard Permission.'), _('Which permission must the user have in order to follow this transition?'), 'fPermissionId', $oWorkflow->getStartStateId(), $this->oPage, true, null, null, $aOptions);
        
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _('Workflows'),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => $oWorkflow->getName(),
        );
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'aStates' => $aStates,
            'aTransitions' => KTWorkflowTransition::getByWorkflow($oWorkflow),
            'aPermissions' => $aPermissions,
            'aActions' => KTDocumentActionUtil::getAllDocumentActions(),
            'aActionsSelected' => KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow),
            
            // fields
            'edit_fields' => $edit_fields,
            'add_state_fields' => $add_state_fields,
            'add_transition_fields' => $add_transition_fields,
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
        $oWorkflow->setName($_REQUEST['fName']);
        $oWorkflow->setHumanName($_REQUEST['fName']);
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
            'message' => 'No name given',
        );
        $sName = KTUtil::arrayGet($_REQUEST, 'fName');
        $sName = $this->oValidator->validateString($sName,
                $aErrorOptions);
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

    // {{{ do_setWorkflowActions
    function do_setWorkflowActions() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::setControlledActionsForWorkflow($oWorkflow, $_REQUEST['fActions']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' . $oWorkflow->getId()),
            'message' => _('Error saving workflow controlled actions'),
        ));
        $this->successRedirectTo('editWorkflow', _('Changes saved'), 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // }}}

    // {{{ STATE HANDLING
    //
    // {{{ do_newState
    function do_newState() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oState = KTWorkflowState::createFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
        ));
        $this->oValidator->notError($oState, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' .  $oWorkflow->getId()),
            'message' => _('Could not create workflow state'),
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
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _('Workflows'),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => $oWorkflow->getName(),
        );
        $this->oPage->setBreadcrumbDetails(_('state') . ': ' . $oState->getName());
        
        $aInformed = KTWorkflowUtil::getInformedForState($oState);
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
        $this->successRedirectTo('editState', _('Actions set'), 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
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
        $iPermissionId = KTUtil::arrayGet($_REQUEST, 'fPermissionId');
        $iGroupId = KTUtil::arrayGet($_REQUEST, 'fGroupId');
        $iRoleId = KTUtil::arrayGet($_REQUEST, 'fRoleId');
        if ($iPermissionId) {
            $this->oValidator->validatePermission($_REQUEST['fPermissionId']);
        }
        if ($iGroupId) {
            $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        }
        if ($iRoleId) {
            $this->oValidator->validateRole($_REQUEST['fRoleId']);
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
        $this->successRedirectTo('editWorkflow', _('Workflow transition created'), 'fWorkflowId=' . $oWorkflow->getId());
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
            'name' => _('Workflows'),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => $oWorkflow->getName(),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
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

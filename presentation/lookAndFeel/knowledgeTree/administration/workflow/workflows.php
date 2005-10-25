<?php

require_once('../../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');

$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');

class KTWorkflowDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
        array('action' => 'manageWorkflows', 'name' => 'Workflow Management'),
    );

    // {{{ WORKFLOW HANDLING
    // {{{ do_main
    function do_main () {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/workflow/listWorkflows');
        $oTemplate->setData(array(
            'aWorkflow' => KTWorkflow::getList(),
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_editWorkflow
    function do_editWorkflow() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editWorkflow');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => 'Workflow ' . $oWorkflow->getName(),
        );
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'aStates' => KTWorkflowState::getByWorkflow($oWorkflow),
            'aTransitions' => KTWorkflowTransition::getByWorkflow($oWorkflow),
            'aPermissions' => KTPermission::getList(),
            'aActions' => KTDocumentActionUtil::getAllDocumentActions(),
            'aActionsSelected' => KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow),
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
            'message' => 'Error saving workflow',
        ));
        $this->successRedirectTo('editWorkflow', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // {{{ do_newWorkflow
    function do_newWorkflow() {
        $res = KTWorkflow::createFromArray(array(
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
        ));
        $this->oValidator->notError($res, array(
            'redirect_to' => array('main'),
            'message' => 'Could not create workflow',
        ));
        $this->successRedirectTo('editWorkflow', 'Workflow created', 'fWorkflowId=' . $res->getId());
        exit(0);
    }
    // }}}

    // {{{ do_setWorkflowActions
    function do_setWorkflowActions() {
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::setControlledActionsForWorkflow($oWorkflow, $_REQUEST['fActions']);
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' . $oWorkflow->getId()),
            'message' => 'Error saving workflow controlled actions',
        ));
        $this->successRedirectTo('editWorkflow', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId());
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
            'message' => 'Could not create workflow state',
        ));
        $this->successRedirectTo('editState', 'Workflow state created', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
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
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => 'Workflow ' . $oWorkflow->getName(),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editState&fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' . $oState->getId(),
            'name' => 'State ' . $oState->getName(),
        );
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
            'message' => 'Error saving state',
        ));
        $this->successRedirectTo('editState', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
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
            'message' => 'Error saving transitions',
        ));
        $this->successRedirectTo('editState', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
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
            'message' => 'Error saving state enabled actions',
        ));
        $this->successRedirectTo('editState', 'Actions set', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
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
            $this->errorRedirectTo($sTargetAction, 'Invalid roles specified', $sTargetParams);
        }
        $aGroupIds = KTUtil::arrayGet($_REQUEST, 'fGroupIds');
        if (empty($aGroupIds)) {
            $aGroupIds = array();
        }
        if (!is_array($aGroupIds)) {
            $this->errorRedirectTo($sTargetAction, 'Invalid groups specified', $sTargetParams);
        }
        $aUserIds = KTUtil::arrayGet($_REQUEST, 'fUserIds');
        if (empty($aUserIds)) {
            $aUserIds = array();
        }
        if (!is_array($aUserIds)) {
            $this->errorRedirectTo($sTargetAction, 'Invalid users specified', $sTargetParams);
        }
        $aAllowed = array(
            'role' => $aRoleIds,
            'group' => $aGroupIds,
            'user' => $aUserIds,
        );
        KTWorkflowUtil::setInformedForState($oState, $aAllowed);
        $this->successRedirectTo($sTargetAction, 'Changes made', $sTargetParams);
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
            'message' => 'Could not create workflow transition',
        ));
        $this->successRedirectTo('editWorkflow', 'Workflow transition created', 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // {{{ do_editTransition
    function do_editTransition() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/editTransition');
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $oTransition =& $this->oValidator->validateWorkflowTransition($_REQUEST['fTransitionId']);
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editWorkflow&fWorkflowId=' . $oWorkflow->getId(),
            'name' => 'Workflow ' . $oWorkflow->getName(),
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'manageWorkflows',
            'query' => 'action=editTransitionfWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' . $oTransition->getId(),
            'name' => 'Transition ' . $oTransition->getName(),
        );
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'oTransition' => $oTransition,
            'aStates' => KTWorkflowState::getByWorkflow($oWorkflow),
            'aPermissions' => KTPermission::getList(),
            'aGroups' => Group::getList(),
            'aRoles' => Role::getList(),
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
        if ($iPermissionId) {
            $this->oValidator->validatePermission($_REQUEST['fPermissionId']);
        }
        if ($iGroupId) {
            $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        }
        if ($iRoleId) {
            $this->oValidator->validateRole($_REQUEST['fRoleId']);
        }
        $oTransition->updateFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
            'targetstateid' => $oState->getId(),
            'guardpermissionid' => $iPermissionId,
            'guardgroupid' => $iGroupId,
            'guardroleid' => $iRoleId,
        ));
        $res = $oTransition->update();
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('editTransition', 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' . $oTransition->getId()),
            'message' => 'Error saving transition',
        ));
        $this->successRedirectTo('editTransition', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId() . '&fTransitionId=' .  $oTransition->getId());
        exit(0);
    }
    // }}}

    // }}}

}

$d =& new KTWorkflowDispatcher;
$d->dispatch();

?>

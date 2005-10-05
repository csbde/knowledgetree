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

class KTWorkflowDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

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
        // $oTemplating =& KTTemplating::getSingleton();
        // $oTemplate =& $oTemplating->loadTemplate('ktcore/workflow/editWorkflow');
        $oTemplate =& KTDispatcherValidation::validateTemplate($this, 'ktcore/workflow/editWorkflow');
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
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
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oWorkflow->setName($_REQUEST['fName']);
        $oWorkflow->setHumanName($_REQUEST['fName']);
        if (!empty($_REQUEST['fStartStateId'])) {
            $oWorkflow->setStartStateId($_REQUEST['fStartStateId']);
        } else {
            $oWorkflow->setStartStateId(null);
        }
        $res = $oWorkflow->update();
        KTDispatcherValidation::notErrorFalse($this, $res, array(
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
        KTDispatcherValidation::notError($this, $res, array(
            'redirect_to' => array('main'),
            'message' => 'Could not create workflow',
        ));
        $this->successRedirectTo('editWorkflow', 'Workflow created', 'fWorkflowId=' . $res->getId());
        exit(0);
    }
    // }}}

    // {{{ do_setWorkflowActions
    function do_setWorkflowActions() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::setControlledActionsForWorkflow($oWorkflow, $_REQUEST['fActions']);
        KTDispatcherValidation::notErrorFalse($this, $res, array(
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
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState = KTWorkflowState::createFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
        ));
        KTDispatcherValidation::notError($this, $oState, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' .  $oWorkflow->getId()),
            'message' => 'Could not create workflow state',
        ));
        $this->successRedirectTo('editState', 'Workflow state created', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}

    // {{{ do_editState
    function do_editState() {
        $oTemplate =& KTDispatcherValidation::validateTemplate($this, 'ktcore/workflow/editState');
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fStateId']);
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
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'oState' => $oState,
            'aTransitionsTo' => $aTransitionsTo,
            'aTransitions' => $aTransitions,
            'aTransitionsSelected' => $aTransitionsSelected,
            'aActions' => KTDocumentActionUtil::getDocumentActionsByNames(KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow)),
            'aActionsSelected' => KTWorkflowUtil::getEnabledActionsForState($oState),
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_saveState
    function do_saveState() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fStateId']);
        $oState->setName($_REQUEST['fName']);
        $oState->setHumanName($_REQUEST['fName']);
        $res = $oState->update();
        KTDispatcherValidation::notErrorFalse($this, $res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' . $oState->getId()),
            'message' => 'Error saving state',
        ));
        $this->successRedirectTo('editState', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}

    // {{{ do_saveTransitions
    function do_saveTransitions() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fStateId']);
        $res = KTWorkflowUtil::saveTransitionsFrom($oState, $_REQUEST['fTransitionIds']);
        KTDispatcherValidation::notErrorFalse($this, $res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' . $oState->getId()),
            'message' => 'Error saving transitions',
        ));
        $this->successRedirectTo('editState', 'Changes saved', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}
    
    // {{{ do_setStateActions
    function do_setStateActions() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fStateId']);
        $res = KTWorkflowUtil::setEnabledActionsForState($oState, $_REQUEST['fActions']);
        KTDispatcherValidation::notErrorFalse($this, $res, array(
            'redirect_to' => array('editState', 'fWorkflowId=' . $oWorkflow->getId(), '&fStateId=' .  $oState->getId()),
            'message' => 'Error saving state enabled actions',
        ));
        $this->successRedirectTo('editState', 'Actions set', 'fWorkflowId=' . $oWorkflow->getId() . '&fStateId=' .  $oState->getId());
        exit(0);
    }
    // }}}
    // }}}

    // {{{ TRANSITION HANDLING
    //
    // {{{ do_newTransition
    function do_newTransition() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fTargetStateId']);
        $oPermission =& KTDispatcherValidation::validatePermission($this, $_REQUEST['fPermissionId']);
        $res = KTWorkflowTransition::createFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
            'targetstateid' => $oState->getId(),
            'guardpermissionid' => $oPermission->getId(),
        ));
        KTDispatcherValidation::notError($this, $res, array(
            'redirect_to' => array('editWorkflow', 'fWorkflowId=' .  $oWorkflow->getId()),
            'message' => 'Could not create workflow transition',
        ));
        $this->successRedirectTo('editWorkflow', 'Workflow transition created', 'fWorkflowId=' . $oWorkflow->getId());
        exit(0);
    }
    // }}}

    // {{{ do_editTransition
    function do_editTransition() {
        $oTemplate =& KTDispatcherValidation::validateTemplate($this, 'ktcore/workflow/editTransition');
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oTransition =& KTDispatcherValidation::validateWorkflowTransition($this, $_REQUEST['fTransitionId']);
        $oTemplate->setData(array(
            'oWorkflow' => $oWorkflow,
            'oTransition' => $oTransition,
            'aStates' => KTWorkflowState::getByWorkflow($oWorkflow),
            'aPermissions' => KTPermission::getList(),
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_saveTransition
    function do_saveTransition() {
        $oWorkflow =& KTDispatcherValidation::validateWorkflow($this, $_REQUEST['fWorkflowId']);
        $oTransition =& KTDispatcherValidation::validateWorkflowTransition($this, $_REQUEST['fTransitionId']);
        $oState =& KTDispatcherValidation::validateWorkflowState($this, $_REQUEST['fTargetStateId']);
        $oPermission =& KTDispatcherValidation::validatePermission($this, $_REQUEST['fPermissionId']);
        $oTransition->updateFromArray(array(
            'workflowid' => $oWorkflow->getId(),
            'name' => $_REQUEST['fName'],
            'humanname' => $_REQUEST['fName'],
            'targetstateid' => $oState->getId(),
            'guardpermissionid' => $oPermission->getId(),
        ));
        $res = $oTransition->update();
        KTDispatcherValidation::notErrorFalse($this, $res, array(
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

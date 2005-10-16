<?php

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');

class KTWorkflowUtil {
    function saveTransitionsFrom($oState, $aTransitionIds) {
        $sTable = KTUtil::getTableName('workflow_state_transitions');
        $aQuery = array(
            "DELETE FROM $sTable WHERE state_id = ?",
            array($oState->getId()),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aOptions = array('noid' => true);
        foreach ($aTransitionIds as $iTransitionId) {
            $res = DBUtil::autoInsert($sTable, array(
                'state_id' => $oState->getId(),
                'transition_id' => $iTransitionId,
            ), $aOptions);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return;
    }

    function getTransitionsFrom($oState, $aOptions = null) {
        $bIds = KTUtil::arrayGet($aOptions, 'ids');
        $sTable = KTUtil::getTableName('workflow_state_transitions');
        $aQuery = array(
            "SELECT transition_id FROM $sTable WHERE state_id = ?",
            array($oState->getId()),
        );
        $aTransitionIds = DBUtil::getResultArrayKey($aQuery, 'transition_id');
        if (PEAR::isError($aTransitionIds)) {
            return $aTransitionIds;
        }
        if ($bIds) {
            return $aTransitionIds;
        }
        $aRet = array();
        foreach ($aTransitionIds as $iId) {
            $aRet[] =& KTWorkflowTransition::get($iId);
        }
        return $aRet;
    }

    function startWorkflowOnDocument ($oWorkflow, $oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $oWorkflow =& KTWorkflow::get($iWorkflowId);
        $iStartStateId = $oWorkflow->getStartStateId();
        if (empty($iStartStateId)) {
            return PEAR::raiseError('Cannot assign workflow with no starting state set');
        }
        $aOptions = array('noid' => true);
        $aValues = array(
            'document_id' => $iDocumentId,
            'workflow_id' => $iWorkflowId,
            'state_id' => $iStartStateId,
        );
        $sTable = KTUtil::getTableName('workflow_documents');
        return DBUtil::autoInsert($sTable, $aValues, $aOptions);
    }

    function getControlledActionsForWorkflow($oWorkflow) {
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $sTable = KTUtil::getTableName('workflow_actions');

        $aQuery = array(
            "SELECT action_name FROM $sTable WHERE workflow_id = ?",
            array($iWorkflowId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'action_name');
    }

    function setControlledActionsForWorkflow($oWorkflow, $aActions) {
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $sTable = KTUtil::getTableName('workflow_actions');

        $aQuery = array(
            "DELETE FROM $sTable WHERE workflow_id = ?",
            array($iWorkflowId),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aOptions = array('noid' => true);
        foreach ($aActions as $sAction) {
            $res = DBUtil::autoInsert($sTable, array(
                'workflow_id' => $iWorkflowId,
                'action_name' => $sAction,
            ), $aOptions);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return;
    }

    function setEnabledActionsForState($oState, $aActions) {
        $iStateId = KTUtil::getId($oState);
        $sTable = KTUtil::getTableName('workflow_state_actions');

        $aQuery = array(
            "DELETE FROM $sTable WHERE state_id = ?",
            array($iStateId),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aOptions = array('noid' => true);
        foreach ($aActions as $sAction) {
            $res = DBUtil::autoInsert($sTable, array(
                'state_id' => $iStateId,
                'action_name' => $sAction,
            ), $aOptions);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return;
    }

    function getEnabledActionsForState($oState) {
        $iStateId = KTUtil::getId($oState);
        $sTable = KTUtil::getTableName('workflow_state_actions');

        $aQuery = array(
            "SELECT action_name FROM $sTable WHERE state_id = ?",
            array($iStateId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'action_name');
    }

    function actionEnabledForDocument($oDocument, $sName) {
        $oWorkflow =& KTWorkflow::getByDocument($oDocument);
        if (is_null($oWorkflow)) {
            return true;
        }
        if (!in_array($sName, KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow))) {
            return true;
        }
        $oState =& KTWorkflowState::getByDocument($oDocument);
        if (!in_array($sName, KTWorkflowUtil::getEnabledActionsForState($oState))) {
            return false;
        }
        return true;
    }

    function getWorkflowForDocument ($oDocument, $aOptions = null) {
        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
        $iDocumentId = KTUtil::getId($oDocument);
        $sTable = KTUtil::getTableName('workflow_documents');
        $aQuery = array(
            "SELECT workflow_id FROM $sTable WHERE document_id = ?",
            array($iDocumentId),
        );
        $iWorkflowId = DBUtil::getOneResultKey($aQuery, 'workflow_id');
        if (is_null($iWorkflowId)) {
            return $iWorkflowId;
        }
        if (PEAR::isError($iWorkflowId)) {
            return $iWorkflowId;
        }
        if ($ids) {
            return $iWorkflowId;
        }
        return KTWorkflow::get($iWorkflowId);
    }

    function getWorkflowStateForDocument ($oDocument, $aOptions = null) {
        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
        $iDocumentId = KTUtil::getId($oDocument);
        $sTable = KTUtil::getTableName('workflow_documents');
        $aQuery = array(
            "SELECT state_id FROM $sTable WHERE document_id = ?",
            array($iDocumentId),
        );
        $iWorkflowStateId = DBUtil::getOneResultKey($aQuery, 'state_id');
        if (is_null($iWorkflowStateId)) {
            return $iWorkflowStateId;
        }
        if (PEAR::isError($iWorkflowStateId)) {
            return $iWorkflowStateId;
        }
        if ($ids) {
            return $iWorkflowStateId;
        }
        return KTWorkflowState::get($iWorkflowStateId);
    }

    function getTransitionsForDocumentUser($oDocument, $oUser) {
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($oDocument);
        if (is_null($oState) || PEAR::isError($oState)) {
            return $oState;
        }
        return KTWorkflowUtil::getTransitionsFrom($oState);
    }
}


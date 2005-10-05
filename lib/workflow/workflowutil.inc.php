<?php

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

    function getTransitionsFrom($oState, $aOptions) {
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
}


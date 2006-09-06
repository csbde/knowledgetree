<?php

// a very simple utility class to help keep the admin code clean, tidy
// and re-usable, without having to do the same thing in 5 or 6 different 
// places.
//
// this also helps ease code-creep in workflowutil

class KTWorkflowAdminUtil {
    // transition origins.
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
        if (empty($aTransitionIds)) {
            return; // don't fail if there are no transitions.
        }
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
    
    function saveTransitionSources($oTransition, $aStateIds) {
        $sTable = KTUtil::getTableName('workflow_state_transitions');
        $aQuery = array(
            "DELETE FROM $sTable WHERE transition_id = ?",
            array($oTransition->getId()),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aOptions = array('noid' => true);
        if (empty($aStateIds)) {
            return; // don't fail if there are no transitions.
        }
        foreach ($aStateIds as $iStateId) {
            $res = DBUtil::autoInsert($sTable, array(
                'state_id' => $iStateId,
                'transition_id' => $oTransition->getId(),
            ), $aOptions);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return;
    }    
    
// {{{ getTransitionsFrom
    /**
     * Gets which workflow transitions are available to be chosen from
     * this workflow state.
     *
     * Workflow transitions have only destination workflow states, and
     * it is up to the workflow state to decide which workflow
     * transitions it wants to allow to leave its state.
     *
     * This function optionally will return the database id numbers of
     * the workflow transitions using the 'ids' option.
     */
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
    // }}}
    
    function getSourceStates($oTransition, $aOptions = null) {
        $bIds = KTUtil::arrayGet($aOptions, 'ids');
        $sTable = KTUtil::getTableName('workflow_state_transitions');
        $aQuery = array(
            "SELECT state_id FROM $sTable WHERE transition_id = ?",
            array($oTransition->getId()),
        );
        $aStateIds = DBUtil::getResultArrayKey($aQuery, 'state_id');
        if (PEAR::isError($aStateIds)) {
            return $aStateIds;
        }
        if ($bIds) {
            return $aStateIds;
        }
        $aRet = array();
        foreach ($aStateIds as $iId) {
            $aRet[] =& KTWorkflowState::get($iId);
        }
        return $aRet;
    }
            

}

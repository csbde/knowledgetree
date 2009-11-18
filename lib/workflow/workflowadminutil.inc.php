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

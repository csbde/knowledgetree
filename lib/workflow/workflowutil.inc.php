<?php

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');

require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');
require_once(KT_LIB_DIR . '/roles/roleallocation.inc.php');

require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');

require_once(KT_LIB_DIR . '/dashboard/Notification.inc.php');


class KTWorkflowUtil {
    // {{{ saveTransitionsFrom
    /**
     * Saves which workflow transitions are available to be chosen from
     * this workflow state.
     *
     * Workflow transitions have only destination workflow states, and
     * it is up to the workflow state to decide which workflow
     * transitions it wants to allow to leave its state.
     */
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
    // }}}

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

    // {{{ startWorkflowOnDocument
    /**
     * Starts the workflow process on a document, placing it into the
     * starting workflow state for the given workflow.
     */
    function startWorkflowOnDocument ($oWorkflow, $oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $oWorkflow =& KTWorkflow::get($iWorkflowId);
        // null workflow == remove workflow.
        if (is_null($oWorkflow) || PEAR::isError($oWorkflow) || ($oWorkflow == false)) {
            return true; // delete and no-act.
        }
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
        $res = DBUtil::autoInsert($sTable, $aValues, $aOptions);
        
        // FIXME does this function as expected?
        $oUser = User::get($_SESSION['userID']);
        
        $oTargetState = KTWorkflowState::get($iStartStateId);
        KTWorkflowUtil::informUsersForState($oTargetState, 
            KTWorkflowUtil::getInformedForState($oTargetState), $oDocument, $oUser, '');
        
        return $res;
    }
    // }}}
    

    // {{{ changeWorkflowOnDocument
    /**
     * Starts the workflow process on a document, placing it into the
     * starting workflow state for the given workflow.
     */
    function changeWorkflowOnDocument ($oWorkflow, $oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $oWorkflow =& KTWorkflow::get($iWorkflowId);
        
        if (empty($iStartStateId)) {
            return PEAR::raiseError('Cannot assign workflow with no starting state set');
        }
        $oOldWorkflow = KTWorkflowUtil::getWorkflowForDocument($oDocument);
        if ((!(PEAR::isError($oOldWorkflow) || ($oOldWorkflow == false))) && ($oOldWorkflow->getId() == $oWorkflow->getId())) {
            return true;         // all fine - no change required.
        }
        
        $sQuery = 'DELETE FROM ' . KTUtil::getTableName('workflow_documents');
        $sQuery .= ' WHERE document_id = ?';
        $aParams = array($iDocumentId);
        DBUtil::runQuery(array($sQuery, $aParams));
        
        if (is_null($oWorkflow) || PEAR::isError($oWorkflow) || ($oWorkflow == false)) {
            return true; // delete and no-act.
        }
        
        $iStartStateId = $oWorkflow->getStartStateId();
        $aOptions = array('noid' => true);
        $aValues = array(
            'document_id' => $iDocumentId,
            'workflow_id' => $iWorkflowId,
            'state_id' => $iStartStateId,
        );
        $sTable = KTUtil::getTableName('workflow_documents');
        
        $oUser = User::get($_SESSION['userID']);
        $oTargetState = KTWorkflowState::get($iStartStateId);
        
        KTWorkflowUtil::informUsersForState($oTargetState, 
            KTWorkflowUtil::getInformedForState($oTargetState), $oDocument, $oUser, '');
        
        
        return DBUtil::autoInsert($sTable, $aValues, $aOptions);
    }
    // }}}    

    // {{{ getControlledActionsForWorkflow
    /**
     * Gets the actions that are controlled by a workflow.
     *
     * A controlled action is one that can be enabled or disabled by a
     * workflow state attached to this workflow.  This allows for
     * actions such as "Delete" to not be allowed to occur during the
     * workflow, or for special actions such as "Publish" to only occur
     * when a particular workflow state is reached.
     */
    function getControlledActionsForWorkflow($oWorkflow) {
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $sTable = KTUtil::getTableName('workflow_actions');

        $aQuery = array(
            "SELECT action_name FROM $sTable WHERE workflow_id = ?",
            array($iWorkflowId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'action_name');
    }
    // }}}

    // {{{ setControlledActionsForWorkflow
    /**
     * Sets the actions that are controlled by a workflow.
     *
     * A controlled action is one that can be enabled or disabled by a
     * workflow state attached to this workflow.  This allows for
     * actions such as "Delete" to not be allowed to occur during the
     * workflow, or for special actions such as "Publish" to only occur
     * when a particular workflow state is reached.
     */
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
        if (!empty($aActions)) {
            foreach ($aActions as $sAction) {
                $res = DBUtil::autoInsert($sTable, array(
                    'workflow_id' => $iWorkflowId,
                    'action_name' => $sAction,
                ), $aOptions);
                if (PEAR::isError($res)) {
                   return $res;
                }
            }
        }
        return;
    }
    // }}}

    // {{{ setEnabledActionsForState
    /**
     * Sets the actions that are enabled by this workflow state.
     *
     * A controlled action is one that can be enabled or disabled by a
     * workflow state attached to this workflow.  This allows for
     * actions such as "Delete" to not be allowed to occur during the
     * workflow, or for special actions such as "Publish" to only occur
     * when a particular workflow state is reached.
     *
     * Only the enabled actions are tracked.  Any actions controlled by
     * the workflow but not explicitly enabled are disabled.
     */
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

        if(!is_array($aActions)) return;
        
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
    // }}}

    // {{{ getEnabledActionsForState
    /**
     * Gets the actions that are enabled by this workflow state.
     *
     * A controlled action is one that can be enabled or disabled by a
     * workflow state attached to this workflow.  This allows for
     * actions such as "Delete" to not be allowed to occur during the
     * workflow, or for special actions such as "Publish" to only occur
     * when a particular workflow state is reached.
     *
     * Only the enabled actions are tracked.  Any actions controlled by
     * the workflow but not explicitly enabled are disabled.
     */
    function getEnabledActionsForState($oState) {
        $iStateId = KTUtil::getId($oState);
        $sTable = KTUtil::getTableName('workflow_state_actions');

        $aQuery = array(
            "SELECT action_name FROM $sTable WHERE state_id = ?",
            array($iStateId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'action_name');
    }
    // }}}

    // {{{ actionEnabledForDocument
    /**
     * Checks if a particular action is enabled to occur on a document
     * by virtue of its workflow and workflow state.
     *
     * A controlled action is one that can be enabled or disabled by a
     * workflow state attached to this workflow.  This allows for
     * actions such as "Delete" to not be allowed to occur during the
     * workflow, or for special actions such as "Publish" to only occur
     * when a particular workflow state is reached.
     *
     * Only the enabled actions are tracked.  Any actions controlled by
     * the workflow but not explicitly enabled are disabled.
     */
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
    // }}}

    // {{{ getWorkflowForDocument
    /**
     * Gets the workflow that applies to the given document, returning
     * null if there is no workflow assigned.
     */
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
    // }}}

    // {{{ getWorkflowStateForDocument
    /**
     * Gets the workflow state that applies to the given document,
     * returning null if there is no workflow assigned.
     */
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
    // }}}

    // {{{ getTransitionsForDocumentUser
    /**
     * Gets the transitions that are available for a document by virtue
     * of its workflow state, and also by virtue of the user that wishes
     * to perform the transition.
     *
     * In other words, ensures that the guard permission, role, group,
     * and/or user are met for the given user.
     */
    function getTransitionsForDocumentUser($oDocument, $oUser) {
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($oDocument);
        if (is_null($oState) || PEAR::isError($oState)) {
            return $oState;
        }
        $aTransitions = KTWorkflowUtil::getTransitionsFrom($oState);
        $aEnabledTransitions = array();
        foreach ($aTransitions as $oTransition) {
            $iPermissionId = $oTransition->getGuardPermissionId();
            if ($iPermissionId) {
                $oPermission =& KTPermission::get($iPermissionId);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oDocument)) {
                    continue;
                }
            }
            $iGroupId = $oTransition->getGuardGroupId();
            if ($iGroupId) {
                $oGroup =& Group::get($iGroupId);
                if (!$oGroup->hasMember($oUser)) {
                    continue;
                }
            }
            $iRoleId = $oTransition->getGuardRoleId();
            if ($iRoleId) {
                $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($oDocument->getFolderID(), $iRoleId);
                
                if ($oRoleAllocation == null) {   // no role allocation, no fulfillment.
                    continue;
                }
                
                if (!$oRoleAllocation->hasMember($oUser)) {
                    continue;
                }
            }

            $iConditionId = $oTransition->getGuardConditionId();
            if ($iConditionId) {
                if (!KTSearchUtil::testConditionOnDocument($iConditionId, $oDocument)) {
                    continue;
                }
            }
            $aEnabledTransitions[] = $oTransition;
        }
        return $aEnabledTransitions;
    }
    // }}}

    // {{{ performTransitionOnDocument
    /**
     * Performs a workflow transition on a document, changing it from
     * one workflow state to another, with potential side effects (user
     * scripts, and so forth).
     *
     * This function currently assumes that the user in question is
     * allowed to perform the transition and that all the guard
     * functionality on the transition has passed.
     */
    function performTransitionOnDocument($oTransition, $oDocument, $oUser, $sComments) {
        $oWorkflow =& KTWorkflow::getByDocument($oDocument);
        if (empty($oWorkflow)) {
            return PEAR::raiseError("Document has no workflow");
        }
        if (PEAR::isError($oWorkflow)) {
            return $oWorkflow;
        }
        $oSourceState =& KTWorkflowUtil::getWorkflowStateForDocument($oDocument);

        $sTable = KTUtil::getTableName('workflow_documents');
        $iStateId = $oTransition->getTargetStateId();
        $iDocumentId = $oDocument->getId();
        $aQuery = array(
            "UPDATE $sTable SET state_id = ? WHERE document_id = ?",
            array($iStateId, $iDocumentId),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }

        $oTargetState =& KTWorkflowState::get($iStateId);
        $sSourceState = $oSourceState->getName();
        $sTargetState = $oTargetState->getName();

        // create the document transaction record
        $sTransactionComments = "Workflow state changed from $sSourceState to $sTargetState";
        if ($sComments) {
            $sTransactionComments .= "; Reason given was: " . $sComments;
        }
        $oDocumentTransaction = & new DocumentTransaction($oDocument, $sTransactionComments, 'ktcore.transactions.workflow_state_transition');
        $oDocumentTransaction->create();

        KTWorkflowUtil::informUsersForState($oTargetState, KTWorkflowUtil::getInformedForState($oTargetState), $oDocument, $oUser, $sComments);

        return true;
    }
    // }}}

    // {{{ informUsersForState
    function informUsersForState($oState, $aInformed, $oDocument, $oUser, $sComments) {
        // say no to duplicates.
        
        KTWorkflowNotification::clearNotificationsForDocument($oDocument);

        $aUsers = array();
        $aGroups = array();
        $aRoles = array();
        
        foreach (KTUtil::arrayGet($aInformed,'user',array()) as $iUserId) {
            $oU = User::get($iUserId);
            if (PEAR::isError($oU) || ($oU == false)) {
                continue;
            } else {
                $aUsers[$oU->getId()] = $oU;
            }
        }
        
        foreach (KTUtil::arrayGet($aInformed,'group',array()) as $iGroupId) {
            $oG = Group::get($iGroupId);
            if (PEAR::isError($oG) || ($oG == false)) {
                continue;
            } else {
                $aGroups[$oG->getId()] = $oG;
            }
        }
        
        foreach (KTUtil::arrayGet($aInformed,'role',array()) as $iRoleId) {
            $oR = Role::get($iRoleId);
            if (PEAR::isError($oR) || ($oR == false)) {
                continue;
            } else {
                $aRoles[] = $oR;
            }
        }
        
        
        
        // FIXME extract this into a util - I see us using this again and again.
        // start with roles ... roles _only_ ever contain groups.
        foreach ($aRoles as $oRole) {
            $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($oDocument->getFolderID(), $oRole->getId());
            $aRoleUsers = $oRoleAllocation->getUsers();
            $aRoleGroups = $oRoleAllocation->getGroups();
            
            foreach ($aRoleUsers as $id => $oU) {
                $aUsers[$id] = $oU;
            }
            foreach ($aRoleGroups as $id => $oGroup) {
                $aGroups[$id] = $oGroup;
            }
        }
        
        
        
        // we now have a (potentially overlapping) set of groups, which may
        // have subgroups.
        //
        // what we need to do _now_ is build a canonical set of groups, and then
        // generate the singular user-base.
        
        $aGroupMembershipSet = GroupUtil::buildGroupArray();
        $aAllIds = array_keys($aGroups);
        foreach ($aGroups as $id => $oGroup) {
            $aAllIds = array_merge($aGroupMembershipSet[$id], $aAllIds);
        }
        
        foreach ($aAllIds as $id) {
            if (!array_key_exists($id, $aGroups)) {
                $aGroups[$id] = Group::get($id);
            }
        }
        
        // now, merge this (again) into the user-set.
        foreach ($aGroups as $oGroup) {
            $aNewUsers = $oGroup->getUsers();
            foreach ($aNewUsers as $id => $oU) {
                if (!array_key_exists($id, $aUsers)) {
                    $aUsers[$id] = $oU;
                }
            }
        }
        
        // and done.  
        foreach ($aUsers as $oU) {
            KTWorkflowNotification::newNotificationForDocument($oDocument, $oU, $oState, $oUser, $sComments);
        }
    }
    // }}}

    // {{{ setInformedForState
    /**
     * Sets which users/groups/roles are to be informed when a state is
     * arrived at.
     */
    function setInformedForState(&$oState, $aInformed) {
        $oDescriptor =& KTPermissionUtil::getOrCreateDescriptor($aInformed);
        if (PEAR::isError($oDescriptor)) {
            return $oDescriptor;
        }
        $iOldDescriptorId = $oState->getInformDescriptorId();
        $oState->setInformDescriptorId($oDescriptor->getId());
        $res = $oState->update();
        if (PEAR::isError($res)) {
            $oState->setInformDescriptorId($iOldDescriptorId);
            return $res;
        }
        return $res;
    }
    // }}}

    // {{{ getInformedForState
    /**
     * Gets which users/groups/roles are to be informed when a state is
     * arrived at.
     */
    function getInformedForState($oState) {
        $iDescriptorId = $oState->getInformDescriptorId();
        if (empty($iDescriptorId)) {
            return array();
        }
        return KTPermissionUtil::getAllowedForDescriptor($iDescriptorId);
    }
    // }}}
}


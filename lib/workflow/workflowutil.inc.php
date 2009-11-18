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
 *
 */

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtrigger.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtriggerinstance.inc.php');

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


    /* WILL NOT RESET THE WORKFLOW if changing to the -same- workflow */
    function changeWorkflowOnDocument($oWorkflow, $oDocument) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);


        // fix for 1049: workflows reset on document move.
        // this was the original purpose of "changeWorkflowOnDocument".
        if (is_null($oWorkflow)) {
            if ($oDocument->getWorkflowId() == null) {
                return true; // no definition.
            }
        } else {
            if ($oDocument->getWorkflowId() == $oWorkflow->getId()) {
                return true; // bail out, essentially.
            }
        }

        return KTWorkflowUtil::startWorkflowOnDocument($oWorkflow, $oDocument);
    }
    // {{{ startWorkflowOnDocument
    /**
     * Starts the workflow process on a document, placing it into the
     * starting workflow state for the given workflow.
     */
    function startWorkflowOnDocument ($oWorkflow, $oDocument) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        $iDocumentId = $oDocument->getId();

        $oUser = User::get($_SESSION['userID']);

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();
        $oDocument->startNewMetadataVersion($oUser);
        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);

        if (!empty($oWorkflow)) {
            $oWorkflow =& KTUtil::getObject('KTWorkflow', $oWorkflow);
            $iWorkflowId = $oWorkflow->getId();
            // null workflow == remove workflow.
            if (is_null($oWorkflow) || PEAR::isError($oWorkflow) || ($oWorkflow == false)) {
                return true; // delete and no-act.
            }
            $iStartStateId = $oWorkflow->getStartStateId();
            if (empty($iStartStateId)) {
                return PEAR::raiseError(_kt('Cannot assign workflow with no starting state set'));
            }

            $oDocument->setWorkflowId($iWorkflowId);
            $oDocument->setWorkflowStateId($iStartStateId);
            $sTransactionComments = sprintf(_kt("Workflow \"%s\" started."), $oWorkflow->getHumanName());

        } else {
            $oDocument->setWorkflowId(null);
            $oDocument->setWorkflowStateId(null);
            $sTransactionComments = _kt('Workflow removed from document.');
        }

        $res = $oDocument->update();
        if (PEAR::isError($res)) { return $res; }

        // create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($oDocument, $sTransactionComments, 'ktcore.transactions.workflow_state_transition');
        $oDocumentTransaction->create();


        // FIXME does this function as expected?

        KTPermissionUtil::updatePermissionLookup($oDocument);

        if (isset($iStartStateId)) {
            $oTargetState = KTWorkflowState::get($iStartStateId);
            KTWorkflowUtil::informUsersForState($oTargetState,
                KTWorkflowUtil::getInformedForState($oTargetState), $oDocument, $oUser, '');
        }

        return $res;
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

    // FIXME DEPRECATED

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

    function setDisabledActionsForState($oState, $aActions) {
        $iStateId = KTUtil::getId($oState);
        $sTable = KTUtil::getTableName('workflow_state_disabled_actions');

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

    function getDisabledActionsForState($oState) {
        $iStateId = KTUtil::getId($oState);
        $sTable = KTUtil::getTableName('workflow_state_disabled_actions');

        $aQuery = array(
            "SELECT action_name FROM $sTable WHERE state_id = ?",
            array($iStateId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'action_name');
    }

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
        // FIXME: The workflow_actions table that the method below uses is always empty!
        //        It seems the new method was never followed though to completion.
        //if (!in_array($sName, KTWorkflowUtil::getControlledActionsForWorkflow($oWorkflow))) {
        //    return true;
        //}
        $oState =& KTWorkflowState::getByDocument($oDocument);
        if (in_array($sName, KTWorkflowUtil::getDisabledActionsForState($oState))) {
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

        if (is_a($oDocument, 'KTDocumentCore')) {
            $oDocument = $oDocument->getId();
        }

        $oDocument = KTUtil::getObject('Document', $oDocument);
        $iWorkflowId = $oDocument->getWorkflowId();

        if (PEAR::isError($iWorkflowId)) {
            return $iWorkflowId;
        }

        if (is_null($iWorkflowId)) {
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

        if (is_a($oDocument, 'KTDocumentCore')) {
            $oDocument = $oDocument->getId();
        }

        $oDocument = KTUtil::getObject('Document', $oDocument);
        $iWorkflowStateId = $oDocument->getWorkflowStateId();

        if (PEAR::isError($iWorkflowStateId)) {
            return $iWorkflowStateId;
        }

        if (is_null($iWorkflowStateId)) {
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

            // keeping this around to make coding the replacements easier.

            /*
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
                $res = GroupUtil::getMembershipReason($oUser, $oGroup);
                if (!is_string($res)) {
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
            */

            $aGuardTriggers = KTWorkflowUtil::getGuardTriggersForTransition($oTransition);
            if (PEAR::isError($aGuardTriggers)) {
                return $aGuardTriggers; // error out?
            }
            $bBreak = false;
            foreach ($aGuardTriggers as $oTrigger) {
                if (!$oTrigger->allowTransition($oDocument, $oUser)) {
                    $bBreak = true;
                    break;
                }
            }
            if ($bBreak) { continue; }
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
            return PEAR::raiseError(_kt("Document has no workflow"));
        }
        if (PEAR::isError($oWorkflow)) {
            return $oWorkflow;
        }
        $oSourceState =& KTWorkflowUtil::getWorkflowStateForDocument($oDocument);

        // walk the action triggers.
        $aActionTriggers = KTWorkflowUtil::getActionTriggersForTransition($oTransition);
        if (PEAR::isError($aActionTriggers)) {
            return $aActionTriggers; // error out?
        }
        foreach ($aActionTriggers as $oTrigger) {
            $res = $oTrigger->precheckTransition($oDocument, $oUser);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        $iPreviousMetadataVersion = $oDocument->getMetadataVersionId();
        $oDocument->startNewMetadataVersion($oUser);
        KTDocumentUtil::copyMetadata($oDocument, $iPreviousMetadataVersion);

        $iStateId = $oTransition->getTargetStateId();

        $oDocument->setWorkflowStateId($iStateId);
        $res = $oDocument->update();
        if (PEAR::isError($res)) {
            return $res;
        }

        $oTargetState =& KTWorkflowState::get($iStateId);
        $sSourceState = $oSourceState->getName();
        $sTargetState = $oTargetState->getName();

        // create the document transaction record
        $sTransactionComments = sprintf(_kt("Workflow state changed from %s to %s"), $sSourceState, $sTargetState);

        if ($sComments) {
            $sTransactionComments .= _kt("; Reason given was: ") . $sComments;
        }
        $oDocumentTransaction = new DocumentTransaction($oDocument, $sTransactionComments, 'ktcore.transactions.workflow_state_transition');
        $oDocumentTransaction->create();

        // walk the action triggers.
        foreach ($aActionTriggers as $oTrigger) {
            $res = $oTrigger->performTransition($oDocument, $oUser);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

		KTPermissionUtil::updatePermissionLookup($oDocument);
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
            // do NOT alert anonymous or Everyone roles - that would be very scary.
            $iRoleId = KTUtil::getId($oRole);
            if (($iRoleId == -3) || ($iRoleId == -4)) {
                continue;
            }
            // first try on the document, then the folder above it.
            $oRoleAllocation = DocumentRoleAllocation::getAllocationsForDocumentAndRole($oDocument->getId(), $iRoleId);
            if (is_null($oRoleAllocation)) {
                // if we don't get a document role, try folder role.
                $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($oDocument->getFolderID(), $oRole->getId());
            }
            if (is_null($oRoleAllocation) || PEAR::isError($oRoleAllocation)) {
		continue;
	    }
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
            $aAllIds = kt_array_merge($aGroupMembershipSet[$id], $aAllIds);
        }

        foreach ($aAllIds as $id) {
            if (!array_key_exists($id, $aGroups)) {
                $aGroups[$id] = Group::get($id);
            }
        }

        // now, merge this (again) into the user-set.
        foreach ($aGroups as $oGroup) {
            $aNewUsers = $oGroup->getMembers();
            foreach ($aNewUsers as $oU) {
			    $id = $oU->getId();
                if (!array_key_exists($id, $aUsers)) {
                    $aUsers[$id] = $oU;
                }
            }
        }


        // and done.
        foreach ($aUsers as $oU) {
		    if (!PEAR::isError($oU)) {
                KTWorkflowNotification::newNotificationForDocument($oDocument, $oU, $oState, $oUser, $sComments);
			}
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

    // retrieves the triggers for a given transition in their WorkflowTrigger form.
    function getTriggersForTransition($oTransition) {
        $oKTWorkflowTriggerRegistry =& KTWorkflowTriggerRegistry::getSingleton();
        $aTriggers = array();
        $aTriggerInstances = KTWorkflowTriggerInstance::getByTransition($oTransition);
        foreach ($aTriggerInstances as $oTriggerInstance) {
            $oTrigger = $oKTWorkflowTriggerRegistry->getWorkflowTrigger($oTriggerInstance->getNamespace());
            if (PEAR::isError($oTrigger)) {
                return $oTrigger;
            }
            $oTrigger->loadConfig($oTriggerInstance);
            $aTriggers[] = $oTrigger;
        }
        return $aTriggers;
    }

    function getGuardTriggersForTransition($oTransition) {
        $aTriggers = KTWorkflowUtil::getTriggersForTransition($oTransition);
        if (PEAR::isError($aTriggers)) {
            return $aTriggers;
        }
        $aGuards = array();
        foreach ($aTriggers as $oTrigger) {
            $aInfo = $oTrigger->getInfo();
            if ($aInfo['guard']) {
                $aGuards[] = $oTrigger;
            }
        }
        return $aGuards;
    }

    function getActionTriggersForTransition($oTransition) {
        $aTriggers = KTWorkflowUtil::getTriggersForTransition($oTransition);
        if (PEAR::isError($aTriggers)) {
            return $aTriggers;
        }
        $aGuards = array();
        foreach ($aTriggers as $oTrigger) {
            $aInfo = $oTrigger->getInfo();
            if ($aInfo['action']) {
                $aGuards[] = $oTrigger;
            }
        }
        return $aGuards;
    }


    function replaceState($oState, $oReplacement) {
        $state_id = KTUtil::getId($oState);
        $replacement_id = KTUtil::getId($oReplacement);

        // we need to convert:
        //   - documents
        //   - transitions
        // before we do a delete.
        $doc = KTUtil::getTableName('document_metadata_version');
        $aDocQuery = array(
            "UPDATE $doc SET workflow_state_id = ? WHERE workflow_state_id = ?",
            array($replacement_id, $state_id),
        );
        $res = DBUtil::runQuery($aDocQuery);
        if (PEAR::isError($res)) { return $res; }

        $wf = KTUtil::getTableName('workflow_transitions');
        $aTransitionQuery = array(
            "UPDATE $wf SET target_state_id = ? WHERE target_state_id = ?",
            array($replacement_id, $state_id),
        );
        $res = DBUtil::runQuery($aTransitionQuery);
        if (PEAR::isError($res)) { return $res; }

        $wf = KTUtil::getTableName('workflow_state_transitions');
        $aTransitionQuery = array(
            "DELETE FROM $wf WHERE state_id = ?",
            array($state_id),
        );
        $res = DBUtil::runQuery($aTransitionQuery);
        if (PEAR::isError($res)) { return $res; }

        Document::clearAllCaches();
    }
}

class KTWorkflowTriggerRegistry {
    var $triggers;

    function KTWorkflowTriggerRegistry() {
        $this->triggers = array();
    }

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTWorkflowTriggerRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTWorkflowTriggerRegistry'] = new KTWorkflowTriggerRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTWorkflowTriggerRegistry'];
    }

    function registerWorkflowTrigger($sNamespace, $sClassname, $sPath) {
        $this->triggers[$sNamespace] = array('class' => $sClassname, 'path' => $sPath);
    }

    function getWorkflowTrigger($sNamespace) {
        $aInfo = KTUtil::arrayGet($this->triggers, $sNamespace, null);
        if (is_null($aInfo)) {
            return PEAR::raiseError(sprintf(_kt("Unable to find workflow trigger: %s"), $sNamespace));
        }

        require_once($aInfo['path']);

        return new $aInfo['class'];
    }

    // get a keyed list of workflow triggers

    function listWorkflowTriggers() {
        $triggerlist = array();
        foreach ($this->triggers as $sNamespace => $aTrigInfo) {
            $oTriggerObj = $this->getWorkflowTrigger($sNamespace);
            $triggerlist[$sNamespace] = $oTriggerObj->getInfo();
        }
        // FIXME do we want to order this alphabetically?
        return $triggerlist;
    }
}


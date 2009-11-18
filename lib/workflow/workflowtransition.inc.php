<?php
/**
 * $Id$
 *
 * Describes a transition to another state, available from some other
 * states, subject to permissions and user-providable scripts.
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

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTWorkflowTransition extends KTEntity {
    var $iId = -1;
    var $iWorkflowId;
    var $sName;
    var $sHumanName;
    var $iTargetStateId;
    var $iGuardPermissionId = null;
    var $iGuardGroupId = null;
    var $iGuardRoleId = null;
    var $iGuardConditionId = null;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iWorkflowId" => "workflow_id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "iTargetStateId" => "target_state_id",
        "iGuardPermissionId" => "guard_permission_id",
        "iGuardGroupId" => "guard_group_id",
        "iGuardRoleId" => "guard_role_id",
        "iGuardConditionId" => "guard_condition_id",
    );

    var $_bUsePearError = true;

    function getId() { return $this->iId; }
    function getName() { return ($this->sName); }
    function getHumanName() { return ($this->sHumanName); }
    function getWorkflowId() { return $this->iWorkflowId; }
    function getTargetStateId() { return $this->iTargetStateId; }
    function getGuardPermissionId() { return $this->iGuardPermissionId; }
    function getGuardGroupId() { return $this->iGuardGroupId; }
    function getGuardRoleId() { return $this->iGuardRoleId; }
    function getGuardConditionId() { return $this->iGuardConditionId; }

    function setId($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = ($sName); }
    function setHumanName($sHumanName) { $this->sHumanName = ($sHumanName); }
    function setWorkflowId($iWorkflowId) { $this->iWorkflowId = $iWorkflowId; }
    function setTargetStateId($iTargetStateId) { $this->iTargetStateId = $iTargetStateId; }
    function setGuardPermissionId($iGuardPermissionId) { $this->iGuardPermissionId = $iGuardPermissionId; }
    function setGuardGroupId($iGuardGroupId) { $this->iGuardGroupId = $iGuardGroupId; }
    function setGuardRoleId($iGuardRoleId) { $this->iGuardRoleId = $iGuardRoleId; }
    function setGuardConditionId($iGuardConditionId) { $this->iGuardConditionId = $iGuardConditionId; }

    function _table () {
        return KTUtil::getTableName('workflow_transitions');
    }

    // STATIC
    function _ktentityOptions() {
        return array(
            'orderby' => 'human_name',
        );
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTWorkflowTransition', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTWorkflowTransition', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTWorkflowTransition', $sWhereClause);
    }

    // STATIC
    function &getByName($sName) {
        return KTEntityUtil::getBy('KTWorkflowTransition', 'name', $sName);
    }

    // STATIC
    function &getByWorkflow($oWorkflow) {
        $iWorkflowId = KTUtil::getId($oWorkflow);

        $aOptions = array(
            'multi' => true,
        );
        return KTEntityUtil::getBy('KTWorkflowTransition', 'workflow_id', $iWorkflowId, $aOptions);
    }

    // STATIC
    function &getByTargetState($oState) {
        $iStateId = KTUtil::getId($oState);

        $aOptions = array(
            'multi' => true,
        );
        return KTEntityUtil::getBy('KTWorkflowTransition', 'target_state_id', $iStateId, $aOptions);
    }

    // STATIC
    function &getBySourceState($oState) {
        return KTWorkflowUtil::getTransitionsFrom($oState);
    }

    function showDescription() {
        $oWorkflowState =& KTWorkflowState::get($this->getTargetStateId());
        return sprintf(_kt("%s (to state %s)"), $this->getName(), $oWorkflowState->getName());
    }

    // STATIC
    function nameExists($sName, $oWorkflow) {
        $iWorkflowId = KTUtil::getId($oWorkflow);
        $res = KTEntityUtil::getByDict(
            'KTWorkflowTransition', array(
                'name' => $sName,
                'workflow_id' => $iWorkflowId
            )
        );
        // expect KTEntityNoObjects
        if (PEAR::isError($res)) {
            return false;
        }

        return true;
    }

}

?>

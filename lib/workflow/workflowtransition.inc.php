<?php
/**
 * $Id$
 *
 * Describes a transition to another state, available from some other
 * states, subject to permissions and user-providable scripts.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");

class KTWorkflowTransition extends KTEntity {
    var $iId = -1;
    var $iWorkflowId;
    var $sName;
    var $sHumanName;
    var $iTargetStateId;
    var $iGuardPermissionId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iWorkflowId" => "workflow_id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "iTargetStateId" => "target_state_id",
        "iGuardPermissionId" => "guard_permission_id",
    );

    var $_bUsePearError = true;

    function getId() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getWorkflowId() { return $this->iWorkflowId; }
    function getTargetStateId() { return $this->iTargetStateId; }
    function getGuardPermissionId() { return $this->iGuardPermissionId; }

    function setId($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setWorkflowId($iWorkflowId) { $this->iWorkflowId = $iWorkflowId; }
    function setTargetStateId($iTargetStateId) { $this->iTargetStateId = $iTargetStateId; }
    function setGuardPermissionId($iGuardPermissionId) { $this->iGuardPermissionId = $iGuardPermissionId; }

    function _table () {
        return KTUtil::getTableName('workflow_transitions');
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
}

?>

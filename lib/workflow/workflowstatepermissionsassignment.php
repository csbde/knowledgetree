<?php
/**
 * $Id$
 *
 * Describes the permissions that apply to a document in a given
 * workflow state.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
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

class KTWorkflowStatePermissionAssignment extends KTEntity {
    var $iStateId;
    var $iPermissionId;
    var $iDescriptorId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iStateId" => "workflow_state_id",
        "iPermissionId" => "permission_id",
        "iDescriptorId" => "permission_descriptor_id",
    );

    var $_bUsePearError = true;

    function getStateId() { return $this->iStateId; }
    function getPermissionId() { return $this->iPermissionId; }
    function getDescriptorId() { return $this->iDescriptorId; }
    function setStateId($mValue) { $this->iStateId = $mValue; }
    function setPermissionId($mValue) { $this->iPermissionId = $mValue; }
    function setDescriptorId($mValue) { $this->iDescriptorId = $mValue; }

    function _table () {
        return KTUtil::getTableName('workflow_state_permission_assignments');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTWorkflowStatePermissionAssignment', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return
            KTEntityUtil::createFromArray('KTWorkflowStatePermissionAssignment', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return
            KTEntityUtil::getList2('KTWorkflowStatePermissionAssignment', $sWhereClause);
    }
}

?>

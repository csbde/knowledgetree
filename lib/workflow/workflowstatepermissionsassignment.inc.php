<?php
/**
 * $Id$
 *
 * Describes the permissions that apply to a document in a given
 * workflow state.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
    
    function &getByState($oState) {
        $iStateId = KTUtil::getId($oState);
        return KTEntityUtil::GetList2('KTWorkflowStatePermissionAssignment', 'workflow_state_id = ' . $iStateId);
    }
    
    function getAllowed() {
        $oDescriptor = KTPermissionDescriptor::get($this->iDescriptorId);
        return $oDescriptor->getAllowed();
    }
}

?>

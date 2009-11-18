<?php
/**
 * $Id$
 *
 * Describes the permissions that apply to a document in a given
 * workflow state.
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

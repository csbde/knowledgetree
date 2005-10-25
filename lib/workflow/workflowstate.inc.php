<?php
/**
 * $Id$
 *
 * Describes a state for a document, representing how far along in its
 * workflow it is, and providing a set of transitions to other states.
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

class KTWorkflowState extends KTEntity {
    var $iId = -1;
    var $iWorkflowId;
    var $sName;
    var $sHumanName;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iWorkflowId" => "workflow_id",
        "sName" => "name",
        "sHumanName" => "human_name",
        'iInformDescriptorId' => 'inform_descriptor_id',
    );

    var $_bUsePearError = true;

    function getId() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getWorkflowId() { return $this->iWorkflowId; }
    function getInformDescriptorId() { return $this->iInformDescriptorId; }
    function setId($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setWorkflowId($iWorkflowId) { $this->iWorkflowId = $iWorkflowId; }
    function setInformDescriptorId($iInformDescriptorId) { $this->iInformDescriptorId = $iInformDescriptorId; }

    function _table () {
        return KTUtil::getTableName('workflow_states');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTWorkflowState', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTWorkflowState', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTWorkflowState', $sWhereClause);
    }

    // STATIC
    function &getByName($sName) {
        return KTEntityUtil::getBy('KTWorkflowState', 'name', $sName);
    }

    // STATIC
    function &getByWorkflow($oWorkflow) {
        $iWorkflowId = KTUtil::getId($oWorkflow);

        $aOptions = array('multi' => true);
        return KTEntityUtil::getBy('KTWorkflowState', 'workflow_id', $iWorkflowId, $aOptions);
    }

    // STATIC
    function &getByDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $sTable = KTUtil::getTableName('workflow_documents');
        $iStateId = DBUtil::getOneResultKey(array(
                "SELECT state_id FROM $sTable WHERE document_id = ?",
                array($iDocumentId),
            ), 'state_id'
        );

        if (PEAR::isError($iStateId)) {
            return $iStateId;
        }

        if (is_null($iStateId)) {
            return $iStateId;
        }

        return KTWorkflowState::get($iStateId);
    }

}

?>

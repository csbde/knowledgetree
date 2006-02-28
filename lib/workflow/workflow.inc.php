<?php
/**
 * $Id$
 *
 * Describes a workflow for a document - a set of states that the
 * document can be in and a set of transitions that allow that document
 * to change to other states.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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

class KTWorkflow extends KTEntity {
    var $iId = -1;
    var $sName;
    var $sHumanName;
    var $iStartStateId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "iStartStateId" => "start_state_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getStartStateId() { return $this->iStartStateId; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setStartStateId($iStartStateId) { $this->iStartStateId = $iStartStateId; }

    function _table () {
        return KTUtil::getTableName('workflows');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTWorkflow', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTWorkflow', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTWorkflow', $sWhereClause);
    }

    // STATIC
    function &getByName($sName) {
        return KTEntityUtil::getBy('KTWorkflow', 'name', $sName);
    }

    // STATIC
    function &getFunctional() {
        return KTEntityUtil::getList2('KTWorkflow', 'start_state_id IS NOT NULL');
    }

    function &getByDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $sTable = KTUtil::getTableName('workflow_documents');
        $iWorkflowId = DBUtil::getOneResultKey(array(
                "SELECT workflow_id FROM $sTable WHERE document_id = ?",
                array($iDocumentId),
            ), 'workflow_id'
        );

        if (PEAR::isError($iWorkflowId)) {
            return $iWorkflowId;
        }

        if (is_null($iWorkflowId)) {
            return $iWorkflowId;
        }

        return KTWorkflow::get($iWorkflowId);
    }
}

?>

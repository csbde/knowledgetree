<?php
/**
 * $Id$
 *
 * Describes a workflow for a document - a set of states that the
 * document can be in and a set of transitions that allow that document
 * to change to other states.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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

class KTWorkflow extends KTEntity {
    var $iId = -1;
    var $sName;
    var $sHumanName;
    var $iStartStateId;
    var $bEnabled;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "iStartStateId" => "start_state_id",
        'bEnabled' => 'enabled',
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getStartStateId() { return $this->iStartStateId; }
    function getIsEnabled() { return ($this->bEnabled == true); }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setStartStateId($iStartStateId) { $this->iStartStateId = $iStartStateId; }
    function setIsEnabled($mValue) { $this->bEnabled = ($mValue == true); }

    function _table () {
        return KTUtil::getTableName('workflows');
    }

    function _ktentityOptions() {
        return array(
            'orderby' => 'human_name',
        );
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
    
    function getIsFunctional() {
        return (($this->getStartStateId() != false) && ($this->getIsEnabled()));
    }

    // STATIC
    function &getFunctional() {
        return KTEntityUtil::getList2('KTWorkflow', 'start_state_id IS NOT NULL AND enabled = 1');
    }

    function &getByDocument($oDocument) {
        $oDocument = KTUtil::getObject('Document', $oDocument);
        $iWorkflowId = $oDocument->getWorkflowId();

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

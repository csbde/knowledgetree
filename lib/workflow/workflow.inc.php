<?php
/**
 * $Id$
 *
 * Describes a workflow for a document - a set of states that the
 * document can be in and a set of transitions that allow that document
 * to change to other states.
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
require_once(KT_LIB_DIR . "/util/sanitize.inc");

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
    function getName() { return ($this->sName); }
    function getHumanName() { return ($this->sHumanName); }
    function getStartStateId() { return $this->iStartStateId; }
    function getIsEnabled() { return ($this->bEnabled == true); }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = ($sName); }
    function setHumanName($sHumanName) { $this->sHumanName = ($sHumanName); }
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

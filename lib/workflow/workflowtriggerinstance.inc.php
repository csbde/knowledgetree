<?php
/**
 * $Id$
 *
 * Provides both association between a transition and a trigger, and a 
 * way to store the configuration for that instance.
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

class KTWorkflowTriggerInstance extends KTEntity {
    var $iId = -1;
    var $sConfigArrayText;
    var $sNamespace;
    var $iTransitionId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sConfigArrayText" => "config_array",
        "sNamespace" => "namespace",
        "iTransitionId" => "workflow_transition_id",
    );

    var $_bUsePearError = true;

    function getTransitionId() { return $this->iTransitionId; }
    function setTransitionId($iNewValue) { $this->iTransitionId = $iNewValue; }
    function getNamespace() { return $this->sNamespace; }
    function setNamespace($sNewValue) { $this->sNamespace = $sNewValue; }    
    function getConfigArrayText() { return $this->sConfigArrayText; }
    function setConfigArrayText($sNewValue) { $this->sConfigArrayText = $sNewValue; }
    function getConfig() { return unserialize($this->sConfigArrayText); }
    function setConfig($aNewValue) { $this->sConfigArrayText = serialize($aNewValue); }

    function _table () {
        return KTUtil::getTableName('workflow_trigger_instances');
    }

    // STATIC
    function &get($iId) { return KTEntityUtil::get('KTWorkflowTriggerInstance', $iId); }

    function &createFromArray($aOptions) {
        // Modified : Jarrett Jordaan
        // Removed Serialize, since the original is serialized already
        $aOptions['configarraytext'] = $aOptions['config'];
        unset($aOptions['config']);
        return KTEntityUtil::createFromArray('KTWorkflowTriggerInstance', $aOptions); 
    }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTWorkflowTriggerInstance', $sWhereClause); }
    function &getByTransition($oTransition, $aOptions = null) {
        $iTransitionId = KTUtil::getId($oTransition);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'multi' => true,
        ));
        return KTEntityUtil::getByDict('KTWorkflowTriggerInstance', array(
            'workflow_transition_id' => $iTransitionId,
        ), $aOptions);
    }

}

?>

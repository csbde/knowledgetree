<?php
/**
 * $Id: workflowtriggerinstance.inc.php 5268 2006-04-18 13:42:22Z nbm $
 *
 * Provides both association between a transition and a trigger, and a 
 * way to store the configuration for that instance.
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
 * @version $Revision: 5268 $
 * @author Brad Shuttleworth, Jam Warehouse (Pty) Ltd, South Africa
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
        $aOptions['configarraytext'] = serialize($aOptions['config']);
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

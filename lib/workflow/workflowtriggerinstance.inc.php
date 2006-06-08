<?php
/**
 * $Id: workflowtriggerinstance.inc.php 5268 2006-04-18 13:42:22Z nbm $
 *
 * Provides both association between a transition and a trigger, and a 
 * way to store the configuration for that instance.
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

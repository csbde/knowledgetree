<?php

/**
 * $Id$
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
 */

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');


class WorkflowAllocationSelection extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/automatic workflows.html';
    var $bAutomaticTransaction = true;
    var $sSection = 'administration';

    function check() {
        $res = parent::check();
        if (!$res) { return false; }
        
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name'=> _kt('Automatic Workflow Assignments'));
        
        return true;
    }

    function do_main() { 
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('workflow', 'objectModification');

        $aFields = array();
        $aVocab = array();
        $aVocab[] = _kt('No automatic assignment');
        foreach ($aTriggers as $aTrigger) {
            $aVocab[$aTrigger[2]] = $aTrigger[0];
        }
        $aFields[] = new KTLookupWidget(_kt('Workflow Plugins'), _kt('Plugins providing workflow allocators.'),'selection_ns', $this->getHandler(), $this->oPage, true, null, null, array('vocab' => $aVocab));
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/workflow/allocator_selection');
        $oTemplate->setData(array(
            'context' => $this,
            'trigger_fields' => $aFields,
        ));
        return $oTemplate->render();        
    }
    
    function getHandler() {
        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $sQuery .= ' WHERE event_ns = ?';
        $aParams = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');
        return $res;
        
    }
    
    function do_assign_handler() {
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('workflow', 'objectModification');
        
        $selection_ns = KTUtil::arrayGet($_REQUEST, 'selection_ns');
        if (empty($selection_ns)) {
            $sQuery = 'DELETE FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            DBUtil::runQuery(array($sQuery, $aParams));
            $this->successRedirectToMain(_kt('Handler removed.'));
        } else {
            if (!array_key_exists($selection_ns, $aTriggers)) {
                $this->errorRedirectToMain(_kt('Invalid assignment'));
            }
        
        
            // clear
            $sQuery = 'DELETE FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            DBUtil::runQuery(array($sQuery, $aParams));
            
            // set 
            $sQuery = 'INSERT INTO ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' (event_ns, selection_ns)';
            $sQuery .= ' VALUES ("ktstandard.workflowassociation.handler",?)';
            $aParams = array($selection_ns);
            DBUtil::runQuery(array($sQuery, $aParams));
            $this->successRedirectToMain(_kt('Handler set.'));
        }
    }
}

?>

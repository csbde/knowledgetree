<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');

require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

class KTWorkflowAssociationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.workflowassociation.plugin";

    function setup() {
        $this->registerTrigger('add', 'postValidate', 'KTWADAddTrigger',
            'ktstandard.triggers.workflowassociation.addDocument');    
        $this->registerTrigger('moveDocument', 'postValidate', 'KTWADMoveTrigger',
            'ktstandard.triggers.workflowassociation.moveDocument');
        $this->registerTrigger('edit', 'postValidate', 'KTWADEditTrigger',
            'ktstandard.triggers.workflowassociation.editDocument');
        $this->registerAdminPage('workflow_allocation', 'WorkflowAllocationSelection', 
            'documents', _kt('Automatic Workflow Assignments'), 
            _kt('Configure how documents are allocated to workflows.'), 'workflow/adminpage.php');        
            $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');
    }
}

// base class for delegation.
class KTWorkflowAssociationDelegator {
    var $_handler;
    var $_document;

    function KTWorkflowAssociationDelegator() {
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('workflow', 'objectModification');
        
        // if we have _some_ triggers.
        if (!empty($aTriggers)) {
            $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');
        
            if (PEAR::isError($res)) { $this->_handler = new KTWorkflowAssociationHandler(); }
            
            if (array_key_exists($res, $aTriggers)) {
                $this->_handler = new $aTriggers[$res][0];
            }
            else {
                $this->_handler = new KTWorkflowAssociationHandler();                 
            }
        }
        else {
            $this->_handler = new KTWorkflowAssociationHandler(); 
        }
    }
    
    function applyWorkflow($oWorkflow) {
        return true;
    }
    
    function setInfo($aOptions) {
        $this->_document = $aOptions['document'];
    }
     
    function postValidate() {
        return KTWorkflowUtil::getWorkflowForDocument($this->_document);
    }
}

// Add
class KTWADAddTrigger extends KTWorkflowAssociationDelegator {
    function postValidate() {
        $oWorkflow = $this->_handler->addTrigger($this->_document);
        
        // catch disabled workflows.
        if (!is_null($oWorkflow) && (PEAR::isError($oWorkflow) || ($oWorkflow->getStartStateId() === null))) {
            return ;          
        }
        
        $ret = KTWorkflowUtil::startWorkflowOnDocument($oWorkflow, $this->_document);
    }
}

// Edit
class KTWADEditTrigger extends KTWorkflowAssociationDelegator {
    function postValidate() {
        $oWorkflow = $this->_handler->editTrigger($this->_document);
        
        // catch disabled workflows.
        if (!is_null($oWorkflow) && (PEAR::isError($oWorkflow) || ($oWorkflow->getStartStateId() === null))) {
            return ;          
        }
                
        $ret = KTWorkflowUtil::changeWorkflowOnDocument($oWorkflow, $this->_document);
    }
}

// Move
class KTWADMoveTrigger extends KTWorkflowAssociationDelegator {
    function postValidate() {
        $oWorkflow = $this->_handler->moveTrigger($this->_document);
        
        // catch disabled workflows.
        if (!is_null($oWorkflow) && (PEAR::isError($oWorkflow) || ($oWorkflow->getStartStateId() === null))) {
            return ;          
        }
                
        $ret = KTWorkflowUtil::changeWorkflowOnDocument($oWorkflow, $this->_document);
    }
}

// "base class" for fallback.  should be subclassed and cross-referenced, otherwise 
// has no impact when called.
class KTWorkflowAssociationHandler {
    function addTrigger($oDocument) { return KTWorkflowUtil::getWorkflowForDocument($oDocument); }
    function editTrigger($oDocument) { return KTWorkflowUtil::getWorkflowForDocument($oDocument); }
    function moveTrigger($oDocument) { return KTWorkflowUtil::getWorkflowForDocument($oDocument); }
}


$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTWorkflowAssociationPlugin', 'ktstandard.workflowassociation.plugin', __FILE__);


/* include others */

require_once('workflow/TypeAssociator.php');
require_once('workflow/FolderAssociator.php');

?>

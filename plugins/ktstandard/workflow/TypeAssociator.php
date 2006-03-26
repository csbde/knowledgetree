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

require_once(KT_DIR . '/plugins/ktstandard/KTWorkflowAssociation.php');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

class KTDocTypeWorkflowAssociationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.workflowassociation.documenttype.plugin";

    function setup() {
        $this->registerTrigger('workflow', 'objectModification', 'DocumentTypeWorkflowAssociator',
            'ktstandard.triggers.workflowassociation.documenttype.handler');
        
        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');
        
        if ($res == 'ktstandard.triggers.workflowassociation.documenttype.handler') {
            $this->registerAdminPage('workflow_type_allocation', 'WorkflowTypeAllocationDispatcher', 
                'documents', _kt('Workflow Allocation by Document Types'), 
                _kt('This installation assigns workflows by Document Type. Configure this process here.'), __FILE__);        
            $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');
        }
        
    }
}

class DocumentTypeWorkflowAssociator extends KTWorkflowAssociationHandler {
    function addTrigger($oDocument) { 
       return $oW = $this->getWorkflowForType($oDocument->getDocumentTypeID());       
    }
    
    function editTrigger($oDocument) { 
       return $oW = $this->getWorkflowForType($oDocument->getDocumentTypeID());       
    }
    
    function getWorkflowForType($iDocTypeId) {
        if (is_null($iDocTypeId)) { return null; }
        
        $sQuery = 'SELECT `workflow_id` FROM ' . KTUtil::getTableName('type_workflow_map');
        $sQuery .= ' WHERE `document_type_id` = ?';
        $aParams = array($iDocTypeId);
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'workflow_id');
        if (PEAR::isError($res)) { 
            return null;
        }
        return KTWorkflow::get($res);
    }
}

class WorkflowTypeAllocationDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
    var $sSection = 'administration';

    function check() {
        $res = parent::check();
        if (!$res) { return false; }
        
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name'=> _kt('Workflow Allocation by Document Types'));
        
        return true;
    }

    function do_main() { 
        $sQuery = 'SELECT document_type_id, workflow_id FROM ' . KTUtil::getTableName('type_workflow_map');
        $aParams = array();
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        $aWorkflows = KTWorkflow::getList('start_state_id IS NOT NULL');
        $aTypes = DocumentType::getList();
        
        $aTypeMapping = array();
        if (PEAR::isError($res)) {
            $this->oPage->addError(_kt('Failed to get type mapping: ') . $res->getMessage());
        } else {
            foreach ($res as $aRow) {
                $aTypeMapping[$aRow['document_type_id']] = $aRow['workflow_id'];
            }
        }
    
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/workflow/type_allocation');
        $oTemplate->setData(array(
            'context' => $this,
            'types_mapping' => $aTypeMapping,
            'types' => $aTypes,
            'workflows' => $aWorkflows,
        ));
        return $oTemplate->render();
    }
    
    function isActiveWorkflow($oType, $oWorkflow, $types_mapping) {
        if (!array_key_exists($oType->getId(), $types_mapping)) { return false; }
        else {
            return $types_mapping[$oType->getId()] == $oWorkflow->getId();
        }
    }
    
    function do_update() {
        $types_mapping = (array) KTUtil::arrayGet($_REQUEST, 'fDocumentTypeAssignment');
        
        $aWorkflows = KTWorkflow::getList();
        $aTypes = DocumentType::getList();
        
        $sQuery = 'DELETE FROM ' . KTUtil::getTableName('type_workflow_map');
        $aParams = array();
        DBUtil::runQuery(array($sQuery, $aParams));
        
        $aOptions = array('noid' => true);
        $sTable = KTUtil::getTableName('type_workflow_map');
        foreach ($aTypes as $oType) {
            $t = $types_mapping[$oType->getId()];
            if ($t == null) { $t = null; }
            $res = DBUtil::autoInsert($sTable, array(
                'document_type_id' => $oType->getId(),
                'workflow_id' => $t,
            ), $aOptions);
        }
        
        $this->successRedirectToMain(_kt('Type mapping updated.'));
    }
}


$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTDocTypeWorkflowAssociationPlugin', 'ktstandard.workflowassociation.documenttype.plugin', __FILE__);


?>

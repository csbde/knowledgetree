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

require_once(KT_DIR . '/plugins/ktstandard/KTWorkflowAssociation.php');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

class KTFolderWorkflowAssociationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.workflowassociation.folder.plugin";
    
    function KTFolderWorkflowAssociationPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Workflow allocation by location');
        return $res;
    }              

    function setup() {
        $this->registerTrigger('workflow', 'objectModification', 'FolderWorkflowAssociator',
            'ktstandard.triggers.workflowassociation.folder.handler');
        
        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $sQuery .= ' WHERE event_ns = ?';
        $aParams = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');
        
        if ($res == 'ktstandard.triggers.workflowassociation.folder.handler') {
            $this->registerAction('folderaction', 'FolderWorkflowAssignmentFolderAction',
                'ktstandard.workflowassociation.folder.action');
            }
        }
}

class FolderWorkflowAssociator extends KTWorkflowAssociationHandler {
    function addTrigger($oDocument) { 
       return $this->getWorkflowForDoc($oDocument);       
    }
    
    function editTrigger($oDocument) { 
       return $this->getWorkflowForDoc($oDocument);       
    }
    
    function getWorkflowForDoc($oDocument) {
        
        $sQuery = 'SELECT `workflow_id` FROM ' . KTUtil::getTableName('folder_workflow_map');
        $sQuery .= ' WHERE `folder_id` = ?';
        $aParams = array($oDocument->getFolderID());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'workflow_id');
        
        
        if (PEAR::isError($res) || (is_null($res))) { 
            return KTWorkflowUtil::getWorkflowForDocument($oDocument); // don't remove if moved out.
        }
        return KTWorkflow::get($res);
    }
}

class FolderWorkflowAssignmentFolderAction extends KTFolderAction {
    var $sName = 'ktstandard.workflowassociation.folder.action';

    var $_sShowPermission = "ktcore.permissions.workflow";

    function getDisplayName() {
        return _kt('Configure Workflows');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Configure Workflows for Folder"));
        $this->oPage->setTitle(_kt("Configure Workflows for Folder"));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/workflow/folderconfigure');
        $fields = array();
        
        $aWorkflows = KTWorkflow::getList('start_state_id IS NOT NULL');
        $aVocab = array();
        $aVocab[] = _kt('No automatic workflow.');
        foreach ($aWorkflows as $oWorkflow) {
            $aVocab[$oWorkflow->getId()] = $oWorkflow->getName();   
        }
        $fieldOptions = array("vocab" => $aVocab);
        
        // grab the value.
        $sQuery = 'SELECT `workflow_id` FROM ' . KTUtil::getTableName('folder_workflow_map');
        $sQuery .= ' WHERE `folder_id` = ?';
        $aParams = array($this->oFolder->getId());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'workflow_id');
        if (PEAR::isError($res)) {
            $res = null;
        }
        $fields[] = new KTLookupWidget(_kt('Automatic Workflow'), _kt('If you specify an automatic workflow, new documents will automatically enter that workflow\'s starting state.  Setting this to "No Automatic Workflow" will mean that users can choose the appropriate workflow.'), 'fWorkflowId', $res, $this->oPage, true, null, $fieldErrors, $fieldOptions);
        
        
        $oTemplate->setData(array(
            'context' => &$this,
            'folder_id' => $this->oFolder->getId(),
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_allocate() {
        $fWorkflowId = KTUtil::arrayGet($_REQUEST, 'fWorkflowId', null);
        
        $this->startTransaction();
        
        $sQuery = 'DELETE FROM '  . KTUtil::getTableName('folder_workflow_map') . ' WHERE folder_id = ?';
        $aParams = array($this->oFolder->getId());
        DBUtil::runQuery(array($sQuery, $aParams));
        
        if (is_null($fWorkflowId)) {
            $this->successRedirectToMain(_kt('Workflow assignment removed.'), 'fFolderId='.$this->oFolder->getId());
        }
        
        $aOptions = array('noid' => true);
        $sTable = KTUtil::getTableName('folder_workflow_map');
        if ($fWorkflowId == null) { $fWorkflowId = null; }
        $res = DBUtil::autoInsert($sTable, array(
            'folder_id' => $this->oFolder->getId(),
            'workflow_id' => $fWorkflowId,
            ), $aOptions);
            
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_kt('Error assigning workflow.'), 'fFolderId='.$this->oFolder->getId());
        }
        
        $this->successRedirectToMain(_kt('Workflow assignment updated.'), 'fFolderId='.$this->oFolder->getId());
    }

}


$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTFolderWorkflowAssociationPlugin', 'ktstandard.workflowassociation.folder.plugin', __FILE__);


?>

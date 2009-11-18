<?php
/**
 * $Id$
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
        }

    /**
     * Method to setup the plugin on rendering it
     *
     * @return unknown
     */
    function run_setup() {
        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $sQuery .= ' WHERE event_ns = ?';
        $aParams = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');

        if ($res == 'ktstandard.triggers.workflowassociation.folder.handler') {
            $this->registerAction('folderaction', 'FolderWorkflowAssignmentFolderAction',
                'ktstandard.workflowassociation.folder.action');
        }else{
            $this->deRegisterPluginHelper('ktstandard.workflowassociation.folder.action', 'action');
        }
        return true;
    }
}

class FolderWorkflowAssociator extends KTWorkflowAssociationHandler {
    function addTrigger($oDocument) {
       return $this->getWorkflowForDoc($oDocument);
    }

    function editTrigger($oDocument) {
       return $this->getWorkflowForDoc($oDocument);
    }

    function moveTrigger($oDocument) {
       return $this->getWorkflowForDoc($oDocument);
    }

    function copyTrigger($oDocument) {
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

        $aWorkflows = KTWorkflow::getList('start_state_id IS NOT NULL AND enabled = 1');
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

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
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

class KTDocTypeWorkflowAssociationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.workflowassociation.documenttype.plugin";
    function KTDocTypeWorkflowAssociationPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Workflow allocation by document type');
        return $res;
    }

    function setup() {
        $this->registerTrigger('workflow', 'objectModification', 'DocumentTypeWorkflowAssociator',
            'ktstandard.triggers.workflowassociation.documenttype.handler');
        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');
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

        if ($res == 'ktstandard.triggers.workflowassociation.documenttype.handler') {
            $this->registerAdminPage('workflow_type_allocation', 'WorkflowTypeAllocationDispatcher',
                'documents', _kt('Workflow Allocation by Document Types'),
                _kt('This installation assigns workflows by Document Type. Configure this process here.'), __FILE__);
        }else{
            $this->deRegisterPluginHelper('documents/workflow_type_allocation', 'admin_page');
        }
        return true;
    }
}

class DocumentTypeWorkflowAssociator extends KTWorkflowAssociationHandler {
    function addTrigger($oDocument) {
       return $this->getWorkflowForType($oDocument->getDocumentTypeID(), $oDocument);
    }

    function editTrigger($oDocument) {
       return $this->getWorkflowForType($oDocument->getDocumentTypeID(), $oDocument);
    }

    function moveTrigger($oDocument) {
       return $this->getWorkflowForType($oDocument->getDocumentTypeID(), $oDocument);
    }

    function copyTrigger($oDocument) {
       return $this->getWorkflowForType($oDocument->getDocumentTypeID(), $oDocument);
    }


    function getWorkflowForType($iDocTypeId, $oDocument) {
        if (is_null($iDocTypeId)) { return null; }

        // Link to the workflows table to ensure disabled workflows aren't associated
        $sQuery = 'SELECT `workflow_id` FROM ' . KTUtil::getTableName('type_workflow_map') .' m';
        $sQuery .= ' LEFT JOIN workflows w ON w.id = m.workflow_id
            WHERE document_type_id = ? AND w.enabled = 1';

        $aParams = array($iDocTypeId);
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'workflow_id');

        if (PEAR::isError($res) || (is_null($res))) {
            return KTWorkflowUtil::getWorkflowForDocument($oDocument); // don't remove if type changed out.
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

        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $sQuery .= ' WHERE event_ns = ?';
        $aParams = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');

        if ($res != 'ktstandard.triggers.workflowassociation.documenttype.handler') {
            return false;
        }

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name'=> _kt('Workflow Allocation by Document Types'));

        return true;
    }

    function do_main() {
        $sQuery = 'SELECT document_type_id, workflow_id FROM ' . KTUtil::getTableName('type_workflow_map');
        $aParams = array();
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        $aWorkflows = KTWorkflow::getList('start_state_id IS NOT NULL AND enabled = 1');
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

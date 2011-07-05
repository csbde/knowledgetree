<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

    var $sNamespace = 'ktstandard.workflowassociation.documenttype.plugin';

    function KTDocTypeWorkflowAssociationPlugin($filename = null)
    {
        $res = parent::KTPlugin($filename);
        $this->sFriendlyName = _kt('Workflow allocation by document type');
        return $res;
    }

    function setup()
    {
        $this->registerTrigger('workflow', 'objectModification', 'DocumentTypeWorkflowAssociator',
            'ktstandard.triggers.workflowassociation.documenttype.handler');
        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');
    }

    /**
     * Method to setup the plugin on rendering it
     *
     * @return unknown
     */
    function run_setup()
    {
        $query = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $query .= ' WHERE event_ns = ?';
        $params = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($query, $params), 'selection_ns');

        if ($res == 'ktstandard.triggers.workflowassociation.documenttype.handler') {
            $this->registerAdminPage('workflow_type_allocation', 'WorkflowTypeAllocationDispatcher',
                'workflows', _kt('Workflow Allocation by Document Types'),
                _kt('This installation assigns workflows by Document Type. Configure this process here.'), __FILE__);
        }
        else {
            $this->deRegisterPluginHelper('contentManagement/workflow_type_allocation', 'admin_page');
        }

        return true;
    }

}

class DocumentTypeWorkflowAssociator extends KTWorkflowAssociationHandler {

    function addTrigger($document)
    {
       return $this->getWorkflowForType($document->getDocumentTypeID(), $document);
    }

    function editTrigger($document)
    {
       return $this->getWorkflowForType($document->getDocumentTypeID(), $document);
    }

    function moveTrigger($document)
    {
       return $this->getWorkflowForType($document->getDocumentTypeID(), $document);
    }

    function copyTrigger($document)
    {
       return $this->getWorkflowForType($document->getDocumentTypeID(), $document);
    }

    function getWorkflowForType($docTypeId, $document)
    {
        if (is_null($docTypeId)) {
            return null;
        }

        // Link to the workflows table to ensure disabled workflows aren't associated
        $query = 'SELECT `workflow_id` FROM ' . KTUtil::getTableName('type_workflow_map') .' m';
        $query .= ' LEFT JOIN workflows w ON w.id = m.workflow_id
            WHERE document_type_id = ? AND w.enabled = 1';

        $params = array($docTypeId);
        $res = DBUtil::getOneResultKey(array($query, $params), 'workflow_id');

        if (PEAR::isError($res) || (is_null($res))) {
            return KTWorkflowUtil::getWorkflowForDocument($document); // don't remove if type changed out.
        }

        return KTWorkflow::get($res);
    }

}

class WorkflowTypeAllocationDispatcher extends KTAdminDispatcher {

    var $bAutomaticTransaction = true;
    var $sSection = 'administration';

    function check()
    {
        $res = parent::check();
        if (!$res) {
            return false;
        }

        $query = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $query .= ' WHERE event_ns = ?';
        $params = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($query, $params), 'selection_ns');

        if ($res != 'ktstandard.triggers.workflowassociation.documenttype.handler') {
            return false;
        }

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name'=> _kt('Workflow Allocation by Document Types'));

        return true;
    }

    function do_main()
    {
        $query = 'SELECT document_type_id, workflow_id FROM ' . KTUtil::getTableName('type_workflow_map');
        $params = array();
        $res = DBUtil::getResultArray(array($query, $params));
        $workflows = KTWorkflow::getList('start_state_id IS NOT NULL AND enabled = 1');
        $types = DocumentType::getList();

        $typeMap = array();
        if (PEAR::isError($res)) {
            $this->oPage->addError(_kt('Failed to get type mapping: ') . $res->getMessage());
        }
        else {
            foreach ($res as $row) {
                $typeMap[$row['document_type_id']] = $row['workflow_id'];
            }
        }

        $template =& $this->oValidator->validateTemplate('ktstandard/workflow/type_allocation');
        $template->setData(array(
            'context' => $this,
            'types_mapping' => $typeMap,
            'types' => $types,
            'workflows' => $workflows,
        ));

        return $template->render();
    }

    function isActiveWorkflow($type, $workflow, $typeMap)
    {
        if (!array_key_exists($type->getId(), $typeMap)) {
            return false;
        }
        else {
            return $typeMap[$type->getId()] == $workflow->getId();
        }
    }

    function do_update()
    {
        $typeMap = (array) KTUtil::arrayGet($_REQUEST, 'fDocumentTypeAssignment');

        //$workflows = KTWorkflow::getList();
        $types = DocumentType::getList();

        $query = 'DELETE FROM ' . KTUtil::getTableName('type_workflow_map');
        $params = array();
        DBUtil::runQuery(array($query, $params));

        $options = array('noid' => true);
        $table = KTUtil::getTableName('type_workflow_map');
        foreach ($types as $type) {
            $typeId = $type->getId();
            $workflowId = $typeMap[$typeId];
            if (empty($workflowId)) {
                $workflowId = null;
            }

            $res = DBUtil::autoInsert($table, array(
                'document_type_id' => $typeId,
                'workflow_id' => $workflowId,
            ), $options);
        }

        $this->successRedirectToMain(_kt('Type mapping updated.'));
    }

    public function handleOutput($output)
    {
        print $output;
    }

}

$pluginRegistry =& KTPluginRegistry::getSingleton();
$pluginRegistry->registerPlugin(
                            'KTDocTypeWorkflowAssociationPlugin',
                            'ktstandard.workflowassociation.documenttype.plugin', __FILE__
);

?>

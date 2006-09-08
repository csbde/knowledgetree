<?php

/**
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
 */

require_once(KT_LIB_DIR . "/actions/documentviewlet.inc.php");
require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");

// {{{ KTDocumentDetailsAction 
class KTWorkflowViewlet extends KTDocumentViewlet {
    var $sName = 'ktcore.viewlets.document.workflow';

    function display_viewlet() {
        $oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oKTTemplating->loadTemplate("ktcore/document/viewlets/workflow");
        if (is_null($oTemplate)) { return ""; }
        
        $oWorkflowState = KTWorkflowState::get($this->oDocument->getWorkflowStateId());
        if (PEAR::isError($oWorkflowState)) {
            return "";
        }
        
        $aDisplayTransitions = array();
        $aTransitions = KTWorkflowUtil::getTransitionsForDocumentUser($this->oDocument, $this->oUser);
        if (empty($aTransitions)) {
            return "";
        }
        
        foreach ($aTransitions as $oTransition) {
            $aDisplayTransitions[] = array(
                'url' => KTUtil::ktLink('action.php', 'ktcore.actions.document.workflow', array("fDocumentId" => $this->oDocument->getId(), "action" => "quicktransition", "fTransitionId" => $oTransition->getId())),
                'name' => $oTransition->getName(),
            );
        }
        
        $oTemplate->setData(array(
            'context' => $this,
            'transitions' => $aDisplayTransitions,
            'state_name' => $oWorkflowState->getName(),
        ));        
        return $oTemplate->render();
    }
}
// }}}



?>

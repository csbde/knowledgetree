<?php

require_once("../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

$sectionName = "Manage Documents";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

class DocumentWorkflowDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate("ktcore/workflow/documentWorkflow");
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);

        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($oDocument);
        $oWorkflowState = KTWorkflowUtil::getWorkflowStateForDocument($oDocument);

        $oUser =& User::get($_SESSION['userID']);
        $aTransitions = KTWorkflowUtil::getTransitionsForDocumentUser($oDocument, $oUser);
        $aWorkflows = KTWorkflow::getList();

        $aTemplateData = array(
            'oDocument' => $oDocument,
            'oWorkflow' => $oWorkflow,
            'oState' => $oWorkflowState,
            'aTransitions' => $aTransitions,
            'aWorkflows' => $aWorkflows,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_startWorkflow() {
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::startWorkflowOnDocument($oWorkflow, $oDocument);
        $this->successRedirectToMain('Workflow started',
                array('fDocumentId' => $oDocument->getId()));
        exit(0);
    }

    function do_performTransition() {
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);
        $oTransition =& $this->oValidator->validateWorkflowTransition($_REQUEST['fTransitionId']);
        $sComments =& $this->oValidator->notEmpty($_REQUEST['fComments']);
        $oUser =& User::get($_SESSION['userID']);
        $res = KTWorkflowUtil::performTransitionOnDocument($oTransition, $oDocument, $oUser, $sComments);
        $this->successRedirectToMain('Transition performed',
                array('fDocumentId' => $oDocument->getId()));
    }
}

$oDispatcher = new DocumentWorkflowDispatcher;
$oDispatcher->dispatch();

?>

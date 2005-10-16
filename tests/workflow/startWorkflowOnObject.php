<?php

require_once('../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

$oDocument =& Document::get(4);
$oWorkflow =& KTWorkflow::get(1);

$res = KTWorkflowUtil::startWorkflowOnDocument($oWorkflow, $oDocument);
if (PEAR::isError($res)) {
    var_dump($res);
}

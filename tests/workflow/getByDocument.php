<?php

require_once('../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');

$oDocument =& Document::get(4);

$res = KTWorkflow::getByDocument($oDocument);
if (PEAR::isError($res)) {
    print "FAILED\n";
    var_dump($res);
}
$iWorkflowId = $res->getId();
if ($iWorkflowId != 1) {
    print "FAILED\n";
    print $iWorkflowId;
}

<?php
/**
* Document collaboration business logic - contains business logic to set up
* document approval process
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("collaborationUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
			
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(getDocumentRoutingPage(null, 1));
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=1");
	$main->render();
}

?>

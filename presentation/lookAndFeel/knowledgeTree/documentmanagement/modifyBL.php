<?php
/**
* Business logic data used to modify documents (will use modifyUI.inc)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 24 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");						
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyUI.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	$aDocumentDataArray;
	settype($aDocumentDataArray, "array");
	
	$oDocument = & Document::get($fDocumentID);	
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(renderPage($oDocument));
	$main->setCentralPayload($oPatternCustom);
	$main->setHasRequiredFields(true);	
	$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID()));
	$main->render();	
}

?>

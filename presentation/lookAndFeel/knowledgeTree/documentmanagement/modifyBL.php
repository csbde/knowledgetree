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
	
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");						
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyUI.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$aDocumentDataArray;
	settype($aDocumentDataArray, "array");
	
	$oDocument = & Document::get($fDocumentID);	
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(renderPage($oDocument));
	$main->setCentralPayload($oPatternCustom);
	$main->setHasRequiredFields(true);	
	$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "&fFireSubscription=1"));
	$main->setHasRequiredFields(true);
	$main->render();	
}

?>

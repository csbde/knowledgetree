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
	$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID()));
	$main->render();
		
	/*$oDocument = & Document::get($fDocumentID);
	echo $sToRender = "<html><head></head><body><form method=\"POST\" action=\"../store.php\" >\n";
	echo ;
	echo "<input type=submit value=\"Submit\" /></form></body></html>";*.
	/*if (isset($fDocumentID)) {	
		require_once("$default->owl_fs_root/lib/security/permission.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
		require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
		
		if (Permission::userHasDocumentReadPermission($fDocumentID)) {		
			require_once("$default->owl_fs_root/lib/security/permission.inc");
			require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
			require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");						
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyUI.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
			require_once("$default->owl_fs_root/presentation/Html.inc");
		
			$oDocument = & Document::get($fDocumentID);
			
			$aDocumentDataArray;
			settype($aDocumentDataArray, "array");
			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oDocument));
			$main->setCentralPayload($oPatternCustom);
			$main->render();
		} else {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
		
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("<p class=\"errorText\">Either you do not have permission to view this document,<br>" .
								"or the document you have chosen no longer exists on the file sytem.</p>\n");
		$main->setCentralPayload($oPatternCustom);
		$main->render();
		}
	} else {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
		
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("<p class=\"errorText\">You have not chosen a document to view</p>\n");
		$main->setCentralPayload($oPatternCustom);
		$main->render();			
	}*/
}

?>

<?php
/**
* documentViewUI.php
* Contains the business logic required to build the document view page.
* Will use documentViewUI.php for HTML
*
* Variables expected:
*			o $fDocumentID		Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 21 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/


require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {	
	if (isset($fDocumentID)) {	
		require_once("$default->owl_fs_root/lib/security/permission.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/DocumentTransaction.inc");
		require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
		
		if (isset($fForDownload)) {
			if (Permission::userHasDocumentReadPermission($fDocumentID)) {
				$oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document downloaded", DOWNLOAD);
				$oDocumentTransaction->create();
				PhysicalDocumentManager::downloadPhysicalDocument($fDocumentID);
				//redirect($_SERVER["PHP_SELF"] . "?fDocumentID=" . $fDocumentID);
			}
			else {
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
			
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml("<p class=\"errorText\">Either you do not have permission to view this document,<br>" .
									"or the document you have chosen no longer exists on the file sytem.</p>\n");
				$main->setCentralPayload($oPatternCustom);
				$main->render();
			}
		}
		
		if (Permission::userHasDocumentWritePermission($fDocumentID)) {		
			require_once("$default->owl_fs_root/lib/security/permission.inc");
			require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
			require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternListFromQuery.inc");	
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
			require_once("$default->owl_fs_root/presentation/Html.inc");
		
			$oDocument = & Document::get($fDocumentID);	
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getEditPage($oDocument));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction("$default->owl_root_url/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
			$main->render();
		} else if (Permission::userHasDocumentReadPermission($fDocumentID)) {
			require_once("$default->owl_fs_root/lib/security/permission.inc");
			require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
			require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternListFromQuery.inc");	
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
			require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
			require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
			require_once("$default->owl_fs_root/presentation/Html.inc");			
			
			$oDocument = & Document::get($fDocumentID);	
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getViewPage($oDocument));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction("$default->owl_root_url/control.php?action=modifyDocument&fDocumentID=" . $oDocument->getID());
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
	}
}

?>

<?php
/**
* Business Logic to add a new document to the 
* database.  Will use addDocumentUI.inc for presentation
*
* Expected form variable:
*	o $fFolderID - primary key of folder user is currently browsing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/lib/DocumentManagement/Document.inc");
	require_once("$default->owl_fs_root/lib/DocumentManagement/PhysicalDocumentManager.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	require_once("addDocumentUI.inc");

	if (isset($fFolderID)) {
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			//user has permission to add document to this folder
			if (isset($fForStore)) {
				//user wants to store a document
				//create the document in the database
				//var_dump($_FILES);
				$oDocument = & PhysicalDocumentManager::createDocumentFromUploadedFile($_FILES['fFile'], $fFolderID);
				if ($oDocument->create()) {
					//if the document was successfully created in the db, then store it on the file system
					if (PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $fFolderID, "None", $_FILES['fFile']['tmp_name'])) {
						redirect("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID());
					} else {
						require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
						$oDocument->delete();
						$oPatternCustom = & new PatternCustom();
						$oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
						$main->setCentralPayload($oPatternCustom);
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
						$main->setFormEncType("multipart/form-data");
						$main->setErrorMessage("An error occured while storing the document on the file system");
						$main->render();
					}
				} else {
					require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
					$main->setCentralPayload($oPatternCustom);
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
					$main->setFormEncType("multipart/form-data");
					$main->setErrorMessage("An error occured while storing the document in the database");
					$main->render();
				}
			} else {
				//we're still just browsing
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getBrowseAddPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
				$main->setFormEncType("multipart/form-data");
				$main->render();
			}
		} else {
			//user does not have write permission for this folder,
			//so don't display add button
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getBrowsePage($fFolderID));
			$main->setCentralPayload($oPatternCustom);			
			$main->setErrorMessage("You do not have permission to add a document to this folder");
			$main->render();
		}
	} else {
		//no folder id was set when coming to this page, 
		//so display an error message
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("<p class=\"errorText\">No folder to which a document can be added is currenlty selected</p>\n");
		$main->setCentralPayload($oPatternCustom);		
		$main->render();
	}
}


?>

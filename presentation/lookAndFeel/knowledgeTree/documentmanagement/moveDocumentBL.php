<?php


require_once("../../../../config/dmsDefaults.php");

require_once("$default->owl_fs_root/lib/security/permission.inc");

require_once("$default->owl_fs_root/lib/users/User.inc");

require_once("$default->owl_fs_root/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");

require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/moveDocumentUI.inc");
require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->owl_fs_root/presentation/Html.inc");

if (checkSession()) {
	
	if (isset($fDocumentID) && isset($fFolderID)) {		
		if (isset($fForMove)) {
			//we're trying to move a document
			$oDocument = & Document::get($fDocumentID);			
			$iOldFolderID = $oDocument->getFolderID();
			if (Permission::userHasDocumentWritePermission($fDocumentID) && Permission::userHasFolderWritePermission($fFolderID)) {
				//if the user has both document and folder write permissions				
				//get the old document path
				$sOldDocumentFileSystemPath = Folder::getFolderPath($iOldFolderID) . $oDocument->getFileName();
				//put the document in the new folder
				$oDocument->setFolderID($fFolderID);
				if ($oDocument->update(true)) {					
					//get the new document path
					$sNewDocumentFileSystemPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();
					//move the document on the file system
					if (PhysicalDocumentManager::move($sOldDocumentFileSystemPath, $sNewDocumentFileSystemPath)) {
						//redirect to the view path
						redirect("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=$fDocumentID");
					} else {
						require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
						//we couldn't move the document on the file system
						//so reset the database values
						$oDocument->setFolderID($iOldFolderID);
						$oDocument->update();						
						$oPatternCustom = & new PatternCustom();
						$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
						$main->setCentralPayload($oPatternCustom);   
						$main->setErrorMessage("Could not move document on file system");
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1");
						$main->render();
					}
				} else {
					require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
					//had a problem with the database					
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
					$main->setCentralPayload($oPatternCustom);   
					$main->setErrorMessage("Could not update document in database");
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
					$main->render();
				}
			} else {
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
				$main->setCentralPayload($oPatternCustom);   
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
				$main->setErrorMessage("You do not have rights to move this document");
				$main->render();
			}
			
		} else {		
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentID));
			$main->setCentralPayload($oPatternCustom);   
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForMove=1&fDocumentID=$fDocumentID&fFolderID=$fFolderID");
			$main->render();
		}
	} else {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No document/folder selected");
		$main->render();
	}
    
}
?>

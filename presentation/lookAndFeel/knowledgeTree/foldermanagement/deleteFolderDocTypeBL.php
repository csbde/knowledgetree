<?php
/**
* Business logic for removing a document type from a folder
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 27 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderDocTypeLink.inc");        
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("deleteFolderDocTypeUI.inc");
	
	$oPatternCustom = & new PatternCustom();
	
	if (Permission::userHasFolderWritePermission($fFolderID)) {
		//user has permission to delete
		if (isset($fFolderDocTypeID)) {
			//the required variables exist
            
			if (Document::documentIsAssignedDocTypeInFolder($fFolderID, $fFolderDocTypeID)) {
				//there is a document in the folder assigned this type, so
				//it may not be deleted
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("A document in this folder is currently assigned this type.  You may not delete it.");								
				$main->render();
            } else if (count(FolderDocTypeLink::getList("folder_id=$fFolderID")) == 1) {
                // there is only one document type mapped to this folder- not allowed to delete the last one
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("You may not delete the last document type for this folder.");								
				$main->render();
			} else {
				//go ahead and delete
				$oFolderDocTypeLink = FolderDocTypeLink::get($fFolderDocTypeID);
				if ($oFolderDocTypeLink->delete()) {
					redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID");
				} else {
					//there was a problem deleting from the database
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom->setHtml(getPage($fFolderID));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("An error was encountered while attempting to delete this link from the database");								
					$main->render();
				}
			}
		}
	} else {
		//user does not have permission to delete this document type
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom->setHtml(getPage($fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("You do not have permission to remove this document type from this folder");		
		$main->render();
	}
}
?>

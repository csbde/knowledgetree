<?php
/**
* Business logic for assigning a new document type to a folder
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 27 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderDocTypeLink.inc");        
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("addFolderDocTypeUI.inc");
	
	$oPatternCustom = & new PatternCustom();
	
	if (Permission::userHasFolderWritePermission($fFolderID)) {
		if (isset($fForAdd)) {
			//user has selected a document type
			if (Folder::folderIsLinkedToDocType($fFolderID, $fDocumentTypeID)) {
				//if the folder is already assigned this document type
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("The folder has already been assigned this document type");				
				$main->setFormAction("addFolderDocTypeBL.php?fForAdd=1&fFolderID=$fFolderID");
                $main->render();
				
			} else {
				$oFolderDocType = & new FolderDocTypeLink($fFolderID,$fDocumentTypeID);
				if ($oFolderDocType->create()) {
					redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID");					
				} else {
					//error creating document in the db
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("A database error occured while attempting to assig the document type to the folder");					
					$main->setFormAction("addFolderDocTypeBL.php?fForAdd=1&fFolderID=$fFolderID");
					$main->render();
				}
			}
		} else {
			//show the user the page to assign document types
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom->setHtml(getPage($fFolderID, $fDocumentTypeID));
			$main->setCentralPayload($oPatternCustom);			
			$main->setFormAction("addFolderDocTypeBL.php?fForAdd=1&fFolderID=$fFolderID");
            $main->render();
		}
	} else {
		//user does not have write permission for this folder
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("You do not have permission to assign a document type to this folder");						
		$main->render();
	}
}

?>

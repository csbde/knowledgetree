<?php
/**
* Business logic used to edit folder properties
*
* Expected form variables:
* o $fFolderID - primary key of folder user is currently browsing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 2 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("editUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    if (isset($fFolderID)) {
        //if the user can edit the folder
        if (Permission::userHasFolderWritePermission($fFolderID)) {
			if (isset($fForUpdate)) {
				//user is updating folder data
				$oFolder = Folder::get($fFolderID);
				$oFolder->setDescription($fDescription);
				if (isset($fIsPublic)) {
					$oFolder->setIsPublic(true);
				} else {
					$oFolder->setIsPublic(false);
				}
				$bSuccessfulUpdate = false;
				if (isset($fFolderName) && strcmp($oFolder->getName(), $fFolderName) != 0) {					
					//folder name has changed, update the full_path
					$sOldName = $oFolder->getName();
					$sOldPath = $default->documentRoot . "/" . $oFolder->getFullPath() . "/" . $oFolder->getName();
					$oFolder->setName($fFolderName);					
					if ($oFolder->update(true)) {
						$bSuccessfulUpdate = true;
						if (!PhysicalFolderManagement::renameFolder($sOldPath, $default->documentRoot . "/" . $oFolder->getFullPath() . "/" . $oFolder->getName())) {
							//reverse the database changes if the physical rename failed
							$oFolder->setName($sOldName);
							$oFolder->update(true);
							$bSuccessfulUpdate = false;
						}						
					}
				} else {
					$bSuccessfulUpdate = $oFolder->update();
				}				
				if ($bSuccessfulUpdate) {
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($fFolderID));
					$main->setCentralPayload($oPatternCustom);
					$main->setHasRequiredFields(true);
                    $main->setErrorMessage("Folder successfully updated");
					$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
					$main->render();
				} else {
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($fFolderID));
					$main->setErrorMessage("An error occured while updating this folder");
					$main->setCentralPayload($oPatternCustom);
					$main->setHasRequiredFields(true);
					$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
					$main->render();					
				}
			} else if (isset($fCollaborationEdit)) {
                //user attempted to edit the folder collaboration process but could not because there is
                //a document currently in this process
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getPage($fFolderID, true));
                $main->setErrorMessage("You cannot edit this folder collaboration process as a document is currently undergoing this collaboration process");
                $main->setCentralPayload($oPatternCustom);
                $main->setHasRequiredFields(true);
                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
                $main->render();
            } else if (isset($fCollaborationDelete)) {
                //user attempted to delete the folder collaboration process but could not because there is
                //a document currently in this process
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getPage($fFolderID, true));
                $main->setErrorMessage("You cannot delete this folder collaboration process as a document is currently undergoing this collaboration process");
                $main->setCentralPayload($oPatternCustom);
                $main->setHasRequiredFields(true);
                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
                $main->render();
            } else {
                $oPatternCustom = & new PatternCustom();
                // does this folder have a document in it that has started collaboration?
                $bCollaboration = Folder::hasDocumentInCollaboration($fFolderID);
                    
                $oPatternCustom->setHtml(getPage($fFolderID, $bCollaboration));
                $main->setCentralPayload($oPatternCustom);
                $main->setHasRequiredFields(true);
                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
                $main->render();
            }
        } else {
            //user does not have write permission for this folder,
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml("");
            $main->setCentralPayload($oPatternCustom);
            $main->setErrorMessage("You do not have permission to edit this folder");
            $main->render();
        }
    } else {
        //else display an error message
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("");
        $main->setCentralPayload($oPatternCustom);
        $main->setErrorMessage("No folder currently selected");
        $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
        $main->render();
    }
}

?>

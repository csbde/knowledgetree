<?php
/**
 * $Id$
 *
 * Business logic used to edit folder properties
 *
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently browsing
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 2 February 2003
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
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
    	$oFolder = Folder::get($fFolderID);
    	if ($oFolder) {
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
						$oPatternCustom->setHtml(getStatusPage($fFolderID, "Folder successfully updated"));
			            $main->setDHTMLScrolling(false);
			            $main->setOnLoadJavaScript("switchDiv('folderData', 'folder')");
						
						$main->setCentralPayload($oPatternCustom);
						$main->setHasRequiredFields(true);
						$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
						$main->render();
					} else {
						$oPatternCustom = & new PatternCustom();
			            $main->setDHTMLScrolling(false);
			            $main->setOnLoadJavaScript("switchDiv('folderData', 'folder')");
						
						$oPatternCustom->setHtml(getStatusPage($fFolderID, "An error occurred while updating this folder"));
						$main->setCentralPayload($oPatternCustom);
						$main->setHasRequiredFields(true);
						$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
						$main->render();					
					}
				} else if (isset($fCollaborationEdit)) {
	                //user attempted to edit the folder collaboration process but could not because there is
	                //a document currently in this process
	                $oPatternCustom = & new PatternCustom();
		            $main->setDHTMLScrolling(false);
		            $main->setOnLoadJavaScript("switchDiv('folderRouting', 'folder')");
	                
	                $oPatternCustom->setHtml(getPage($fFolderID, "You cannot edit this folder collaboration process as a document is currently undergoing this collaboration process", true));
	                $main->setCentralPayload($oPatternCustom);
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	                $main->render();
	            } else if (isset($fCollaborationDelete)) {
	                //user attempted to delete the folder collaboration process but could not because there is
	                //a document currently in this process
	                $oPatternCustom = & new PatternCustom();
		            $main->setDHTMLScrolling(false);
		            $main->setOnLoadJavaScript("switchDiv('folderRouting', 'folder')");
	                
	                $oPatternCustom->setHtml(getPage($fFolderID, "You cannot delete this folder collaboration process as a document is currently undergoing this collaboration process", true));
	                $main->setCentralPayload($oPatternCustom);
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	                $main->render();
	            } else {
	                $oPatternCustom = & new PatternCustom();
	                // does this folder have a document in it that has started collaboration?
	                $bCollaboration = Folder::hasDocumentInCollaboration($fFolderID);
		            $main->setDHTMLScrolling(false);
		            $main->setOnLoadJavaScript("switchDiv('" . (isset($fShowSection) ? $fShowSection : "folderData") . "', 'folder')");
	                    
	                $oPatternCustom->setHtml(getPage($fFolderID, "", $bCollaboration));
	                $main->setCentralPayload($oPatternCustom);
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	                $main->render();
	            }
	        } else {
	            //user does not have write permission for this folder,
	            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	            $oPatternCustom = & new PatternCustom();
	            $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	            $main->setCentralPayload($oPatternCustom);
	            $main->setErrorMessage("You do not have permission to edit this folder");
	            $main->render();
	        }
    	} else {
    		// folder doesn't exist
	        $oPatternCustom = & new PatternCustom();
	        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	        $main->setCentralPayload($oPatternCustom);
	        $main->setErrorMessage("The folder you're trying to modify does not exist in the DMS");
	        $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	        $main->render();
    	}
    } else {
        //else display an error message
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        $main->setCentralPayload($oPatternCustom);
        $main->setErrorMessage("No folder currently selected");
        $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
        $main->render();
    }
}
?>
<?php

/**
 * $Id$
 *
 * Presentation information used for folder data editing.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("modifyFolderDataUI.inc");
        
	if (isset($fFolderID)) {	
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
				// redirect to edit folder page
				controllerRedirect("editFolder", "fFolderID=$fFolderID");
				exit;
			} else {
			    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getFolderData($fFolderID, "An error occurred while updating this folder"));
				$main->setHasRequiredFields(true);
				$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
			}
		} else {
		    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			// display form
			$oPatternCustom->setHtml(getFolderData($fFolderID));
			$main->setHasRequiredFields(true);
			$main->setFormAction($_SERVER['PHP_SELF']);
		}
	}
	$main->setCentralPayload($oPatternCustom);						
	$main->render();	
}
?>
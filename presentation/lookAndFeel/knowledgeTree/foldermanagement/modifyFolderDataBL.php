<?php
/**
 * $Id$
 *
 * Presentation information used for folder data editing.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
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
				$oPatternCustom->setHtml(getFolderData($fFolderID, _("An error occurred while updating this folder")));
				$main->setHasRequiredFields(true);
				$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
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

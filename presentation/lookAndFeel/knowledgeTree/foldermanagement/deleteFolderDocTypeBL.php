<?php
/**
 * $Id$
 *
 * Business logic for removing a document type from a folder.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fFolderDocTypeID', 'fFolderID');

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
	
	$oFolder = Folder::get($fFolderID);
	if (Permission::userHasFolderWritePermission($oFolder)) {
		//user has permission to delete
		if (isset($fFolderDocTypeID)) {
			//the required variables exist
            
			if (Document::documentIsAssignedDocTypeInFolder($fFolderID, $fFolderDocTypeID)) {
				//there is a document in the folder assigned this type, so
				//it may not be deleted
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage(_("A document in this folder is currently assigned this type.  You may not delete it."));
				$main->render();
            } else if (count(FolderDocTypeLink::getList(array("folder_id = ?", $fFolderID))) == 1) {/*ok*/
                // there is only one document type mapped to this folder- not allowed to delete the last one
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage(_("You may not delete the last document type for this folder."));
				$main->render();
			} else {
				//go ahead and delete
				$oFolderDocTypeLink = FolderDocTypeLink::get($fFolderDocTypeID);
				if ($oFolderDocTypeLink->delete()) {
					controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=documentTypes");
				} else {
					//there was a problem deleting from the database
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom->setHtml(getPage($fFolderID));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage(_("An error was encountered while attempting to delete this link from the database"));
					$main->render();
				}
			}
		}
	} else {
		//user does not have permission to delete this document type
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom->setHtml(getPage($fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage(_("You do not have permission to remove this document type from this folder"));
		$main->render();
	}
}
?>

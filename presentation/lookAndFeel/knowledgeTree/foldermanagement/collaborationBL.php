<?php
/**
 * $Id$
 *
 * Document collaboration business logic - contains business logic to set up
 * document approval process
 *
 * Expected form variables:
 *	o fFolderCollaborationID - 
 *	o fForAdd - 
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

KTUtil::extractGPC('fFolderCollaborationID', 'fFolderID', 'fForUpdate', 'fGroupID', 'fRoleID', 'fSequenceNumber', 'fUserID');

if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("collaborationUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	if (isset($fForUpdate)) {		
		//we are updating
		$oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);
		$oFolderCollaboration->setGroupID($fGroupID);
		$oFolderCollaboration->setUserID($fUserID);
		if ($fRoleID != -1) {
			$oFolderCollaboration->setRoleID($fRoleID);
		} else {
			$oFolderCollaboration->setRoleID(null);
		}
		$oFolderCollaboration->setSequenceNumber($fSequenceNumber);
		$oFolderCollaboration->update();
		controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderRouting");
	} else {		
		$oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
		if ($oFolderCollaboration->hasDocumentInProcess()) {
			//you cannot alter collaboration process at the folder level if a document is currently
			//going through the process
			redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID&fCollaborationEdit=0");			
		} else {
			//we are editing an existing entry
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getEditPage($fFolderCollaborationID, $fFolderID, $fGroupID, $fUserID, $fRoleID, $fSequenceNumber));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForUpdate=1");
			$main->setHasRequiredFields(true);
			$main->render();
		}
	}	
}
?>

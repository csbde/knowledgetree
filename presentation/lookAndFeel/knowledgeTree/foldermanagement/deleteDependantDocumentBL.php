<?php
/**
 * $Id$
 *
 * Business logic for deleting a dependant document
 *
 * Expected variables:
 *	$fFolderCollaborationID: primary key of folder collaboration to check
 *	$fFolderID: folder we are currently editing
 *	$fDependantDocumentTemplateID: primary key of dependant document to be deleted
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

if (checkSession()) {	
	if (isset($fFolderID) && isset($fFolderCollaborationID)) {
		require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
		require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
		require_once("$default->fileSystemRoot/lib/users/User.inc");
		require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentTemplate.inc");
		require_once("$default->fileSystemRoot/presentation/Html.inc");		
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
		require_once("deleteDependantDocumentUI.inc");	
	
	 	$oFolder = Folder::get($fFolderID);
		if (Permission::userHasFolderWritePermission($oFolder)) {
			//user has permission to alter folder contents
			if (isset($fForDelete)) {
				$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
				if ($oDependantDocumentTemplate->delete()) {
					controllerRedirect("viewDependantDocument", "fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID");				
				} else {
					$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
					$oUser = User::get($oDependantDocumentTemplate->getDefaultUserId());
					$oTemplateDocument = Document::get($oDependantDocumentTemplate->getTemplateDocumentID());
				
					$oPatternCustom = & new PatternCustom();				
					$oPatternCustom->setHtml(getPage($fFolderID, $fFolderCollaborationID, $oDependantDocumentTemplate->getDocumentTitle(), $oUser->getName(), (!($oTemplateDocument->getName() === false)) ? $oTemplateDocument->getName() : ""));
	    			$main->setCentralPayload($oPatternCustom);
	    	    	$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fDependantDocumentTemplateID=$fDependantDocumentTemplateID&fForDelete=1");
	    	    	$main->setErrorMessage(_("An error occured while attempting to delete the dependant document"));
	    			$main->render();						
				}
				
			} else {				
				include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				
				$oDependantDocumentTemplate = DependantDocumentTemplate::get($fDependantDocumentTemplateID);
				$oUser = User::get($oDependantDocumentTemplate->getDefaultUserId());
				$oTemplateDocument = Document::get($oDependantDocumentTemplate->getTemplateDocumentID());
				
				$oPatternCustom = & new PatternCustom();				
				$oPatternCustom->setHtml(getPage($fFolderID, $fFolderCollaborationID, $oDependantDocumentTemplate->getDocumentTitle(), $oUser->getName(), (!($oTemplateDocument->getName() === false)) ? $oTemplateDocument->getName() : ""));
	    		$main->setCentralPayload($oPatternCustom);
	    	    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fDependantDocumentTemplateID=$fDependantDocumentTemplateID&fForDelete=1");	    	        		
	    		$main->render();	
				
			}

		
		}
		
	}
}

?>
		

<?php
/**
 * $Id$
 *
 * Business logic for assigning a new document type to a folder.
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
					controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=documentTypes");					
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

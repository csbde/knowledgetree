<?php
/**
 * $Id$
 * Business logic for adding folder access
 * addFolderAccessUI.inc for presentation information
 *
 * Expected form variables:
 *	o $fFolderID - primary key of folder user is currently editing
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

KTUtil::extractGPC('fCanRead', 'fCanWrite', 'fFolderID', 'fForStore', 'fGroupID');

include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
include_once("$default->fileSystemRoot/lib/security/Permission.inc");
include_once("$default->fileSystemRoot/lib/users/User.inc");
include_once("$default->fileSystemRoot/lib/groups/GroupFolderLink.inc");
include_once("$default->fileSystemRoot/presentation/Html.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");			
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
include_once("groupFolderLinkUI.inc");                        

if (checkSession()) {
	if (isset($fFolderID)) {
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("");
        $oFolder = Folder::get($fFolderID);
		// if a folder has been selected
		if (Permission::userHasFolderWritePermission($oFolder)) {
			// can only add access if the user has folder write permission
			if (isset($fForStore)) {
				// attempt to create the new folder access entry				
				$oGroupFolderLink = & new GroupFolderLink($fFolderID, $fGroupID, $fCanRead, $fCanWrite);
                // check if exists for the fFolderID, fGroupID combination
                if (!$oGroupFolderLink->exists()) {
                    if ($oGroupFolderLink->create()) {
                        // on successful creation, redirect to the folder edit page
                        controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderPermissions");                        
                    } else {
                        //otherwise display an error message
                        $sErrorMessage = _("The folder access entry could not be created in the database");
                        $oPatternCustom->setHtml(getPage($fFolderID));
                    }
                } else {
                    $sErrorMessage = _("A folder access entry for the selected folder and group already exists.");
                    $oPatternCustom->setHtml(renderErrorPage($sErrorMessage, $fFolderID));
                }
			} else {
				// display the browse page
				$oPatternCustom->setHtml(getAddPage($fFolderID));
			}
		}
	} else {
		//display an error message
        $sErrorMessage = _("No folder currently selected");
	}
    
    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
    $main->setHasRequiredFields(true);
    $main->render();    
}
?>

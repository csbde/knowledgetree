<?php
/**
 * $Id$
 *
 * Business logic for deleting a folder access entry
 * addFolderAccessUI.inc for presentation information
 *
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently editing 
 * o $fGroupFolderLinkID - primary key of group folder link user to delete
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
include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
include_once("$default->fileSystemRoot/lib/security/Permission.inc");
include_once("$default->fileSystemRoot/lib/groups/GroupFolderLink.inc");
include_once("$default->fileSystemRoot/lib/groups/Group.inc");
include_once("$default->fileSystemRoot/lib/roles/Role.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
include_once("$default->fileSystemRoot/presentation/Html.inc");
include_once("groupFolderLinkUI.inc");

if (checkSession()) {
    if (isset($fFolderID) && isset($fGroupFolderLinkID)) {
        // if a folder has been selected
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("");    
           
		$oFolder = Folder::get($fFolderID);
        if (Permission::userHasFolderWritePermission($oFolder)) {
            // can only delete group folder links if the user has folder write permission
            if (isset($fForDelete)) {
                $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                if ($oGroupFolderLink->delete()) {
                    // on successful deletion, redirect to the folder edit page
                    controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderPermissions");
                } else {
                    // otherwise display an error message
                    $sErrorMessage = _("The folder access entry could not be deleted from the database");
                    $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                    $oPatternCustom->setHtml(getPage($oGroupFolderLink));
                }
            } else {
                $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                $oPatternCustom->setHtml(getDeletePage($oGroupFolderLink, $fFolderID));
            }
        } else {
            // display an error message
            $sErrorMessage = _("You don't have permission to delete this folder access entry.");
        }
    } else {
        $sErrorMessage = _("No folder currently selected");
    }
    
    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    if (isset($sErrorMessage)) {
        $main->setErrorMessage($sErrorMessage);
    }
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fGroupFolderLinkID=$fGroupFolderLinkID&fForDelete=1");
    $main->setHasRequiredFields(true);
    $main->render();    
}
?>

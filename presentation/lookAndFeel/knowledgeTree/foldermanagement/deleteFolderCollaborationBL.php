<?php
/**
 * $Id$
 *
 * Business logic for delete a new step from the folder collaboration process
 * Will use deleteFolderCollaborationUI.inc for presentation information
 *
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently editing
 * o $fFolderCollaborationID - primary key of folder collaboration to delete
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

KTUtil::extractGPC('fFolderCollaborationID', 'fFolderID', 'fForDelete');

if (checkSession()) {
    if (isset($fFolderID) && isset($fFolderCollaborationID)) {
        //if a folder has been selected
        include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
        include_once("$default->fileSystemRoot/lib/security/Permission.inc");
        include_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
        include_once("$default->fileSystemRoot/lib/groups/Group.inc");
        include_once("$default->fileSystemRoot/lib/roles/Role.inc");
        require_once("$default->fileSystemRoot/presentation/Html.inc");
        
        $oFolder = Folder::get($fFolderID);
        if (Permission::userHasFolderWritePermission($oFolder)) {
            //can only delete new collaboration steps if the user has folder write permission
            if (isset($fForDelete)) {
                $oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);
                if ($oFolderCollaboration->delete()) {
                    //on successful deletion, redirect to the folder edit page
                    include_once("$default->fileSystemRoot/presentation/Html.inc");
                    controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderRouting");
                } else {
                    //otherwise display an error message
                    include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
                    include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

                    include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
                    include_once("$default->fileSystemRoot/presentation/Html.inc");
                    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    include_once("deleteFolderCollaborationUI.inc");

                    $oPatternCustom = & new PatternCustom();
                    $oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
                    $oPatternCustom->setHtml(getPage($oFolderCollaboration->getFolderID(), $oFolderCollaboration->getGroupID(), $oFolderCollaboration->getRoleID(), $oFolderCollaboration->getSequenceNumber()));
                    $main->setErrorMessage(_("The folder collaboration entry could not be deleted from the database"));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForDelete=1");
                    $main->setHasRequiredFields(true);
                    $main->render();
                }
            } else {
                $oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);
                if ($oFolderCollaboration->hasDocumentInProcess()) {
                    //can't delete a step in the folder collaboration process if there is a document
                    //currently undergoing the process
                    controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderRouting&fCollaborationDelete=0");
                } else {
                    //display the browse page
                    include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
                    include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
                    include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
                    include_once("$default->fileSystemRoot/presentation/Html.inc");
                    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                    include_once("deleteFolderCollaborationUI.inc");

                    $oPatternCustom = & new PatternCustom();
                    $oFolderCollaboration = FolderCollaboration::get($fFolderCollaborationID);
                    $oPatternCustom->setHtml(getPage($oFolderCollaboration->getFolderID(), $oFolderCollaboration->getGroupID(), $oFolderCollaboration->getRoleID(), $oFolderCollaboration->getSequenceNumber()));
                    $main->setCentralPayload($oPatternCustom);
                    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForDelete=1");
                    $main->setHasRequiredFields(true);
                    $main->render();
                }
            }
        } else {
            //display an error message
            include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
            include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
            include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
            include_once("$default->fileSystemRoot/presentation/Html.inc");
            include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            include_once("deleteFolderCollaborationUI.inc");

            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml("");
            $main->setCentralPayload($oPatternCustom);
            $main->setErrorMessage(_("No folder currently selected"));
            $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForDelete=1");
            $main->setHasRequiredFields(true);
            $main->render();
        }
    }
}
?>

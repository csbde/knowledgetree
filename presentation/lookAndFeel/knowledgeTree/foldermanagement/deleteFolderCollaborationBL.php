<?php
/**
* Business logic for delete a new step from the folder collaboration process
* Will use deleteFolderCollaborationUI.inc for presentation information
*
* Expected form variables:
* o $fFolderID - primary key of folder user is currently editing
* o $fFolderCollaborationID - primary key of folder collaboration to delete
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 6 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/
require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    if (isset($fFolderID) && isset($fFolderCollaborationID)) {
        //if a folder has been selected
        include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
        include_once("$default->fileSystemRoot/lib/security/permission.inc");
        include_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
        include_once("$default->fileSystemRoot/lib/groups/Group.inc");
        include_once("$default->fileSystemRoot/lib/roles/Role.inc");
        require_once("$default->fileSystemRoot/presentation/Html.inc");
        if (Permission::userHasFolderWritePermission($fFolderID)) {
            //can only delete new collaboration steps if the user has folder write permission
            if (isset($fForDelete)) {
                $oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);
                if ($oFolderCollaboration->delete()) {
                    //on successful deletion, redirect to the folder edit page
                    include_once("$default->fileSystemRoot/presentation/Html.inc");
                    redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID");
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
                    $main->setErrorMessage("The folder collaboration entry could not be deleted from the database");
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
                    redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID&fCollaborationDelete=0");
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
            $main->setErrorMessage("No folder currently selected");
            $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fFolderCollaborationID=$fFolderCollaborationID&fForDelete=1");
            $main->setHasRequiredFields(true);
            $main->render();
        }
    }
}
?>

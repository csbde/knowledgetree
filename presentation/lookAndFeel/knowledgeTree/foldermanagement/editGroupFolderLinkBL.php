<?php
/**
 * $Id$
 *
 * Business logic for editing a folder access entry
 * groupFolderLinkUI.inc for presentation information
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently editing 
 * o $fGroupFolderLinkID - primary key of group folder link user to delete
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");
include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
include_once("$default->fileSystemRoot/lib/security/permission.inc");
include_once("$default->fileSystemRoot/lib/groups/GroupFolderLink.inc");
include_once("$default->fileSystemRoot/lib/groups/Group.inc");
include_once("$default->fileSystemRoot/lib/roles/Role.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
include_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
include_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
include_once("$default->fileSystemRoot/presentation/Html.inc");
include_once("groupFolderLinkUI.inc");

if (checkSession()) {
    if (isset($fFolderID) && isset($fGroupFolderLinkID)) {
        // if a folder has been selected
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml("");        
        if (Permission::userHasFolderWritePermission($fFolderID)) {
            // can only edit group folder links if the user has folder write permission
            if (isset($fForStore)) {
                $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                $oGroupFolderLink->setCanRead($fCanRead);
                $oGroupFolderLink->setCanWrite($fCanWrite);
                if ($oGroupFolderLink->update()) {
                    // on successful deletion, redirect to the folder edit page
                    redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID");
                } else {
                    // otherwise display an error message
                    $sErrorMessage = "The folder access entry could not be deleted from the database";
                    $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                    $oPatternCustom->setHtml(getEditPage($oGroupFolderLink, $fFolderID));
                }
            } else {
                $oGroupFolderLink = & GroupFolderLink::get($fGroupFolderLinkID);
                $oPatternCustom->setHtml(getEditPage($oGroupFolderLink, $fFolderID));
            }
        } else {
            // display an error message
            $sErrorMessage = "You don't have permission to delete this folder access entry.";
        }
    } else {
        $sErrorMessage = "No folder currently selected";
    }
    
    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    if (isset($sErrorMessage)) {
        $main->setErrorMessage($sErrorMessage);
    }
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fGroupFolderLinkID=$fGroupFolderLinkID&fForStore=1");
    $main->setHasRequiredFields(true);
    $main->render();    
}
?>

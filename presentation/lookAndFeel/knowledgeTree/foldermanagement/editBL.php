<?php
/**
 * $Id$
 *
 * Business logic used to edit folder properties
 *
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently browsing
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 2 February 2003
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
    require_once("editUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    

	$oPatternCustom = & new PatternCustom();
    if (isset($fFolderID)) {
    	$oFolder = Folder::get($fFolderID);
    	if ($oFolder) {
	        //if the user can edit the folder
	        if (Permission::userHasFolderWritePermission($fFolderID)) {
				if (isset($fCollaborationEdit)) {
	                //user attempted to edit the folder collaboration process but could not because there is
	                //a document currently in this process
	                $oPatternCustom->setHtml(getStatusPage($fFolderID, "You cannot edit this folder collaboration process as a document is currently undergoing this collaboration process"));
	                
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	            } else if (isset($fCollaborationDelete)) {
	                //user attempted to delete the folder collaboration process but could not because there is
	                //a document currently in this process
	                $oPatternCustom->setHtml(getStatusPage($fFolderID, "You cannot delete this folder collaboration process as a document is currently undergoing this collaboration process"));
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	            } else {
	                // does this folder have a document in it that has started collaboration?
	                $bCollaboration = Folder::hasDocumentInCollaboration($fFolderID);
		            $main->setDHTMLScrolling(false);
		            $main->setOnLoadJavaScript("switchDiv('" . (isset($fShowSection) ? $fShowSection : "folderData") . "', 'folder')");
	                    
	                $oPatternCustom->setHtml(getPage($fFolderID, "", $bCollaboration));
	                $main->setHasRequiredFields(true);
	                $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
	            }
	        } else {
	            //user does not have write permission for this folder,
	            $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	            $main->setErrorMessage("You do not have permission to edit this folder");
	        }
    	} else {
    		// folder doesn't exist
	        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
	        $main->setErrorMessage("The folder you're trying to modify does not exist in the DMS");
	        $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
    	}
    } else {
        //else display an error message
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        $main->setErrorMessage("No folder currently selected");
        $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID"));
    }
	$main->setCentralPayload($oPatternCustom);						
	$main->render();
}
?>
<?php
/**
 * $Id$
 *
 * Business logic for adding folder access
 * addFolderAccessUI.inc for presentation information
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * Expected form variables:
 *	o $fFolderID - primary key of folder user is currently editing
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */
require_once("../../../../config/dmsDefaults.php");
include_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
include_once("$default->fileSystemRoot/lib/security/permission.inc");
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
		// if a folder has been selected
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			// can only add access if the user has folder write permission
			if (isset($fForStore)) {
				// attempt to create the new folder access entry				
				$oGroupFolderLink = & new GroupFolderLink($fFolderID, $fGroupID, $fCanRead, $fCanWrite);
                // check if exists for the fFolderID, fGroupID combination
                if (!$oGroupFolderLink->exists()) {
                    if ($oGroupFolderLink->create()) {
                        // on successful creation, redirect to the folder edit page                        
                        redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID");
                    } else {
                        //otherwise display an error message
                        $sErrorMessage = "The folder access entry could not be created in the database";                        
                        $oPatternCustom->setHtml(getPage($fFolderID));
                    }
                } else {
                    $sErrorMessage = "A folder access entry for the selected folder and group already exists.";
                    $oPatternCustom->setHtml(renderErrorPage($sErrorMessage, $fFolderID));
                }
			} else {
				// display the browse page
				$oPatternCustom->setHtml(getAddPage($fFolderID));
			}
		}
	} else {
		//display an error message
        $sErrorMessage = "No folder currently selected";
	}
    
    include_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
    $main->setHasRequiredFields(true);
    $main->render();    
}
?>

<?php
/**
* Business logic for adding a new step in the folder collaboration process
* Will used addFolderCollaborationUI.inc for presentation information
*
* Expected form variables:
*	o $fFolderID - primary key of folder user is currently editing
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 6 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/
require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	if (isset($fFolderID)) {
		//if a folder has been selected
		include_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
		include_once("$default->owl_fs_root/lib/security/permission.inc");
        include_once("$default->owl_fs_root/lib/users/User.inc");
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			//can only create new collaboration steps if the user has folder write permission
			if (isset($fForStore)) {
				//attempt to create the new folder collaboration entry
				include_once("$default->owl_fs_root/lib/foldermanagement/FolderCollaboration.inc");
				$oFolderCollaboration = & new FolderCollaboration($fFolderID, $fGroupID, $fSequenceNumber, $fRoleID);
				if ($oFolderCollaboration->create()) {
					//on successful creation, redirect to the folder edit page
					include_once("$default->owl_fs_root/presentation/Html.inc");
					redirect("$default->owl_root_url/control.php?action=editFolder&fFolderID=$fFolderID");
				} else {
					//otherwise display an error message
					include_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");			
					include_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
					
					include_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
					include_once("$default->owl_fs_root/presentation/Html.inc");
					include_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
					include_once("addFolderCollaborationUI.inc");
					
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($fFolderID, $fGroupID, $fRoleID, $fSequenceNumber));
					$main->setErrorMessage("The folder collaboration entry could not be created in the database");
					$main->setCentralPayload($oPatternCustom);
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
					$main->setHasRequiredFields(true);
					$main->render();
					
				}
			} else {
				//display the browse page
				include_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");			
				include_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");			
				include_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
				include_once("$default->owl_fs_root/presentation/Html.inc");
				include_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				include_once("addFolderCollaborationUI.inc");
						
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fFolderID, $fGroupID, $fRoleID, $fSequenceNumber));
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
				$main->setHasRequiredFields(true);
				$main->render();
			}
		}
	} else {
		//display an error message
		include_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");			
		include_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");			
		include_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
		include_once("$default->owl_fs_root/presentation/Html.inc");
		include_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		include_once("addFolderCollaborationUI.inc");
						
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No folder currently selected");
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForStore=1");
		$main->setHasRequiredFields(true);
		$main->render();
	}
}
?>

<?php
/**
* Business logic used to edit folder properties
*
* Expected form variables:
*	o $fFolderID - primary key of folder user is currently browsing
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 2 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
		
	if (Permission::userHasFolderWritePermission($fFolderID)) {
		//if the user can edit the folder		
		if (isset($fFolderID)) {
			if (isset($fCollaborationEdit)) {
				//user attempted to edit the folder collaboration process but could not because there is 
				//a document currently in this process
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setErrorMessage("You cannot edit this folder collaboration process as a document is currently undergoing this collaboration process");
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);
				$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=browse&fFolderID=$fFolderID"));
				$main->render();
			} else if (isset($fCollaborationDelete)) {
				//user attempted to delete the folder collaboration process but could not because there is 
				//a document currently in this process
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setErrorMessage("You cannot delete this folder collaboration process as a document is currently undergoing this collaboration process");
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);
				$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=browse&fFolderID=$fFolderID"));
				$main->render();
			} else {
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);
				$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=browse&fFolderID=$fFolderID"));
				$main->render();
			}
		} else {
			//else display an error message
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml("");
			$main->setCentralPayload($oPatternCustom);
			$mail->setErrorMessage("No folder currently selected");
			$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=browse&fFolderID=$fFolderID"));
			$main->render();
		}
	}
}

?>

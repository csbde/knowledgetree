<?php
/**
* Document collaboration business logic - contains business logic to set up
* document approval process
*
* Expected form variables:
*	o fFolderCollaborationID - 
*	o fForAdd - 
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 28 January 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/FolderCollaboration.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("collaborationUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");	
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	if (isset($fForAdd)) {
		//we are adding a new entry
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getEditPage($fFolderCollaborationID, $fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForCreate=1");
		$main->render();
	} else if (isset($fForStore)) {
		//we are storing a new entry
		$oFolderCollaboration = & FolderCollaboration::get($fFolderCollaborationID);
		$oFolderCollaboration->setGroupID($fGroupID);
		if ($fRoleID != -1) {
			$oFolderCollaboration->setRoleID($fRoleID);
		} else {
			$oFolderCollaboration->setRoleID(null);
		}
		$oFolderCollaboration->setSequenceNumber($fSequenceNumber);
		$oFolderCollaboration->update();
		redirect("$default->owl_root_url/control.php?action=editFolder&fFolderID=$fFolderID");
	} else {			
		//we are editing an existing entry
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getEditPage($fFolderCollaborationID, $fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=editFolder&fFolderID$fFolderID"));
		$main->render();
	}
	/*if (isset($fForAdd)) {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getEditPage($fFolderCollaborationID, $fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fFolderID=$fFolderID&fForCreate=1");
		$main->render();
	} else {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getEditPage($fFolderCollaborationID, $fFolderID));
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->owl_root_url/control.php?action=editFolder&fFolderID$fFolderID"));
		$main->render();
	}*/
}
?>

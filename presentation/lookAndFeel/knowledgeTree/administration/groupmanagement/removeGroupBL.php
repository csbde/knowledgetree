<?php
/**
* BL information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeGroupUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/groups/Group.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	if (isset($fGroupID)) {
		$oPatternCustom = & new PatternCustom();		
		$oPatternCustom->setHtml(getDeletePage($fGroupID));
		$main->setCentralPayload($oPatternCustom);				
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
		$main->render();		
	
	} else {
		$oPatternCustom = & new PatternCustom();		
		$oPatternCustom->setHtml(getDeletePage(null));
		$main->setCentralPayload($oPatternCustom);		
		$main->setFormAction($_SERVER["PHP_SELF"]);
		$main->render();
	
	}
	
		if (isset($fForDelete)) {
			$oGroup = Group::get($fGroupID);
			$oGroup->setName($fGroupName);
			
		if ($oGroup->delete()) {
			redirect("$default->owl_root_url/control.php?action=removeGroupSuccess");
		} else {
			redirect("$default->owl_root_url/control.php?action=removeGroupFail");
		}
	}
}
?>

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
	require_once("editGroupUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/groups/Group.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	if (isset($fFromCreate)) {
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getCreatePage($fGroupID));
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction("$default->owl_root_url/presentation/lookAndFeel/knowledgeTree/store.php?fRedirectURL=" . urlencode("$default->owl_root_url/control.php?action=editGroup&fGroupID=$fGroupID"));		
		$main->render();		
	} else if (isset($fForStore)) {
		$oGroup = Group::get($fGroupID);
		$oGroup->setName($fGroupName);
		
		echo "Group name:" . $fGroupName;
		if (isset($fGroupUnitAdmin)) {
			$oGroup->setUnitAdmin(true);
		} else {
			$oGroup->setUnitAdmin(false);
		}
		
		if (isset($fGroupSysAdmin)) {
			$oGroup->setSysAdmin(true);
		} else {
			$oGroup->setSysAdmin(false);
		}
		if ($oGroup->update()) {
			redirect($_SERVER["PHP_SELF"]);
		} else {
			
		}
	} else if (isset($fGroupID)){		
		$oPatternCustom = & new PatternCustom();		
		$oPatternCustom->setHtml(getEditPage($fGroupID));
		$main->setCentralPayload($oPatternCustom);				
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
		$main->render();
		
	} else {
		$oPatternCustom = & new PatternCustom();		
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setCentralPayload($oPatternCustom);		
		$main->setFormAction($_SERVER["PHP_SELF"]);
		$main->render();
		
	}
}
?>

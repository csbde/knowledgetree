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
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeGroupUI.inc");
    //require_once("../adminUI.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/groups/Group.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	

	if (isset($fGroupID)) {
		$oGroup = Group::get($fGroupID);
		if (!$oGroup->hasUsers()) {
			if (!$oGroup->hasUnit()) {
				if (isset($fForDelete)) {
					if ($oGroup->delete()) {
						// FIXME: refactor getStatusPage in Html.inc
						$oPatternCustom->setHtml(statusPage("Remove Group", "Group successfully removed!", "", "listGroups"));
					} else {
						$oPatternCustom->setHtml(statusPage("Remove Group", "Group deletion failed!", "There was an error deleting this group.  Please try again later.", "listGroups"));
					}
				} else {
					$oPatternCustom->setHtml(getDeletePage($fGroupID));
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
				}
			} else {
				$oPatternCustom->setHtml(statusPage("Remove Group", "This group is in a unit!", "This group can not be deleted because it belongs to a unit.", "listGroups"));
			}					
		} else {
			$oPatternCustom->setHtml(statusPage("Remove Group", "This group has users!", "This group can not be deleted because there are still users in it.", "listGroups"));
		}
	} else {
		$oPatternCustom->setHtml(statusPage("Remove Group", "No group was selected for deletion", "", "listGroups"));
	}
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>
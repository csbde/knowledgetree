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
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("removeGroupFromUnitUI.inc");
	require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
	require_once("$default->fileSystemRoot/lib/groups/Group.inc");
	require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();
		
	if(!isset($fGroupSet)){
		// build first page
		
		$oPatternCustom->setHtml(getPage(null,null));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1");
	
	}else{
		// do a check to see both drop downs selected
		if($fGroupID == -1){
			$oPatternCustom->setHtml(getPageNotSelected());
					
		}else{ 		$fUnitID = GroupUnitLink::groupBelongsToUnit($fGroupID);	
				$oPatternCustom->setHtml(getPage($fGroupID,$fUnitID));
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1&fDeleteConfirmed=1");
		}
		
	}
	
		
	if (isset($fDeleteConfirmed)){
				
		// else add to db and then goto page succes
		$oGroupUnit = new GroupUnitLink($fGroupID,$fUnitID);
		$oGroupUnit->setGroupUnitID($fGroupID);
		$oGroupUnit->delete();
		$oPatternCustom->setHtml(getPageSuccess());
		
	}
	
	// render page
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	
}
?>

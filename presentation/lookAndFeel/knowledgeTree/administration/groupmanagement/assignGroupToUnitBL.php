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
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCreate.inc");
	require_once("assignGroupToUnitUI.inc");
	require_once("$default->owl_fs_root/lib/unitmanagement/Unit.inc");
	require_once("$default->owl_fs_root/lib/groups/Group.inc");
	require_once("$default->owl_fs_root/lib/groups/GroupUnitLink.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();
		
	if(!isset($fGroupSet)){
		// build first page
		
		$oPatternCustom->setHtml(getPage(null,null));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1");
	
	}else{
		
		// do a check to see both drop downs selected
		if($fGroupID == -1 Or $fUnitID ==-1){
	
			$oPatternCustom->setHtml(getPageNotSelected());
			
					
		}else{ //check if it belongs to a unit
			$unitLink = GroupUnitLink::groupBelongsToUnit($fGroupID);
		
			// if it does'nt ..then go to normal page
			if($unitLink == false){
				
				$oPatternCustom->setHtml(getPage($fGroupID,$fUnitID));
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1&fGroupAssign=1");
			
			}else{
			//if it does...then go to failure page
				$oPatternCustom->setHtml(getPageFail($fGroupID));
				
			}
		}
	}
	
	if (isset($fGroupAssign)){
		
		// else add to db and then goto page succes
		$oGroupUnit = new GroupUnitLink($fGroupID,$fUnitID);
		$oGroupUnit->create();
		$oPatternCustom->setHtml(getPageSuccess());
		
	}
	
	// render page
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	
}
?>

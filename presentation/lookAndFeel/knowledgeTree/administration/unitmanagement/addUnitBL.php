<?php
/**
* BL information for adding a unit
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
	require_once("addUnitUI.inc");
	require_once("$default->owl_fs_root/lib/unitmanagement/Unit.inc");
	require_once("$default->owl_fs_root/lib/unitmanagement/UnitOrganisationLink.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();
	
	if (isset($fForStore)) {
		
		if($fUnitName != "" and $fOrgID !=-1)
		{
			$oUnit = new Unit($fUnitName);
								
			//$oOrg = Organisation($
			// if creation is successfull..get the unit id
			if ($oUnit->create()) {
				$unitID = $oUnit->getID();
				$oUnitOrg = new UnitOrganisationLink($unitID,$fOrgID);
				
				if($oUnitOrg->create()){
					// if successfull print out success message
					$oPatternCustom->setHtml(getAddPageSuccess());
				}else{
				
					// if fail print out fail message
					$oPatternCustom->setHtml(getAddToOrgFail());
				}
				
							
			} else {
					// if fail print out fail message
					$oPatternCustom->setHtml(getAddPageFail());
			}
		}else{
		
					$oPatternCustom->setHtml(getPageFail());
		}
		
	} else if (isset($fUnitID)){		
			// post back on Unit select from manual edit page	
			$oPatternCustom->setHtml(getAddPage($fUnitID));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
	}else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getAddPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]. "?fForStore=1");
			
	}		

	//$oPatternCustom->setHtml(getPage());
	
	//$main->setFormAction("$default->owl_root_url/presentation/lookAndFeel/knowledgeTree/create.php?fRedirectURL=".urlencode("$default->owl_root_url/control.php?action=addUnitSuccess&fUnit"));
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>

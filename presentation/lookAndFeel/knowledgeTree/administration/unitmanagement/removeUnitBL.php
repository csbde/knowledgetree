<?php
/**
* BL information for adding a Unit
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
	require_once("removeUnitUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/unitmanagement/Unit.inc");
	require_once("$default->owl_fs_root/lib/orgmanagement/Organisation.inc");
	require_once("$default->owl_fs_root/lib/unitmanagement/UnitOrganisationLink.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	
	
	// get main page
	if (isset($fUnitID)) {
			
			$fOrgID = UnitOrganisationLink::unitBelongsToOrg($fUnitID);
			
			
			$oPatternCustom->setHtml(getDeleteConfirmedPage($fUnitID,$fOrgID));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDeleteConfirmed=1");
	}
	else {
		$oPatternCustom->setHtml(getDeletePage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]);
	}
		
	if (isset($fForDeleteConfirmed)) {
	
			// get unitorg object
			$oUnitOrg = new UnitOrganisationLink($fUnitID,$fOrgID);
			$oUnitOrg->setUnitOrgID($fUnitID);
			
			//get unit object
			$oUnit = Unit::get($fUnitID);
			$oUnit->setName($fUnitName);
					
			//delete unitorgobject
			$oUnitOrg->delete();	
			
			//delet unit object
			if ($oUnit->delete()) {
				
				$oPatternCustom->setHtml(getDeleteSuccessPage());
			
			} else {
				$oPatternCustom->setHtml(getDeleteFailPage());
			}
		
	}
	
	
	
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>

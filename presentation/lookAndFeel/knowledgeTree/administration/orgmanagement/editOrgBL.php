<?php
/**
* BL information for editing a Org
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
	require_once("editOrgUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/Orgmanagement/Organisation.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();		
	
	// if a new Org has been added
	// coming from manual edit page	
	if (isset($fForStore)) {
		$oOrg = Organisation::get($fOrgID);
		$oOrg->setName($fOrgName);
		
		if ($oOrg->update()) {
				// if successfull print out success message
				$oPatternCustom->setHtml(getEditPageSuccess());
				
		} else {
				// if fail print out fail message
				$oPatternCustom->setHtml(getEditPageFail());
		}
	} else if (isset($fOrgID)){		
		// post back on Org select from manual edit page	
		$oPatternCustom->setHtml(getEditPage($fOrgID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
		
		
	} else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]);
			
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>

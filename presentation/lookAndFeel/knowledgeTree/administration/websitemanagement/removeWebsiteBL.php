<?php
/**
* BL information for adding a Link
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");
require_once("../adminUI.inc");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeWebsiteUI.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
	require_once("$default->fileSystemRoot/lib/users/User.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	
	
	// get main page
	if (isset($fWebSiteID)) {
		$oPatternCustom->setHtml(getDeletePage($fWebSiteID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
		
	// get delete page
	} else {
		$oPatternCustom->setHtml(getDeletePage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]);
	}

    // if delete entry
	if (isset($fForDelete)) {
			$oWebSite = Website::get($fWebSiteID);
			$oWebSite->setWebSiteName($fWebSiteName);
			
		if ($oWebSite->delete()) {
			$oPatternCustom->setHtml(getDeleteSuccessPage());
			
		} else {
			$oPatternCustom->setHtml(getDeleteFailPage());
		}
	}
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>

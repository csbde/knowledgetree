<?php
/**
* BL information for adding a User
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
    require_once("editWebsiteUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if ($fWebSiteID) {
    	$oWebSite = WebSite::get($fWebSiteID);
    	if ($oWebSite) {
	    	if ($fForStore) {
	    		$oWebSite->setWebSiteName($fWebSiteName);
	    		$oWebSite->setWebMasterID($fWebMasterID);
	    		$oWebSite->setWebSiteURL($fWebSiteURL);
	            if ($oWebSite->update()) {
	                $oPatternCustom->setHtml(getSuccessPage());
	            } else {
	                $oPatternCustom->setHtml(getFailPage());
	            }
		    } else {
		        $oPatternCustom->setHtml(getEditWebSitePage($oWebSite));
		        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
		    }
    	} else {
			$oPatternCustom->setHtml(statusPage("Edit Website", "", "The selected website no longer exists in the database.", "listWebsites"));    		
    	}
  	} else {
  		$oPatternCustom->setHtml(statusPage("Edit Website", "", "No website has been selected for editing.", "listWebsites"));
  	}
    	
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>
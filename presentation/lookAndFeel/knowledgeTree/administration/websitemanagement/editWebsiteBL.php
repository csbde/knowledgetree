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

    if(isset($fUserID)) {
        // post back on User select from manual edit page
        $oPatternCustom->setHtml(getSelectWebSitePage($fUserID,$fWebSiteID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fSelected=1");
    } else {
        // if nothing happens...just reload edit page
        $oPatternCustom->setHtml(getSelectWebMasterPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);
    }

    if(isset($fSelected)) {
        $oPatternCustom->setHtml(getEditWebSitePage($fUserID,$fWebSiteID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
    }

    if(isset($fForStore)) {
        if($fWebSiteName != "") {
            $oWebSite = new WebSite($fWebSiteName,$fWebSiteURL, $fUserID);
            $oWebSite->setWebSiteID($fUserID,$fOldWebSiteName);

            if($oWebSite->update()) {
                $oPatternCustom->setHtml(getSuccessPage());
            } else {
                $oPatternCustom->setHtml(getFailPage());
            }
        } else {
            $oPatternCustom->setHtml(getTextPage());
        }
    }
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>

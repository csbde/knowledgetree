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
    require_once("removeUserUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUserLink.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    // get main page
    if (isset($fUserID)) {
        $oPatternCustom->setHtml(getDeleteConfirmedPage($fUserID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForDeleteConfirmed=1");
    } else {
        $oPatternCustom->setHtml(getDeletePage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);
    }

    if (isset($fForDeleteConfirmed)) {
        //get User object
        $oUser = User::get($fUserID);
        $oUser->setUserName($fUserName);

        //delete from all groups
        $oUser->deleteFromSystem();
        
        //delete the User object
        if ($oUser->delete()) {
            $oPatternCustom->setHtml(getDeleteSuccessPage());
        } else {
            $oPatternCustom->setHtml(getDeleteFailPage());
        }
    }

    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>

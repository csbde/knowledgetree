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
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("addUserToGroupUI.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUserLink.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");


    $oPatternCustom = & new PatternCustom();

    if(!isset($fUserSet)) {
        // build first page
        $oPatternCustom->setHtml(getPage($fUserID,null));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1");
    } else {
        // do a check to see both drop downs selected
        if($fUserID == -1 Or $fGroupID ==-1 OR $fUserID=="" Or $fGroupID == "") {
            $oPatternCustom->setHtml(getPageNotSelected("&fUserID=$fUserID"));
        } else {
            $oPatternCustom->setHtml(getPage($fUserID,$fGroupID));
            $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1&fUserAssign=1");
       
        }      
    }

    if (isset($fUserAssign)) {
        // else add to db and then goto page succes
        $oUserGroup = new GroupUserLink($fGroupID,$fUserID);
        if($oUserGroup->create()) {
            $oPatternCustom->setHtml(getPageSuccess("&fUserID=$fUserID"));
        } else {
            $oPatternCustom->setHtml(getPageFail("&fUserID=$fUserID"));
        }
    }
    // render page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>

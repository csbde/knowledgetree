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
    require_once("editGroupUnitUI.inc");        
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if(isset($fGroupID)) { //isset($fUserSet)) {
        // do a check to see both drop downs selected
        if($fGroupID == -1) {
            $oPatternCustom->setHtml(getPageNotSelected());
        } else {            
   			$oPatternCustom->setHtml(renderHeading("Edit Group Units"));
            $oPatternCustom->setHtml(getUnitPage($fGroupID));
            $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1&fUnitSet=1");
        }
    } else {
        // build first page
        $oPatternCustom->setHtml(getPage(null,null));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1");
    }

    if(isset($fUnitSet)) {
        if($fOtherGroupID) {
        	$oPatternCustom->setHtml("Add");
        } else {	                
	        $oPatternCustom->setHtml("Delete");
	        $main->setFormAction($_SERVER["PHP_SELF"] . "?fDeleteConfirmed=1&fGroupID=$fGroupID"); 		   
        }        
    }

    if (isset($fDeleteConfirmed)) {
        // else add to db and then goto page succes
        $oUserGroup = new GroupUserLink($fGroupID, $fUserID);
        $oUserGroup->setUserGroupID($fGroupID,$fUserID);
        if($oUserGroup->delete()) {
            $oPatternCustom->setHtml(getPageSuccess());
        } else {
            $oPatternCustom->setHtml(getPageFail());
        }
    }

    // render page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>

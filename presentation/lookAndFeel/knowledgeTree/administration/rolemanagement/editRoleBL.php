<?php
/**
* BL information for editing a role's properties
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
    require_once("editRoleUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/roles/Role.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");


    $oPatternCustom = & new PatternCustom();

    // if a new group has been added
    if (isset($fFromCreate)) {

        if($fRoleID == -1) {
            $oPatternCustom->setHtml(getAddFailPage());
        } else {
            $oPatternCustom->setHtml(getCreatePage($fRoleID));
        }

        $main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=editRoleSuccess"));

        // coming from manual edit page
    }
    else if (isset($fForStore)) {
        $oRole = Role::get($fRoleID);
        $oRole->setName($fRoleName);

        //check if checkbox checked
        if (isset($fActive)) {
            $oRole->setActive(true);
        } else {
            $oRole->setActive(false);
        }        
        //check if checkbox checked
        if (isset($fReadable)) {
            $oRole->setReadable(true);
        } else {
            $oRole->setReadable(false);
        }
        //check if checkbox checked
        if (isset($fWriteable)) {
            $oRole->setWriteable(true);
        } else {
            $oRole->setWriteable(false);
        }
        if ($oRole->update()) {
            // if successfull print out success message
            $oPatternCustom->setHtml(getEditPageSuccess());

        } else {
            // if fail print out fail message
            $oPatternCustom->setHtml(getEditPageFail());
        }
    } else if (isset($fRoleID)) {
        // post back on group select from manual edit page
        $oPatternCustom->setHtml(getEditPage($fRoleID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");


    } else {
        // if nothing happens...just reload edit page
        $oPatternCustom->setHtml(getEditPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);

    }
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>

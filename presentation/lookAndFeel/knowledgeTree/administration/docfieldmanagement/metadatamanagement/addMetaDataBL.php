<?php
/**
* BL information for adding a DocField
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("addMetaDataUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/MetaData.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if(isset($fDocFieldID)) {
        // post back on DocField select from manual edit page
        $oPatternCustom->setHtml(getAddMetaDataPage($fDocFieldID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
    } else {
        // if nothing happens...just reload edit page
        $oPatternCustom->setHtml(getSelectFieldPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);
    }

    if(isset($fForStore)) {
        if($fMetaDataName != "") {
            $oMetaData = new MetaData($fDocFieldID,$fMetaDataName);
            if($oMetaData->create()) {
                $oPatternCustom->setHtml(getSuccessPage($fDocFieldID));
            } else {
                $oPatternCustom->setHtml(getFailPage($fDocFieldID));
            }
        } else {
            $oPatternCustom->setHtml(getTextPage($fDocFieldID));
        }
    }

    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>

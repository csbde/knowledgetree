<?php
/**
* BL information for adding a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
require_once("$default->fileSystemRoot/lib/security/permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");
require_once("addUnitUI.inc");

if (checkSession()) {

	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();

    if (isset($fForStore)) {
        if($fUnitName != "" and $fOrgID != "") {
            $oUnit = new Unit($fUnitName);

            // if creation is successfull..get the unit id
            if ($oUnit->create()) {
                $unitID = $oUnit->getID();
                $oUnitOrg = new UnitOrganisationLink($unitID,$fOrgID);

                if($oUnitOrg->create()) {
                    // if successfull print out success message
                    $oPatternCustom->setHtml(getAddPageSuccess());
                } else {
                    // if fail print out fail message
                    $oPatternCustom->setHtml(getAddToOrgFail());
                }
            } else {
                // if fail print out fail message
                $oPatternCustom->setHtml(getAddPageFail());
            }
        } else {
            $oPatternCustom->setHtml(getPageFail());
        }

    } else {
		// display add unit page
        $oPatternCustom->setHtml(getAddPage());
        $main->setHasRequiredFields(true);
        $main->setFormAction($_SERVER["PHP_SELF"]. "?fForStore=1");

    }
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>

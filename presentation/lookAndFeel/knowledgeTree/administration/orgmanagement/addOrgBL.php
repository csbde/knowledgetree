<?php
/**
* BL information for adding a Org
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
	require_once("addOrgUI.inc");
    require_once("../adminUI.inc");
	require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
			
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(getPage());
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/create.php?fRedirectURL=".urlencode("$default->rootUrl/control.php?action=addOrgSuccess&fedit=1"));
	$main->render();
}
?>

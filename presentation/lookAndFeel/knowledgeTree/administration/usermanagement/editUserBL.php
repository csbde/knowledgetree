<?php
/**
* BL information for editing a User
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
	require_once("editUserUI.inc");
    require_once("../adminUI.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/users/User.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();		
	
	 if (isset($fUserID)){		
		// post back on User select from manual edit page	
		$oPatternCustom->setHtml(getUserDetailsPage($fUserID));
		$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=editUser&fSuccess=1"));		
	} else if ($fSuccess) {
        $oPatternCustom->setHtml(getEditPageSuccess());
    } else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]);
			
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>

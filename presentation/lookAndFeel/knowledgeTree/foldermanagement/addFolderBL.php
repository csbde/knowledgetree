<?php
/**
* Business logic page that provides business logic for adding a folder (uses
* addFolderUI.inc for HTML)
*
* The following form variables are exptected:
*	o $fFolderID - id of the folder the user is currently in
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 27 January 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	require_once("addFolderUI.inc");
	
	if (isset($fFolderID)) {
		echo renderPage($fFolderID);
	} else {
		echo renderPage(14);
	}
}

?>

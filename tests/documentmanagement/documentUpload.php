<?php
/**
* Contains unit test functionality for upload function of class DocumentModify found in 
* /lib/documentmanagement/DocumentModify.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 17 January 2003
*
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/documentmanagement/DocumentModify.inc");
	require_once("$default->owl_fs_root/lib/folderManagement/FolderLib.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	
	
	echo "<html><head></head><body>\n";
	echo "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"documentUpload2.php\">\n";
	$oFolderDropDown = & new PatternListBox("folders", "name", "id", "folderDropDown");
	echo $oFolderDropDown->render();	
	echo "<input type=\"file\" name=\"upfile\"><br><br>\n";
	echo "<input type=\"submit\" value=\"Submit\">\n";
	
	echo "</form>\n";
	echo "<body></html>\n";
}
?>

<?php

require_once("../../config/dmsDefaults.php");

/**
* Contains unit test functionality for upload function of class PhysicalDocumentManager found in 
* /lib/documentmanagement/PhysicalDocumentManager.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 17 January 2003
* @package tests.documentmanagement
*/

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");
	require_once("$default->owl_fs_root/lib/folderManagement/Folder.inc");
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

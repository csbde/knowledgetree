<?php
/**
* Second part of test file for document upload
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 17 January 2003
*
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/documentmanagement/DocumentModify.inc");
	require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
	require_once("$default->owl_fs_root/lib/folderManagement/FolderLib.inc");
require_once("$default->owl_fs_root/lib/folderManagement/FolderManager.inc");	
	
	echo "Document upload succeeded: " . (DocumentModify::uploadDocument($_FILES, $folderDropDown, "None", $_FILES['upfile']['tmp_name']) ? "Yes" : "No");
	
	
}
?>

<?php

require_once("../../config/dmsDefaults.php");

/**
* Second part of test file for document upload
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 17 January 2003
* @package tests.documentmanagement
*/

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/folderManagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/folderManagement/FolderManager.inc");
	
	echo "Document upload succeeded: " . (PhysicalDocumentManager::uploadPhysicalDocument($_FILES, $folderDropDown, "None", $_FILES['upfile']['tmp_name']) ? "Yes" : "No");

	
	
}
?>

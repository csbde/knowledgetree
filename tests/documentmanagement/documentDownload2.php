<?php
require_once("../../config/dmsDefaults.php");

/**
 * @package tests.documentmanagement
 */
if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");	

	if (isset($documentID)) {
		PhysicalDocumentManager::downloadPhysicalDocument($documentID);
		PhysicalDocumentManager::inlineViewPhysicalDocument($documentID);
	} else {
		echo "No file to download";
	}
}

?>

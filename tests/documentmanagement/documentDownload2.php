<?php
require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");
	require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
	require_once("$default->owl_fs_root/lib/folderManagement/FolderLib.inc");
	require_once("$default->owl_fs_root/lib/folderManagement/FolderManager.inc");

	if (isset($documentID)) {
		PhysicalDocumentManager::downloadPhysicalDocument($documentID);
		PhysicalDocumentManager::inlineViewPhysicalDocument($documentID);
	} else {
		echo "No file to download";
	}
}

?>

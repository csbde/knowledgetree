<?php
/**
* Unit test for class DocumentTransaction in /lib/documentmanagement/DocumentTransaction.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 18 January 2003
*
*/
require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
	require_once("$default->owl_fs_root/lib/documentmanagement/DocumentTransaction.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/lib/foldermanagement/FolderManager.inc");

	$oDocTransaction = & new DocumentTransaction(11, 'Test transaction', 1);
	echo "DB create successful? " . ($oDocTransaction->create() ? "Yes" : "No");
}
?>

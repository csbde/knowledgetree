<?php

/**
* This page will be used to sync the old documents and folders table entries to the new ones
* This involves:
*	o	creating the parent_folder_ids and full_path entries for the existing documents that don't have them
*	o	creating the parent_folder_ids and full_path entries for the existing folders that don't have them
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 21 February 2003
* @package sync
*/

require_once("../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");


$aFolders = Folder::getList("parent_folder_ids IS NULL");
$aDocuments = Document::getList("parent_folder_ids IS NULL");

//update the folders
for ($i = 0; $i < count($aFolders); $i++) {
	$oFolder = $aFolders[$i];
	echo "Updating folder: " . $oFolder->getName() . "<br>";
	updateFolder($oFolder);
}

echo "<br>";

//update the documents
for ($i = 0; $i < count($aDocuments); $i++) {
	$oDocument = $aDocuments[$i];
	echo "Updating document: " . $oDocument->getName() . "<br>";
        if (!$oDocument->getCheckedOutUserID()) {
           $oDocument->setCheckedOutUserID(-1);
        }
	$oDocument->update(true);
}

function updateFolder($oFolder) {
		global $default, $lang_err_database, $lang_err_object_key;		
		$sFullPath = $oFolder->generateFullFolderPath($oFolder->getParentID());
		$sFullPath = substr($sFullPath,1,strlen($sFullPath));				
		$sParentIDs = $oFolder->generateParentFolderIDS($oFolder->getParentID());
		$sParentIDs = substr($sParentIDs,1,strlen($sParentIDs));
		
		$sql = $default->db;		
		//root folders won't get anything added to them
		if (strlen($sFullPath) > 0) {			
			$sql->query("UPDATE " . $default->folders_table . " SET " .
							"full_path = '" . addslashes($sFullPath) . "', " .
							"parent_folder_ids = '" . addslashes($sParentIDs) . "' " .
							"WHERE id = " . $oFolder->getID());
		}
}

?>

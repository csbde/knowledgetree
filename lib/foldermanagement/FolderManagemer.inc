<?php

/**
* Class FolderManager
*
* Contains static functions concerned with the insert, update and deletion of folders in
* the database.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 15 January 2003
*/

class FolderManager {
	
	
	/**
	* Insert a new folder into the database
	*
	* @param $sName				Folder name
	* @param $sDescription		Short description of folder
	* @param $iParentID			Primary key of parent folder
	* @param $iCreatorID		ID of user who created the folder
	* @param $iDocumentTypeID	Document type this folder will store
	* @param $iUnitID			Unit to which the folder belongs
	* @param $bIsPublic			Is the folder public (default = false);
	*
	* @return 	true on sucessful insertion, false otherwise and set $_SESSION["errorMessage"]
	*/
	function insertFolder($sName, $sDescription, $iParentID, $iCreatorID, $iDocumentTypeID, $iUnitID, $bIsPublic = false) {
		global $default, $lang_err_database;
		if (!(FolderNamager::folderExistsName($sName, $iParentID)) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_folders_table . " (name, description, parent_id, creator_id, document_type_id, unit_id, is_public) " . 
						"VALUES (" . $sName . . $sDescription . . $iParentID . . $iCreatorID .. $iDocumentTypeID .. $UnitID .. $bIsPublice")");
			if (!$result) {
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
			return true;
		}
		//error message set in folderExistsName
		return false;
		
	}
	
	/**
	* Delete and existing folder
	*
	* $iFolderID 	Primary key of folder to delete
	*
	* @return		true on successfuly deletion, false otherwise and set $_SESSION["errorMessage"]
	*/
	function deleteFolder($iFolderID) {
		global $default,
		if (folderExistsID($iFolderID)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
			if (!$result) {
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
			return true;		
		}
		$_SESSION["errorMessage"] = $lang_err_folder_not_exist . "id " . $iFolderID;
		return false;
	}
	
	/**
	* Checks if a given folder already exists using the folder name
	*
	* @param $sName			Name of folder
	* @param $iParentID		Primary key of parent folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsName($sName, $iParentID) {
		global $default, $lang_err_folder_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE name = '" . $sName . "' AND parent_id = " . $iParentID);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] =$lang_err_folder_exist . $sName;
		return false;
	}
	
	/**
	* Checks if a given folder already exists using the folder name
	*
	* @param $iFolderID			Primary key of folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsID($iFolderID) {
		global $default, $lang_err_folder_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] =$lang_err_folder_exist . $sName;
		return false;
	}
	
	
	
}

?>


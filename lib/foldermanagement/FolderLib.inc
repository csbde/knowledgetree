<?php

/**
* Class FolderLib
*
* Contains static miscellaneous functions used for folder management
*/

class FolderLib {

	/**
	* Check if the folder is a public folder
	*
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if folder is public, false otherwise and set $_SESSION["errorMessage"]
	*/
	function isPublicFolder($iFolderID) {
		global $default, $lang_err_database;
		if (FolderManager::folderExistsID($iFolderID)) {
			$sql = new Owl_DB();
			$sql->query("SELECT is_public FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
			if ($sql->next_record()) {
				return $sql->f("is_public");
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;			
		}
		//error message set by FolderManager::folderExists
		return false;
	}
	
	/**
	* Get the document type for a folder
	*
	* @param $iFolderID
	*
	* @return integer document type primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderDocumentType($iFolderID) {
		global $default, $lang_err_database;
		if (FolderManager::folderExistsID($iFolderID)) {
			$sql = new Owl_DB();
			$sql->query("SELECT document_type_id FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
			if ($sql->next_record()) {
				return $sql->f("document_type_id");
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			
		}
		//error message set by FolderManager::folderExists
		return false;
	}
		
	/**
	* Get the folder name using the primary key
	*
	* @param $iFolderID		Primary key of folder to get name for
	*
	* @return String name on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderName($iFolderID) {
		global $default, $lang_err_database;
		$sql = new Owl_DB();
		$sql->query("SELECT name FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		if ($sql->next_record()) {
			return stripslashes($sql->f("name"));
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	* Get the primary key of the parent folder
	*
	* @param $iFolderID		Primary key of folder to get parent for
	*
	* @return integer primary key of parent folder
	*/
	function getParentFolderID($iFolderID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT parent_id FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID);
		$sql->next_record();
		return $sql->f("parent_id");
	}
	
	/**
	* Get the full path for a folder
	* 
	* @param 	Primary key of folder to generate path for	
	*
	* @return String full path of folder
	*/
	function getFolderPath($iFolderID) {
		global $default;		
		//if the folder has a parent
		if (FolderLib::getParentFolderID($iFolderID) != 0) {			
			$sCurrentPath = FolderLib::getFolderPath(FolderLib::getParentFolderID($iFolderID), FolderLib::getFolderName($iFolderID)) . "/" . $sCurremtPath;
		}
		return $default->owl_FileDir . "/" . FolderLib::getFolderName($iFolderID);
		
	}
}
?>

<?php

/***
* Class documentModify.inc
*
* Contains all functions required to upload, alter and
* delete a document.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 13 January 2003
*/

class DocumentModify {
	
	/**
	* Checks if the current user has write permission for a specific folder
	*	
	* @param $iFolderID		Primary key of folder to check
	*
	* @return true is the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function hasFolderWritePermission($iFolderID) {
		
		return true;
	}
	
	/**
	* Check is the user is assigned a specific role that has write permission for a folder
	*	*
	* @param $sRoleName		Name of role to check
	* @param $iFolderID 	Primary key of folder to check
	*
	* @return true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
	*/
	function hasWriteRoleForFolder($sRoleName, $iFolderID) {
		global $default;
		$iRoleID = $this->getRoleID($sRoleName);
		if (!($iRoleID === false)) {
			$sql = new Owl_DB();
			$sql->query("SELECT * FROM " . $default->owl_folders_user_links_table . " AS FURL INNER JOIN " . $default->owl_role_table . " AS R ON FURL.role_id = R.id WHERE role_id = " . $iRoleID . " AND folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND R.can_write = 1");
			if ($sql->next_record()) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_user_role;
			return false;
		}
		//error message is set in $this->getRoleID($sRoleName);
		return false;
		
	}
	
	/**
	* Get the primary key for a role
	*
	* @param $sRoleName		Name of role to get primary key for
	*
	* @return ID if role exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getRoleID($sRoleName) {
		global $default, $lang_database_error;
		if (roleExists($sRoleName) {
			$sql = new Owl_DB();
			$sql->query("SELECT id FROM " . $default->owl_role_table . " WHERE name = '" . $sRoleName . "'";
			sql->next_record();
			return sql->f("id");	
		}
		$_SESSION["errorMessage"] = $lang_database_error;
		return false;
	}
	
	/**
	* Checks if a given role exists
	*
	* @param $sRoleName		Role to check for
	*
	* @return true if role exists, false otherwise and set $_SESSION["errorMessage"] 
	*/
	function roleExists($sRoleName) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_role_table . " WHERE name = '" . $sRoleName . "'";
		if (sql->next_record()) {
			return true;	
		}
		$_SESSION["errorMessage"] = $lang_err_role_not_exist . $sRoleName;
		return false;
	}
	
}

?>

<?php
/**
* Class Permission
*
* Contains static functions used to determine whether the current user:
* 	o has permission to perform certain actions
*  	o has a certain role
*	o is assigned to a certain group
*	o has read/write access for a specific folder/directory
*/

class Permission {
	
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
	*	
	* @param $iFolderID 	Primary key of folder to check
	*
	* @return true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
	*/
	function hasWriteRoleForFolder($iFolderID) {
		global $default;	
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_user_links_table . " AS FURL INNER JOIN " . $default->owl_role_table . " AS R ON FURL.role_id = R.id WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND R.can_write = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_role;		
		return false;		
	}
	
	/**
	* Checks if a given role exists using the role primary key
	*
	* @param $iRoleID		Primary key of role to check for
	*
	* @return true if role exists, false otherwise and set $_SESSION["errorMessage"] 
	*/
	function roleIDExists($iRoleID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_role_table . " WHERE id = " . $iRoleID;
		if (sql->next_record()) {
			return true;	
		}
		$_SESSION["errorMessage"] = $lang_err_role_not_exist . $sRoleName;
		return false;
	}
	
	/**
	* Checks if a given role exists using the role name
	*
	* @param $sRoleName		Name of role to check for
	*
	* @return true if role exists, false otherwise and set $_SESSION["errorMessage"] 
	*/
	function roleNameExists($sRoleName) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_role_table . " WHERE name = '" . $sRoleName . "'";
		if (sql->next_record()) {
			return true;	
		}
		$_SESSION["errorMessage"] = $lang_err_role_not_exist . $sRoleName;
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
	* Checks if the current user is in a given role
	*
	* @param $sRoleName 	Name of role to check
	*
	* @return true if the user is in the role, false otherwise and sets $_SESSION["errorMessage"]
	*/
	function isInGroup($sGroupName) {
		return true;		
	}
	
}

?>

<?php
/**
* Class Permission
*
* Contains static functions used to determine whether the current user:
* 	o has permission to perform certain actions
*  	o has a certain role
*	o is assigned to a certain group
*	o has read/write access for a specific folder/directory
*
*	@author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
*	@date 14 January 2003 
*/

require_once($default->owl_fs_root . "/lib/documentmanagement/documentLib.inc");

class Permission {
	
	/**
	* Checks if the current user has write permission for a specific document.
	* To have document write permission the user must satisfy ONE of the following conditions:
	*	o have write permission for the folder in which the document resides
	* 	o be assigned a role which has write permission for the document
	*
	* @param $iDocumentID		Primary key of document to check
	*
	* @return boolean true if the current user has document write permission, false otherwise and set $_SESSION["errorMessage"]
	*/	
	function userHasDocumentWritePermission($iDocumentID) {
		if (Permission::userHasFolderWritePermission(DocumentLib::getDocumentFolderID($iDocumentID)) ||
		Permission::userHasWriteRoleForFolder($iDocumentID)) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_doc_write . "id " . $iDocumentID;
		return false;
	}
	
	/**
	* Checks if the current user has read permission for a specific document.
	* To have document read permission the user must satisfy ONE of the following conditions:
	*	o have read permission for the folder in which the document resides
	* 	o be assigned a role which has read permission for the document
	*
	* @param $iDocumentID		Primary key of document to check
	*
	* @return boolean true if the current user has document read permission, false otherwise and set $_SESSION["errorMessage"]
	*/	
	function userHasDocumentReadPermission($iDocumentID) {
		if (Permission::userHasFolderReadPermission(DocumentLib::getDocumentFolderID($iDocumentID)) ||
		Permission::userHasReadRoleForFolder($iDocumentID)) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_doc_read . "id " . $iDocumentID;
		return false;		
	}
	
	/**
	* Checks if the current user has write permission for a specific folder
	* To have write permission on a folder the user must satisfy ONE of the following conditions:
	*	o be in the system administrator group
	*	o be in the unit administrator group for the unit to which the folder belongs
	*	o belong to a group that has write access to the folder 
	*	
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasFolderWritePermission($iFolderID) {
		global $lang_err_user_folder_write;
		if (Permission::userHasGroupWritePermissionForFolder($iFolderID) ||
			Permission::userIsInGroupName("System Administrators") ||
			Permission::userIsInUnitAdministratorGroup($iFolderID)) {
				return true;
			}
		$_SESSION["errorMessage"] = $lang_err_user_folder_write . "id " . $iFolderID; 
		return false;
	}
	
	
	/**
	* Checks if the current user has read permission for a specific folder
	* To have read permission on a folder the user must satisfy ONE of the following conditions
	*	o have write permission for the folder
	*	o belong to a group that has read access to the folder
	*	o the folder is a public folder
	*
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasFolderReadPermission($iFolderID) {
		global $lang_err_user_folder_write;
		if (Permission::folderIsPublic($iFolderID) ||
			Permission::userHasFolderWritePermission($iFolderID) ||
			Permission::userHasGroupReadPermissionForFolder($iFolderID)) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_folder_write . "id " . $iFolderID;
		return false;
	}
	
	/**
	* Checks if a folder is public
	*
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the folder is public, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderIsPublic($iFolderID) {
		global $default, $lang_err_folder_not_public;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_table . " WHERE id = " . $iFolderID . " AND is_public = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_folder_not_public . "id " . $iFolderID;
		return false;		
	}
	
	
	/**
	* Checks if the current user is in the unit administrator group for the unit
	* to which the folder belongs
	*
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*
	* @todo Remove hardcoding of 'Unit Administrators'
	*/
	function userIsInUnitAdministratorGroup($iFolderID) {
		global $lang_err_user_unitadmin_group, $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_group_folders_table ." AS GFL INNER JOIN " . $default->owl_groups_users_link_table . " as GUL ON GFL.group_id = GUL.group_id " .
					"INNER JOIN " . $default->owl_groups_table . " AS G ON G.ID = GFL.group_id " .
					"WHERE GFL.folder_id = " . $iFolderID . " " .
					"AND GUL.user_id = " . $_SESSION["user_id"] . " " .
					"AND G.Name = 'Unit Administrators' ");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_unitadmin_group . " id = " . $iFolderID;
		return false;
					
	}
	
	/**
	* Checks if the current user has write permission through group membership for a particular folder
	* 
	* @param $iFolderID 	Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasGroupWritePermissionForFolder($iFolderID) {
		global $default, $lang_err_user_folder_write;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_groups_folders_table . " WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND can_write = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_folder_write;
		return false;
	}
	
	/**
	* Checks if the current user has read permission through group membership for a particular folder
	*
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasGroupReadPermissionForFolder($iFolderID) {
		global $default, $lang_err_user_folder_read;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_groups_folders_table = "groups_folders_link" . " WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND can_read = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_folder_read;
		return false;
	}
	
	/**
	* Checks if the current user is in the specified group using the group id
	*
	* @param $iGroupID 	Primary key of group to check
	*
	* @return boolean true if the user is in the group, false otherwise and sets $_SESSION["errorMessage"]
	*/
	function userIsInGroupID($iGroupID) {		
		global $default, $lang_err_user_group;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_groups_users_table . " WHERE id = " . $iGroupID . " AND user_id = " . $_SESSION["user_id"]);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_group . "group id = " . $iGroupID;
		return false;
	}
	
	/**
	* Checks if the current user is in the specified group using the group name
	*
	* @param $sGroupName	Name of group to check
	*
	* @return boolean true if the user is in the group, false otherwise and sets $_SESSION["errorMessage"]
	*/
	function userIsInGroupName($sGroupName) {
		global $default, $lang_err_user_group;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_groups_users_table . " AS GULT INNER JOIN " . $default->owl_groups_table . " AS G ON GULT.group_id = G.ID WHERE G.name = '" . $sGroupName . "' AND user_id = " . $_SESSION["user_id"]);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_group . "group name " . $sGroupName;
		return false;
		
	}
	
	/**
	* Check is the user is assigned a specific role that has write permission for a folder
	*	
	* @param $iFolderID 	Primary key of folder to check
	*
	* @return boolean true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasWriteRoleForFolder($iFolderID) {
		global $default, $lang_err_user_role;	
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_user_table . " AS FURL INNER JOIN " . $default->owl_role_table . " AS R ON FURL.role_id = R.id WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND R.can_write = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_role;		
		return false;		
	}
	
	/**
	* Check is the user is assigned a specific role that has read permission for a folder
	*	
	* @param $iFolderID 	Primary key of folder to check
	*
	* @return boolean true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasReadRoleForFolder($iFolderID) {
		global $default, $lang_err_user_role;	
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_folders_user_table . " AS FURL INNER JOIN " . $default->owl_role_table . " AS R ON FURL.role_id = R.id WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["user_id"] . " AND R.can_read = 1");
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
	* @return boolean true if role exists, false otherwise and set $_SESSION["errorMessage"] 
	*/
	function roleIDExists($iRoleID) {
		global $default, $lang_err_role_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_roles_table . " WHERE id = " . $iRoleID);
		if ($sql->next_record()) {
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
	* @return boolean true if role exists, false otherwise and set $_SESSION["errorMessage"] 
	*/
	function roleNameExists($sRoleName) {
		global $default, $lang_err_role_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT id FROM " . $default->owl_roles_table . " WHERE name = '" . $sRoleName . "'");
		if ($sql->next_record()) {
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
		if (roleExists($sRoleName)) {
			$sql = new Owl_DB();
			$sql->query("SELECT id FROM " . $default->owl_roles_table . " WHERE name = '" . $sRoleName . "'");
			$sql->next_record();
			return $sql->f("id");	
		}
		$_SESSION["errorMessage"] = $lang_database_error;
		return false;
	}
	
	
}

?>

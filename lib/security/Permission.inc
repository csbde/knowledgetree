<?php
/**
 * $Id$
 *
 * Contains static functions used to determine whether the current user:
 * 	o has permission to perform certain actions
 *  o has a certain role
 *	o is assigned to a certain group
 *	o has read/write access for a specific folder/directory
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.security
 */
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
		global $default;
		$oDocument = & Document::get($iDocumentID);
		if ($oDocument == null) {
			$default->log->info("Failed to retrieve document with ID $iDocumentID from database");
			return false;
		}
		if (Permission::userHasFolderWritePermission($oDocument->getFolderID()) ||
			Permission::userHasWriteRoleForDocument($iDocumentID)) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_doc_write . "id " . $iDocumentID;
		return false;
	}
	
	/**
	* Checks if the current user has read permission for a specific document.
	* To have document read permission the folder must be public or the user must satisfy ONE of the following conditions:
	*	o have write permission for the document
	*	o have read permission for the folder in which the document resides
	* 	o be assigned a role which has read permission for the document
	*
	* @param $iDocumentID		Primary key of document to check
	*
	* @return boolean true if the current user has document read permission, false otherwise and set $_SESSION["errorMessage"]
	*/	
	function userHasDocumentReadPermission($iDocumentID) {
		global $default;
        $oDocument = & Document::get($iDocumentID);
		if ($oDocument == null) {
			$default->log->info("Failed to retrieve document with ID $iDocumentID from database");
			return false;
		}
		if (Permission::userHasDocumentWritePermission($iDocumentID) ||
			Permission::userHasReadRoleForDocument($iDocumentID) ||
			Permission::userHasFolderReadPermission($oDocument->getFolderID()) ||
			Permission::documentIsTemplateForDependantDocument($iDocumentID)) {
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
	*	o be assigned a role that has write access to the folder
	*	
	* @param $iFolderID		Primary key of folder to check
	*
	* @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
	*/
	function userHasFolderWritePermission($iFolderID) {		
		global $lang_err_user_folder_write;
		if (Permission::userHasGroupWritePermissionForFolder($iFolderID) ||	
			Permission::userIsSystemAdministrator() ||
			Permission::userIsUnitAdministratorForFolder($iFolderID)) {
				return true;
			}
		$_SESSION["errorMessage"] = $lang_err_user_folder_write . "id " . $iFolderID; 
		return false;
	}
	
	
	/**
	* Checks if the current user has read permission for a specific folder
	* To have read permission on a folder, the folder must be public or the user must satisfy ONE of the following conditions
	*	o have write permission for the folder
	*	o belong to a group that has read access to the folder
	*	o be assigned a role that has read permission for the folder	
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
		$sql = $default->db;
		$sql->query("SELECT * FROM " . $default->folders_table . " WHERE id = " . $iFolderID . " AND is_public = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_folder_not_public . "id " . $iFolderID;
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
		$oFolder = Folder::get($iFolderID);
		if ($oFolder == null) {
			$default->log->info("Failed to retrieve folder with ID $iFolderID from database");
			return false;
		}
		$sql = $default->db;			
		$sql->query("SELECT GFL.folder_id " .
						"FROM groups_folders_link AS GFL INNER JOIN users_groups_link AS UGL ON GFL.group_id = UGL.group_id " .
						"WHERE UGL.user_id =  " . $_SESSION["userID"] . " " .
						"AND GFL.can_write = 1 " .
						"AND GFL.folder_id IN (" . (strlen($oFolder->getParentFolderIDs()) > 0 ? $oFolder->getParentFolderIDs() . ",$iFolderID" : $iFolderID) .  ")");		
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_folder_write;
		return false;
	}
	
	/**
	* Generate a string to be used in a where clause
	* that consists of a list of id that are a folders
	* parent  Used this because user has read/write permission for a folder if s/he 
	* has read/write permission for the folder's parent (have to recurse up
	* entire hierarchy)
	*
	* @param 	int		Primary key of folder to start at
	*
	*/
	function generateParentFolderString($iFolderID) {
		$sFolderIDString = $iFolderID;
		//$iParentFolderID = $iFolderID;
		//recurse up the hierarchy, building the string as we go
		$iParentFolderID = Folder::getParentFolderID($iFolderID);
		while ($iParentFolderID != 0) {			
			$sFolderIDString .= ", " . $iParentFolderID;			
			$iFolderID = $iParentFolderID;
			$iParentFolderID = Folder::getParentFolderID($iFolderID);
		}		
		return $sFolderIDString;
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
		$sql = $default->db;
		$oFolder = Folder::get($iFolderID);
		if ($oFolder == null) {
			$default->log->info("Failed to retrieve folder with ID $iFolderID from database");
			return false;
		}
		//$sql->query("SELECT * FROM " . $default->groups_folders_table = "groups_folders_link" . " WHERE folder_id = " . $iFolderID . " AND user_id = " . $_SESSION["userID"] . " AND can_read = 1");
        $sql->query("SELECT GFL.folder_id " .
						"FROM groups_folders_link AS GFL INNER JOIN users_groups_link AS UGL ON GFL.group_id = UGL.group_id " .
						"WHERE UGL.user_id =  " . $_SESSION["userID"] . " " .
						"AND GFL.can_read = 1 " .
						"AND GFL.folder_id IN (" . (strlen($oFolder->getParentFolderIDs()) > 0 ? $oFolder->getParentFolderIDs() . ",$iFolderID" : $iFolderID) .  ")");		
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
		$sql = $default->db;
		$sql->query("SELECT id FROM " . $default->users_groups_table . " WHERE group_id = " . $iGroupID . " AND user_id = " . $_SESSION["userID"]);
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
		$sql = $default->db;
		$sql->query("SELECT GULT.id FROM " . $default->users_groups_table . " AS GULT INNER JOIN " . $default->groups_table . " AS G ON GULT.group_id = G.ID WHERE G.name = '" . $sGroupName . "' AND user_id = " . $_SESSION["userID"]);
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
	function userHasWriteRoleForDocument($iDocumentID) {
		global $default, $lang_err_user_role;	
		$sql = $default->db;
		$sql->query("SELECT FURL.id FROM $default->folders_user_roles_table AS FURL INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " .
					"INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .					
					"WHERE FURL.user_id = " . $_SESSION["userID"] . " " .
					"AND FURL.document_id = $iDocumentID " .
					"AND R.can_write = 1 " . 
					"AND R.active = 1");
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
	function userHasReadRoleForDocument($iDocumentID) {
		global $default, $lang_err_user_role;	
		$sql = $default->db;
		$sql->query("SELECT * FROM $default->folders_user_roles_table AS FURL INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " .
					"INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .					
					"WHERE FURL.user_id = " . $_SESSION["userID"] . " " .
					"AND FURL.document_id = $iDocumentID " .
					"AND R.can_read = 1");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_user_role;		
		return false;		
	}
	
	/** Static functions
	*
	* Checks if the document is a template for a depedant document
	* that the user is responsible for creating
	*/
	function documentIsTemplateForDependantDocument($iDocumentID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT id FROM $default->dependant_document_instance_table WHERE template_document_id = $iDocumentID and user_id = " . $_SESSION["userID"]);
		if ($sql->next_record()) {
			return true;
		}
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
		$sql = $default->db;
		$sql->query("SELECT id FROM " . $default->roles_table . " WHERE id = " . $iRoleID);
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
		$sql = $default->db;
		$sql->query("SELECT id FROM " . $default->roles_table . " WHERE name = '" . $sRoleName . "'");
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
		global $default, $lang_err_database;
		if (roleExists($sRoleName)) {
			$sql = $default->db;
			$sql->query("SELECT id FROM " . $default->roles_table . " WHERE name = '" . $sRoleName . "'");
			$sql->next_record();
			return $sql->f("id");	
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	* Check if the current user is a system administrator
	*
	* @return boolean true is user is system administrator, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function userIsSystemAdministrator($iUserID = "") {
		global $default, $lang_err_database;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }
		$sql = $default->db;
		$sql->query("SELECT UGL.group_id " . 
					"FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON UGL.group_id = GL.id " .
					"WHERE UGL.user_id = $iUserID " .
					"AND is_sys_admin = 1");
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
	
	/**
	* Checks if the current user is a unit administrator
	*
	* @return boolean true if the user is the unit administrator for the unit to which the folder belongs, false otherwise
	*/
	function userIsUnitAdministrator($iUserID = "") {
        global $default;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }        
		$sql = $default->db;
		$sql->query("SELECT UGL.group_id " .
			"FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_units_table AS GUL ON GUL.group_id = UGL.group_id " .
			"INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id " .
			"WHERE UGL.user_id = $iUserID " .
			"AND GL.is_unit_admin = 1");
		return $sql->next_record();
	}
    
	/**
	* Checks if the current user is a unit administrator
	*
	* @return boolean true if the user is the unit administrator for the unit to which the folder belongs, false otherwise
	*/
	function userIsUnitAdministratorForFolder($iFolderID) {
        global $default;
		$sql = $default->db;
		        	
        $sql->query("SELECT * " .
				"FROM $default->groups_folders_table AS GFL INNER JOIN $default->folders_table AS F ON GFL.folder_id = F.id " .
				"INNER JOIN $default->groups_units_table AS GUL ON GUL.unit_id = F.unit_id " .
				"INNER JOIN $default->groups_table AS GL ON GUL.group_id = GL.id " .
				"INNER JOIN $default->users_groups_table AS UGL ON UGL.group_id = GL.id " .
				"WHERE GL.is_unit_admin = 1 "  .
				"AND GFL.folder_id = $iFolderID " .
				"AND UGL.user_id = " . $_SESSION["userID"]);
		return $sql->next_record();
	}
    
    /**
     * Checks if the current user is a guest user
     *
     * @return boolean true if the user is in the Anonymous group, else false
     */
    function userIsGuest($iUserID = "") {
        global $default;
        if ($iUserID == "") {
            $iUserID = $_SESSION["userID"];
        }        
		$sql = $default->db;
        // you're a guest user if you're in the Anonymous group
		$sql->query("SELECT UGL.group_id 
                     FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id
                     WHERE GL.name = 'Anonymous'
                     AND UGL.user_id = $iUserID");
		return $sql->next_record();        
    }
}

?>

<?php
/**
 * $Id$
 *
 * Contains static functions used to determine whether the current user:
 *  o has permission to perform certain actions
 *  o has a certain role
 *  o is assigned to a certain group
 *  o has read/write access for a specific folder/directory
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
    *   o have write permission for the folder in which the document resides
    *   o be assigned a role which has write permission for the document
    *
    * @param $oDocument       Document to check
    *
    * @return boolean true if the current user has document write permission, false otherwise and set $_SESSION["errorMessage"]
    */  
    function userHasDocumentWritePermission($oDocument) {
        global $default;
        
        if ($oDocument == null) {
            $default->log->info("Failed to retrieve document from database");
            return false;
        }
        if (Permission::userHasFolderWritePermission(Folder::get($oDocument->getFolderID())) ||
            Permission::userHasWriteRoleForDocument($oDocument)) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_doc_write . "id " . $oDocument->getID();
        return false;
    }
    
    /**
    * Checks if the current user has read permission for a specific document.
    * To have document read permission the folder must be public or the user must satisfy ONE of the following conditions:
    *   o have write permission for the document
    *   o have read permission for the folder in which the document resides
    *   o be assigned a role which has read permission for the document
    *
    * @param $oDocument       Document to check
    *
    * @return boolean true if the current user has document read permission, false otherwise and set $_SESSION["errorMessage"]
    */  
    function userHasDocumentReadPermission($oDocument) {
        global $default;
                
        if ($oDocument == null) {
            $default->log->info("Failed to retrieve document from database");
            return false;
        }
        if (Permission::userHasWriteRoleForDocument($oDocument) ||
            Permission::userHasReadRoleForDocument($oDocument) ||
            Permission::userHasFolderReadPermission(Folder::get($oDocument->getFolderID())) ||
            Permission::documentIsTemplateForDependantDocument($oDocument)) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_doc_read . "id " . $oDocument->getID();
        return false;       
    }
    
    /**
    * Checks if the current user has write permission for a specific folder
    * To have write permission on a folder the user must satisfy ONE of the following conditions:
    *   o be in the system administrator group
    *   o be in the unit administrator group for the unit to which the folder belongs
    *   o belong to a group that has write access to the folder
    *   o be assigned a role that has write access to the folder
    *   
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderWritePermission($oFolder) {       
        global $lang_err_user_folder_write, $default;
        if (Permission::userHasGroupWritePermissionForFolder($oFolder) ||   
            Permission::userIsSystemAdministrator() ||
            Permission::userIsUnitAdministratorForFolder($oFolder)) {
                $default->log->debug("FOLDER PERMISSIONS: Does have folder writer permission");
                return true;
            }
        $_SESSION["errorMessage"] = $lang_err_user_folder_write . "id " . $oFolder->getID();
        $default->log->debug("FOLDER PERMISSIONS: Does NOT have folder writer permission"); 
        return false;
    }
    
    
    /**
    * Checks if the current user has read permission for a specific folder
    * To have read permission on a folder, the folder must be public or the user must satisfy ONE of the following conditions
    *   o have write permission for the folder
    *   o belong to a group that has read access to the folder
    *   o be assigned a role that has read permission for the folder    
    *
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasFolderReadPermission($oFolder) {        
        global $lang_err_user_folder_write;
        
        if (Permission::folderIsPublic($oFolder) ||
            Permission::userHasFolderWritePermission($oFolder) ||
            Permission::userHasGroupReadPermissionForFolder($oFolder)) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_folder_write . "id " . $oFolder->getID();
        return false;
    }
    
    /**
    * Checks if a folder is public
    *
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true if the folder is public, false otherwise and set $_SESSION["errorMessage"]
    */
    function folderIsPublic($oFolder) {
        global $default, $lang_err_folder_not_public;
        
        if ($oFolder->getIsPublic()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    * Checks if the current user has write permission through group membership for a particular folder
    * 
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasGroupWritePermissionForFolder($oFolder) {
        global $default, $lang_err_user_folder_write;
        
        if ($oFolder == null) {
            $default->log->info("Failed to retrieve folder with ID " . $oFolder->getID() . " from database");
            return false;
        }       
        $sql = $default->db;
        $sQuery = "SELECT GFL.folder_id " ./*ok*/
                        "FROM $default->groups_folders_table AS GFL INNER JOIN $default->users_groups_table AS UGL ON GFL.group_id = UGL.group_id " .
                        "WHERE UGL.user_id = ? " .
                        "AND GFL.folder_id = ? " .
                        "AND GFL.can_write = 1 ";
        $aParams = array($_SESSION["userID"], $oFolder->getPermissionFolderID());

        $res = DBUtil::runQuery(array($sQuery, $aParams));
        
        if (PEAR::isError($res)) {
            $default->log->error("userHasGroupWritePermissionForFolder: Error in SQL statement -> follows:");
            $default->log->error($res->toString());
            return false;
        }
        if ($res->numRows()) {
            $default->log->debug("FOLDER PERMISSIONS: Does have group write permission for folder");
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
    * @param    int     Primary key of folder to start at
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
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true if the user has folder write permission, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasGroupReadPermissionForFolder($oFolder) {
        global $default, $lang_err_user_folder_read;        
        $sql = $default->db;
        
        if ($oFolder == null) {
            $default->log->info("Failed to retrieve folder with ID " . $oFolder->getID() . " from database");
            return false;
        }

        $sQuery = "SELECT GFL.folder_id " ./*ok*/
                  "FROM $default->groups_folders_table AS GFL INNER JOIN $default->users_groups_table AS UGL ON GFL.group_id = UGL.group_id " .
                  "WHERE UGL.user_id = ? " .
                  "AND GFL.folder_id = ? " .
                  "AND GFL.can_read = 1 ";
        $aParams = array($_SESSION["userID"], $oFolder->getPermissionFolderID());
        
        //$sql->query(array($sQuery, $aParams));
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        
        if (PEAR::isError($res)) {
            $default->log->error("userHasGroupReadPermissionForFolder: Error in SQL statement -> follows:");
            $default->log->error($res->toString());
            return false;
        }
        if ($res->numRows()) {
            $default->log->debug("FOLDER PERMISSIONS: Does have group read permission for folder");
            return true;
        }

        $_SESSION["errorMessage"] = $lang_err_user_folder_read;
        $default->log->debug("FOLDER PERMISSIONS: Does  NOT have group read permission for folder");
        return false;
    }
    
    /**
    * Checks if the current user is in the specified group using the group id
    *
    * @param $iGroupID  Primary key of group to check
    *
    * @return boolean true if the user is in the group, false otherwise and sets $_SESSION["errorMessage"]
    */
    function userIsInGroupID($iGroupID) {       
        global $default, $lang_err_user_group;
        $sql = $default->db;
        $sQuery = "SELECT id FROM " . $default->users_groups_table . " WHERE group_id = ? AND user_id = ?";/*ok*/
        $aParams = array($iGroupID, $_SESSION["userID"]);
        $sql->query(array($sQuery, $aParams));
        if ($sql->next_record()) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_group . "group id = " . $iGroupID;
        return false;
    }
    
    /**
    * Checks if the current user is in the specified group using the group name
    *
    * @param $sGroupName    Name of group to check
    *
    * @return boolean true if the user is in the group, false otherwise and sets $_SESSION["errorMessage"]
    */
    function userIsInGroupName($sGroupName) {       
        global $default, $lang_err_user_group;
        $sql = $default->db;
        $sQuery = "SELECT GULT.id FROM " . $default->users_groups_table . " AS GULT INNER JOIN " . $default->groups_table . " AS G ON GULT.group_id = G.ID WHERE G.name = ? AND user_id = ?";/*ok*/
        $aParams = array($sGroupName, $_SESSION["userID"]);
        $sql->query(array($sQuery, $aParams));
        if ($sql->next_record()) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_group . "group name " . $sGroupName;
        return false;
        
    }
    
    /**
    * Check is the user is assigned a specific role that has write permission for a folder
    *   
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasWriteRoleForDocument($oDocument) {
        global $default, $lang_err_user_role;   
        $sql = $default->db;
        $sQuery = "SELECT FURL.id FROM $default->folders_user_roles_table AS FURL INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " ./*ok*/
                    "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .                   
                    "WHERE FURL.user_id = ? " .
                    "AND FURL.document_id = ? " .
                    "AND R.can_write = 1 " . 
                    "AND R.active = 1";
        $aParams = array($_SESSION["userID"], $oDocument->getID());
        $sql->query(array($sQuery, $aParams));
        if ($sql->next_record()) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_user_role;        
        return false;       
    }
    
    /**
    * Check is the user is assigned a specific role that has read permission for a folder
    *   
    * @param $iFolderID     Primary key of folder to check
    *
    * @return boolean true is the user has the role assigned, false otherwise and set $_SESSION["errorMessage"]
    */
    function userHasReadRoleForDocument($oDocument) {
        global $default, $lang_err_user_role;   
        $sql = $default->db;
        $sQuery = "SELECT * FROM $default->folders_user_roles_table AS FURL INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " ./*ok*/
                    "INNER JOIN $default->roles_table AS R ON GFAL.role_id = R.id " .                   
                    "WHERE FURL.user_id = ? " .
                    "AND FURL.document_id = ? " .
                    "AND R.can_read = 1";
        $aParams = array($_SESSION["userID"], $oDocument->getID());
        $sql->query(array($sQuery, $aParams));
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
    function documentIsTemplateForDependantDocument($oDocument) {
        global $default;
        $sql = $default->db;
        $sQuery = "SELECT id FROM $default->dependant_document_instance_table WHERE template_document_id = ? and user_id = ?";/*ok*/
        $aParams = array($oDocument->getID(), $_SESSION["userID"]);
        $sql->query(array($sQuery, $aParams));
        if ($sql->next_record()) {
            return true;
        }
        return false;       
    }
    
    /**
    * Checks if a given role exists using the role primary key
    *
    * @param $iRoleID       Primary key of role to check for
    *
    * @return boolean true if role exists, false otherwise and set $_SESSION["errorMessage"] 
    */
    function roleIDExists($iRoleID) {
        global $default, $lang_err_role_not_exist;
        $sql = $default->db;
        $sql->query(array("SELECT id FROM " . $default->roles_table . " WHERE id = ?", $iRoleID));/*ok*/
        if ($sql->next_record()) {
            return true;    
        }
        $_SESSION["errorMessage"] = $lang_err_role_not_exist . $sRoleName;
        return false;
    }
    
    /**
    * Checks if a given role exists using the role name
    *
    * @param $sRoleName     Name of role to check for
    *
    * @return boolean true if role exists, false otherwise and set $_SESSION["errorMessage"] 
    */
    function roleNameExists($sRoleName) {
        global $default, $lang_err_role_not_exist;
        $sql = $default->db;
        $sql->query(array("SELECT id FROM " . $default->roles_table . " WHERE name = ?", $sRoleName));/*ok*/
        if ($sql->next_record()) {
            return true;    
        }
        $_SESSION["errorMessage"] = $lang_err_role_not_exist . $sRoleName;
        return false;
    }
    
    /**
    * Get the primary key for a role
    *
    * @param $sRoleName     Name of role to get primary key for
    *
    * @return ID if role exists, false otherwise and set $_SESSION["errorMessage"]
    */
    function getRoleID($sRoleName) {
        global $default, $lang_err_database;
        if (roleExists($sRoleName)) {
            $sql = $default->db;
            $sql->query(array("SELECT id FROM " . $default->roles_table . " WHERE name = ?", $sRoleName));/*ok*/
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
        $sql->query(array("SELECT UGL.group_id " . /*ok*/
                    "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON UGL.group_id = GL.id " .
                    "WHERE UGL.user_id = ? " .
                    "AND is_sys_admin = ?", array($iUserID, true)));
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
        $sql->query(array("SELECT UGL.group_id " ./*ok*/
            "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_units_table AS GUL ON GUL.group_id = UGL.group_id " .
            "INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id " .
            "WHERE UGL.user_id = ? " .
            "AND GL.is_unit_admin = 1", $iUserID));
        return $sql->next_record();
    }
    
    /**
    * Checks if the current user is a unit administrator
    *
    * @return boolean true if the user is the unit administrator for the unit to which the folder belongs, false otherwise
    */
    function userIsUnitAdministratorForFolder($oFolder) {
        global $default;
        $sql = $default->db;
                    
        $sQuery = "SELECT * " ./*ok*/
                "FROM $default->groups_folders_table AS GFL INNER JOIN $default->folders_table AS F ON GFL.folder_id = F.id " .
                "INNER JOIN $default->groups_units_table AS GUL ON GUL.unit_id = F.unit_id " .
                "INNER JOIN $default->groups_table AS GL ON GUL.group_id = GL.id " .
                "INNER JOIN $default->users_groups_table AS UGL ON UGL.group_id = GL.id " .
                "WHERE GL.is_unit_admin = 1 "  .
                "AND GFL.folder_id = ? " .
                "AND UGL.user_id = ?";
        $aParams = array($oFolder->getID(), $_SESSION["userID"]);
        $sql->query(array($sQuery, $aParams));
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
        $sql->query(array("SELECT UGL.group_id " ./*ok*/
             "FROM $default->users_groups_table AS UGL INNER JOIN $default->groups_table AS GL ON GL.id = UGL.group_id " .
             "WHERE GL.name = 'Anonymous' " .
             "AND UGL.user_id = ?", $iUserID));
        return $sql->next_record();        
    }

    function updateSearchPermissionsForDocument($iDocumentID) {
    	global $default;
    	
	    $sql = $default->db;

        $aDeleteCurrent = array("DELETE FROM $default->search_permissions_table WHERE document_id = ?", $iDocumentID);
        $res = DBUtil::runQuery($aDeleteCurrent);
        if (PEAR::isError($res)) {
            $default->log->error("Unable to delete existing permissions for document: " . $res->toString());
            // XXX: Carry on regardless...
        }

	    // group permissions                
	    $sGroupPerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				       "SELECT UGL.user_id AS user_id, D.id AS document_id " ./*ok*/
				       "FROM $default->documents_table AS D INNER JOIN folders AS F ON D.folder_id = F.id " .
				       "INNER JOIN $default->groups_folders_table AS GFL ON GFL.folder_id = F.permission_folder_id " .
				       "INNER JOIN $default->users_groups_table AS UGL ON UGL.group_id = GFL.group_id " .
				       "WHERE D.id = ?", $iDocumentID);
		$default->log->debug("addDocument groupPerms=$sGroupPerms");
		if ($sql->query($sGroupPerms)) {
			$default->log->debug("groupPerms succeeded");
		} else {
			$default->log->error("groupPerms failed");
		}
		// role permissions
	    $sRolePerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				      "SELECT user_id, document_id " ./*ok*/
				      "FROM $default->folders_user_roles_table " .
				      "WHERE document_id = ?", $iDocumentID);
		$default->log->info("addDocument rolePerms=$sRolePerms");
		if ($sql->query($sRolePerms)) {
			$default->log->debug("rolePerms succeeded");
		} else {
			$default->log->error("rolePerms failed");
		}

							
		// creator permissions
		$sCreatorPerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
						 "SELECT creator_id, id " ./*ok*/
						 "FROM $default->documents_table " .
						 "WHERE id = ?", $iDocumentID);
		$default->log->debug("addDocument creatorPerms=$sCreatorPerms");
		if ($sql->query($sCreatorPerms)) {
			$default->log->debug("creatorPerms succeeded");
		} else {
			$default->log->error("creatorPerms failed");
		}		
    }

    function updateSearchPermissionsForUser($iUserID) {
    	global $default;
    	
	    $sql = $default->db;

        $aDeleteCurrent = array("DELETE FROM $default->search_permissions_table WHERE user_id = ?", $iUserID);
        $res = DBUtil::runQuery($aDeleteCurrent);
        if (PEAR::isError($res)) {
            $default->log->error("Unable to delete existing permissions for user: " . $res->toString());
            // XXX: Carry on regardless...
        }

	    // group permissions                
	    $sGroupPerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				       "SELECT UGL.user_id AS user_id, D.id AS document_id " ./*ok*/
				       "FROM $default->documents_table AS D INNER JOIN folders AS F ON D.folder_id = F.id " .
				       "INNER JOIN $default->groups_folders_table AS GFL ON GFL.folder_id = F.permission_folder_id " .
				       "INNER JOIN $default->users_groups_table AS UGL ON UGL.group_id = GFL.group_id " .
				       "WHERE UGL.user_id = ?", $iUserID);
		if ($sql->query($sGroupPerms)) {
			$default->log->debug("groupPerms succeeded");
		} else {
			$default->log->error("groupPerms failed");
		}
		// role permissions
	    $sRolePerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				      "SELECT user_id, document_id " ./*ok*/
				      "FROM $default->folders_user_roles_table " .
				      "WHERE user_id = ?", $iUserID);
		$default->log->info("addDocument rolePerms=$sRolePerms");
		if ($sql->query($sRolePerms)) {
			$default->log->debug("rolePerms succeeded");
		} else {
			$default->log->error("rolePerms failed");
		}

							
		// creator permissions
		$sCreatorPerms = array("INSERT INTO $default->search_permissions_table (user_id, document_id) " .
						 "SELECT creator_id, id " ./*ok*/
						 "FROM $default->documents_table " .
						 "WHERE creator_id = ?", $iUserID);
		$default->log->debug("addDocument creatorPerms=$sCreatorPerms");
		if ($sql->query($sCreatorPerms)) {
			$default->log->debug("creatorPerms succeeded");
		} else {
			$default->log->error("creatorPerms failed");
		}		
    }
}

?>

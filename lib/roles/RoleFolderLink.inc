<?php
/**
 * $Id$
 *
 * Represents a role, folder links as per the folders_users_role_link table.
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
 * @package lib.roles
 */
class RoleFolderLink {
	
	var $iId;
	var $iUserID;
	var $iFolderID;
	var $iRoleTypeID;
	var $dDateTime;
	var $bDone;
	var $bActive;
	
	/**
	* Default constructor
	*
	* @param 	int		Primary key of user to which role is linked
	* @param 	int		Primary key of folder to which role is linked
	* @param 	int		Primary key of role type
	*
	*/
	function RoleFolderLink($iNewUserID, $iNewFolderID, $iNewRoleTypeID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iUserID = $iNewUserID;
		$this->iFolderID = $iNewFolderID;
		$this->iRoleTypeID = $iNewRoleTypeID;
		//the date time is the time at which the task is finished, not the time
		//at which the object is created
		$this->dDateTime = null;
		//task cannot be done yet
		$this->bDone = false;
		$this->bActive = false;
	}
	
	/**
	* Get the object's primary key
	*
	* @return int object's primary key
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the primary key of the user to which the role is linked
	*
	* @return int primary key of user to which role is linked
	*
	*/
	function getUserID() {
		return $this->iUserID;;
	}
	
	/**
	* Set the primary key of the user to which the role is linked
	*
	* @param 	int		Primary key of user to which role  will be linked
	*
	*/
	function setUserID($iNewValue) {
		$this->iUserID = $iNewValue;
	}
	
	/**
	* Get the primary key of the folder to which the role is linked
	*
	* @return int primary key of folder to which role is linked
	*
	*/
	function getFolderID() {
		return $this->iFolderID;
	}
	
	/**
	* Set the primary key of the folder to which the role is linked
	*
	* @param 	int		Primary key of folder to which role is linked
	*
	*/
	function setFolderID($iNewValue) {
		$this->iFolderID = $iNewValue;
	}
	
	/**
	* Get the primary key of the role type
	*
	* @return int primary key of role type
	*
	*/
	function getRoleTypeID() {
		return $this->iRoleTypeID;
	}
	
	/**
	* Set the primary key of the role type
	*
	* @param 	int		Primary key of role type
	*
	*/
	function setRoleTypeID($iNewValue) {
		$this->iRoleTypeID = $iNewValue;
	}
	
	/**
	* Get the date at which the function for this role was performed
	*
	* @return date date at which the function for this role was performed
	*
	*/
	function getDateTime() {
		return $this->dDateTime;
	}
	
	/**
	* Set the date/time at which the function for this role was performed
	*
	* @param 	date/time	Date/time at which the function for this role was performed
	*
	*/
	function setDateTime($dNewValue) {
		$this->dDateTine = $dNewValue;
	}
	
	/**
	* Get the done status of the role/folder/user link
	*
	* @return boolean done status of this role/folder/user link
	*
	*/
	function getDone() {
		return $this->bDone;
	}
	
	/**
	* Set the done status of this role/folder/user link
	*
	* @param 	boolean		Done status of this role/folder/user link
	*
	*/
	function setDone($bNewValue) {
		$this->bDone = $bNewValue;
	}	
	
	/**
	* Create the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_database, $lang_err_object_exists;
		//if the object hasn't been created
		if ($this->iId < 0) {
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->folders_user_roles_table . " (user_id, folder_id, role_type_id, datetime, done, active) VALUES ($this->iUserID, $this->iFolderID, $this->iRoleTypeID, '$this->dDateTime', " . ($this->bDone ? 1 : 0) . "), " . ($this->bActive ? 1 : 0) . ")");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		return false;
	}
	
	/**
	* Update the values in the database table with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this->iId > 0) {
			$sql = $default->db;
			$result = $sql->query("UPDATE " . $default->folders_user_roles_table . " SET user_id = $this->iUserID, folder_id = $this->iFolderID, role_type_id = $this->iRoleTypeID, datetime = '$this->dDateTime', done = " . ($this->bDone ? 1 : 0) . ", active = " . ($this->bActive ? 1 : 0) . " WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM $default->folders_user_roles_table WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Static function.
	* Given a folders_users_roles_link primary key it will create a 
	* RolesFoldersLink object and populate it with the 
	* corresponding database values
	*
	* @return RolesFoldersLink populated RolesFoldersLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iRolesFoldersID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->folders_user_roles_table WHERE id = $iRolesFoldersID");
		if ($result) {
			if ($sql->next_record()) {
				$oRolesFoldersLink = & new RoleFolderLink($sql->f("user_id"), $sql->f("folder_id"), $sql->f("role_type_id"), $sql->f("datetime"));
				$oRolesFoldersLink->bDone = $sql->f("done");
				$oRolesFoldersLink->bActive = $sql->f("active");
				$oRolesFoldersLink->iId = $iRolesFoldersID;
				return $oRolesFoldersLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iRolesFoldersID . " table = $default->folders_user_roles_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list of web documents
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of RolesFoldersLink objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aRolesFoldersLinkArray;
		settype($aRolesFoldersLinkArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->folders_user_roles_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oRolesFoldersLink = & RoleFolderLink::get($sql->f("id"));
				$aRolesFoldersLinkArray[$iCount] = $oRolesFoldersLink;
				$iCount++;
			}
			return $aRolesFoldersLinkArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>

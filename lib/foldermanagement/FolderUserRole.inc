<?php
/**
* Class that represents a link between users, folders and roles as per the
* folders_users_roles_link
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 29 January 2003
* @package lib.foldermanagement
*
*/

class FolderUserRole {
	
	var $iId;
	var $iUserID;
	var $iDocumentID;
	var $iGroupFolderApprovalID;
	var $dDateTime;
	var $bDone;
	var $bActive;
	
	function FolderUserRole($iNewUserID, $iNewDocumentID, $iNewGroupFolderApprovalID, $bNewDone = false, $bNewActive = false) {
		$this->iId = -1;
		$this->iUserID = $iNewUserID;
		$this->iDocumentID = $iNewDocumentID;
		$this->iGroupFolderApprovalID = $iNewGroupFolderApprovalID;
		$this->dDateTime = getCurrentDateTime();
		$this->bDone = $bNewDone;
		$this->bActive = $bNewActive;
	}
	
	function getUserID() {
		return $this->iUserID;
	}
	
	function setUserID($iNewValue) {
		$this->iUserID = $iNewValue;
	}
	
	function getDocumentID() {
		return $this->iDocumentID;
	}
	
	function setDocumentID($iNewValue) {
		$this->iDocumentID = $iNewValue;
	}
	
	function getGroupFolderApprovalID() {
		return $this->iGroupFolderApprovalID;
	}
	
	function setGroupFolderApprovalID($iNewValue) {
		$this->iGroupFolderApprovalID = $iNewValue;
	}
	
	function getDateTime() {
		return $this->dDateTime;
	}
	
	function setDateTime($dNewValue) {
		$this->dDateTime = $dNewValue;
	}
	
	function getDone() {
		return $this->bDone;
	}
	
	function setDone($bNewValue) {
		$this->bDone = $bNewValue;
	}
	
	function getActive() {
		return $this->bActive;
	}
	
	function setActive($bNewValue) {
		$this->bActive = $bNewValue;
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
			$result = $sql->query("INSERT INTO " . $default->owl_folders_user_roles_table . " (user_id, document_id, group_folder_approval_id, datetime, done, active) VALUES ($this->iUserID, $this->iDocumentID, $this->iGroupFolderApprovalID, '$this->dDateTime', " . ($this->bDone ? "1" : "0") . ", " . ($this->bActive ? "1" : "0") . ")");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = document_fields";
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
			$result = $sql->query("UPDATE " . $default->owl_folders_user_roles_table . " SET user_id = $this->iUserID, document_id = $this->iDocumentID, group_folder_approval_id = $this->iGroupFolderApprovalID, datetime = '$this->dDateTime', done = " . ($this->bDone ? "1" : "0") . ", active = " . ($this->bActive ? "1" : "0") . " WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_folders_user_roles_table WHERE id = $this->iId");
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
	* FolderUserRole object and populate it with the 
	* corresponding database values
	*
	* @return FolderUserRole populated FolderUserRole object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iFolderUserRoleID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_folders_user_roles_table WHERE id = $iFolderUserRoleID");
		if ($result) {
			if ($sql->next_record()) {
				$oFolderUserRole = & new FolderUserRole($sql->f("user_id"), $sql->f("document_id"), $sql->f("group_folder_approval_id"), $sql->f("done"), $sql->f("active"));
				$oFolderUserRole->iId = $iFolderUserRoleID;
				$oFolderUserRole->bDateTime =  $sql->f("datetime");
				
				return $oFolderUserRole;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderUserRoleID . " table = $default->owl_folders_user_roles_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list of FolderUserRoles
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of FolderUserRole objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aFolderUserRoleArray;
		settype($aFolderUserRoleArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_folders_user_roles_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oFolderUserRole = & FolderUserRole::get($sql->f("id"));
				$aFolderUserRoleArray[$iCount] = $oFolderUserRole;
				$iCount++;
			}
			return $aFolderUserRoleArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	function & getFromFolderCollaboration($iFolderCollaborationID, $iDocumentID) {
		global $default, $lang_err_database;
		$sql = $default->db;
		$sql->query("SELECT id FROM $default->owl_folders_user_roles_table WHERE group_folder_approval_id = $iFolderCollaborationID AND document_id = $iDocumentID");
		if ($sql->next_record()) {
			return FolderUserRole::get($sql->f("id"));
		}
		return false;
	}
}
?>

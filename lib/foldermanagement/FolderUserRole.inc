<?php
/**
 * $Id$
 *
 * Class that represents a link between users, folders and roles as per the
 * folders_users_roles_link table.
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
 * @package lib.foldermanagement 
 */
class FolderUserRole {
	
	var $iId;
	var $iUserID;
	var $iDocumentID;
	var $iGroupFolderApprovalID;
	var $dDateTime;
	var $bDone;
	var $bActive;
	var $bDependantDocumentsCreated;
	
	function FolderUserRole($iNewUserID, $iNewDocumentID, $iNewGroupFolderApprovalID, $bNewDone = false, $bNewActive = false, $bNewDependantDocumentsCreated = false) {
		$this->iId = -1;
		$this->iUserID = $iNewUserID;
		$this->iDocumentID = $iNewDocumentID;
		$this->iGroupFolderApprovalID = $iNewGroupFolderApprovalID;
		$this->dDateTime = getCurrentDateTime();
		$this->bDone = $bNewDone;
		$this->bActive = $bNewActive;
		$this->bDependantDocumentsCreated = $bNewDependantDocumentsCreated;
	}
	
	function getID() {
		return $this->iId;
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
	
	function setDependantDocumentsCreated($bNewValue) {
		$this->bDependantDocumentsCreated = $bNewValue;
	}
	
	function getDependantDocumentsCreated() {
		return $this->bDependantDocumentsCreated;
		
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
            $sTable = $default->folders_user_roles_table;
            $aFieldValues = array(
                'user_id' => $this->iUserID,
                'document_id' => $this->iDocumentID,
                'group_folder_approval_id' => $this->iGroupFolderApprovalID,
                'datetime' => $this->dDateTime,
                'done' => $this->bDone,
                'active' => $this->bActive,
                'dependant_documents_created' => $this->bDependantDocumentsCreated,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = folders_users_roles_link";
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
			//echo "UPDATE " . $default->folders_user_roles_table . " SET user_id = $this->iUserID, document_id = $this->iDocumentID, group_folder_approval_id = $this->iGroupFolderApprovalID, datetime = '$this->dDateTime', done = " . ($this->bDone ? "1" : "0") . ", active = " . ($this->bActive ? "1" : "0") . ", dependant_documents_created = " . ($this->bDependantDocumentsCreated ? "1" : "0") . " WHERE id = $this->iId";
			$result = $sql->query("UPDATE " . $default->folders_user_roles_table . " SET user_id = $this->iUserID, document_id = $this->iDocumentID, group_folder_approval_id = $this->iGroupFolderApprovalID, datetime = '$this->dDateTime', done = " . ($this->bDone ? "1" : "0") . ", active = " . ($this->bActive ? "1" : "0") . ", dependant_documents_created = " . ($this->bDependantDocumentsCreated ? "1" : "0") . " WHERE id = $this->iId");
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
	* FolderUserRole object and populate it with the 
	* corresponding database values
	*
	* @return FolderUserRole populated FolderUserRole object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iFolderUserRoleID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->folders_user_roles_table WHERE id = $iFolderUserRoleID");
		if ($result) {
			if ($sql->next_record()) {
				$oFolderUserRole = & new FolderUserRole($sql->f("user_id"), $sql->f("document_id"), $sql->f("group_folder_approval_id"), $sql->f("done"), $sql->f("active"), $sql->f("dependant_documents_created"));
				$oFolderUserRole->iId = $iFolderUserRoleID;
				$oFolderUserRole->bDateTime =  $sql->f("datetime");
				
				return $oFolderUserRole;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderUserRoleID . " table = $default->folders_user_roles_table";
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
		$result = $sql->query("SELECT * FROM " . $default->folders_user_roles_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
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
		$sql->query("SELECT id FROM $default->folders_user_roles_table WHERE group_folder_approval_id = $iFolderCollaborationID AND document_id = $iDocumentID");
		if ($sql->next_record()) {
			return FolderUserRole::get($sql->f("id"));
		}
		return false;
	}
	
	/** Creates default folder user roles from 
	* information obtained at the folder level where folder
	* collaboratio is created
	*/
	function createDefaultFolderUserRoles($oDocument) {
		global $default;
		$sQuery = "SELECT GFAL.id, GFAL.user_id " .
				"FROM folders_users_roles_link AS FURL RIGHT OUTER JOIN groups_folders_approval_link AS GFAL ON FURL.group_folder_approval_id = GFAL.id AND group_folder_approval_id = NULL " .
				"WHERE GFAL.folder_id = " . $oDocument->getFolderID();
		$sql = $default->db;
		$sql->query($sQuery);
		while ($sql->next_record()) {
			$oFolderUserRole = & new FolderUserRole($sql->f("user_id"), $oDocument->getID(), $sql->f("id"));
			$oFolderUserRole->create();			
		}
	}
}
?>

<?php
/**
 * $Id$
 *
 * Represents the group_folders_approval_link table in the db used to map out a
 * document approval process.
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
class FolderCollaboration extends KTEntity {
	
	/** primary key of current object */
	var $iId;
	/** primary key of folder */
	var $iFolderID;
	/** primary key of group from which user can be assigned*/
	var $iGroupID;
	/** primary key of default user */
	var $iUserID;
	/** sequence number of this step in the process*/
	var $iSequenceNumber;
	/** primary key of role use will perform */
	var $iRoleID;
	
	function FolderCollaboration($iNewFolderID, $iNewGroupID, $iNewSequenceNumber, $iNewRoleID, $iNewUserID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iFolderID = $iNewFolderID;
		$this->iGroupID = $iNewGroupID;
		$this->iSequenceNumber = $iNewSequenceNumber;
		$this->iRoleID = $iNewRoleID;
		$this->iUserID = $iNewUserID;
	}
	
	function getID() {
		return $this->iId;
	}
	
	function getFolderID() {
		return $this->iFolderID;
	}
	
	function setFolderID($iNewValue) {
		$this->iFolderID = $iNewValue;
	}
	
	function getGroupID() {
		return $this->iGroupID;
	}
	
	function setGroupID($iNewValue) {
		$this->iGroupID = $iNewValue;
	}
	
	function getSequenceNumber() {
		return $this->iSequenceNumber;
	}
	
	function setSequenceNumber($iNewValue) {
		$this->iSequenceNumber = $iNewValue;
	}
	
	function getRoleID() {
		return $this->iRoleID;
	}
	
	function setRoleID($iNewValue) {
		$this->iRoleID = $iNewValue;
	}
	
	function getUserID() {
		return $this->iUserID;
	}
	
	function setUserID($iNewValue) {
		$this->iUserID = $iNewValue;
	}

    function _fieldValues () {
        return array(
            'folder_id' => $this->iFolderID,
            'group_id' => $this->iGroupID,
            'precedence' => $this->iSequenceNumber,
            'role_id' => $this->iRoleID,
            'user_id' => $this->iUserID,
        );
    }

    function _table () {
        global $default;
        return $default->groups_folders_approval_table;
    }
	
	/**
	* Checks whether there is a document which is currently
	* going through this folder collaboration process
	*
	* return boolean true if there is a document currently going through this process, false otherwise
	*/
	function hasDocumentInProcess() {
		global $default;
		$sQuery = "SELECT FURL.id " ./*ok*/
				  "FROM $default->groups_folders_approval_table AS GFAL INNER JOIN documents AS D ON GFAL.folder_id = D.folder_id " .
                  "INNER JOIN $default->folders_user_roles_table AS FURL ON FURL.document_id = D.id " .
                  "WHERE GFAL.id = ? " .
                  "AND FURL.active != 0"; 
        $aParams = array($this->iId);
		$sql = $default->db;		
		$sql->query(array($sQuery, $aParams));
		return $sql->next_record();
		
	}
	
	/**
	* Static function.
	* Given a web_documents primary key it will create a 
	* FolderCollaboration object and populate it with the 
	* corresponding database values
	*
	* @return FolderCollaboration populated FolderCollaboration object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iFolderCollaborationID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->groups_folders_approval_table WHERE id = ?", $iFolderCollaborationID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oFolderCollaboration = & new FolderCollaboration($sql->f("folder_id"), $sql->f("group_id"), $sql->f("precedence"), $sql->f("role_id"), $sql->f("user_id"));
				$oFolderCollaboration->iId = $iFolderCollaborationID;
				return $oFolderCollaboration;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderCollaborationID . " table = $default->groups_folders_approval_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list of folder collaborations
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of FolderCollaboration objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
            return KTEntityUtil::getList(FolderCollaboration::_table(), 'FolderCollaboration', $sWhereClause);
	}
	
	/**
	 * Checks whether all the FolderCollaboration objects have the default user set
	 *
	 * @param array folder collaboration information for a specific document
	 * @return true if all default users are set, false otherwise
	 */
	function defaultUsersAssigned($aFolderCollaboration) {
		$bUsersSet = true;
		for ($i=0; $i<count($aFolderCollaboration); $i++) {
			$bUsersSet = $bUsersSet && $aFolderCollaboration[$i]->getUserID();
		}
		return $bUsersSet;
	}
}
?>

<?php
/**
* Class GroupFolderApprovalLink
* Represents a group/folder/approval link as per the groups_folders_approval_link
* 
* groups_folders_approval_link are used to set the approval workflow for a document
* The precedence_id determines the order of the workflow
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package lib.groups
*/

class GroupFolderApprovalLink {
	
	/** primary key of object */
	var $iId;
	/** primary key of folder to which role is assigned */
	var $iFolderID;
	/** primary key of group to which role is assigned */
	var $iGroupID;
	/** link precedence (1 = highest) */
	var $iPrecedence;
	/** primary key of role assigned to group */
	var $iRoleID;
	
	function GroupFolderApprovalLink($iNewFolderID, $iNewGroupID, $iNewPrecedence, $iNewRoleID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iFolderID = $iNewFolderID;
		$this->iGroupID = $iNewGroupID;
		$this->iPrecedence = $iNewPrecedence;
		$this->iRoleID = $iNewRoleID;
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
	* Get the primary key of the folder to which the group is assigned
	*
	* @return int primary key of folder to which the group is assigned
	*
	*/
	function getFolderID() {
		return $this->iFolderID;
	}
	
	/**
	* Set the primary key of the folder to which the group is assigned
	*
	* @param 	int		Primary key of folder to which the group is assigned
	*
	*/
	function setFolderID($iNewValue) {
		$this->iFolderID = $iNewValue;
	}
	
	/**
	* Get the primary key of the group to which the role is assigned
	*
	* @return int primary key of group to which the role is assigned
	*
	*/
	function getGroupID() {
		return $this->iGroupID;
	}
	
	/**
	* Set the primary key of the group to which the role is assigned
	*
	* @param 	int		Primary key of group to which the role is assigned
	*
	*/
	function setGroupID($iNewValue) {
		$this->iGroupID = $iNewValue;
	}
	
	/**
	* Get the precedence order for the GroupApprovalLink
	*
	* @return int precedence order for the GroupApprovalLink
	*
	*/
	function getPrecedence() {
		return $this->iPrecedence;
	}
	
	/**
	* Set the precedence order for the GroupApprovalLink
	*
	* @param 	int		Precedence order for the GroupApprovalLink
	*
	*/
	function setPrecedence($iNewValue) {
		$this->iPrecedence = $iNewValue;
	}
	
	/**
	* Get the primary key of the role assigned to the group
	*
	* @return int primary key of role assigned to group
	*
	*/
	function getRoleID() {
		return $this->iRoleID;
	}
	
	/**
	* Set the primary key of the role assigned to the group
	*
	* @param 	int		Primary key of role assigned to the group
	*
	*/
	function setRoleID($iNewValue) {
		$this->iRoleID = $iNewValue;
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
			$result = $sql->query("INSERT INTO " . $default->owl_groups_folders_approval_table . " (folder_id, group_id, precedence, role_id) VALUES ($this->iFolderID, $this->iGroupID, $this->iPrecedence, $this->iRoleID)");
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
			$result = $sql->query("UPDATE " . $default->owl_groups_folders_approval_table . " SET folder_id = $this->iFolderID, group_id = $this->iGroupID, precedence = $this->iPrecedence, role_id = $this->iRoleID WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_groups_folders_approval_table WHERE id = $this->iId");
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
	* Given a web_documents primary key it will create a 
	* GroupFolderApprovalLink object and populate it with the 
	* corresponding database values
	*
	* @return GroupFolderApprovalLink populated GroupFolderApprovalLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupFolderLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_groups_folders_approval_table WHERE id = $iGroupFolderLinkID");
		if ($result) {
			if ($sql->next_record()) {
				$oGroupFolderApprovalLink = & new GroupFolderApprovalLink($sql->f("folder_id"), $sql->f("group_id"), $sql->f("precedence"), $sql->f("role_id"), $sql->f("datetime"));
				$oGroupFolderApprovalLink->iId = $iGroupFolderLinkID;
				return $oGroupFolderApprovalLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iGroupFolderLinkID . " table = $default-owl_groups_folders_approval_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list of groups_folders_approval_link
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of GroupFolderApprovalLink objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aGroupFolderApprovalLinkArray;
		settype($aGroupFolderApprovalLinkArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_groups_folders_approval_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oGroupFolderApprovalLink = & GroupFolderApprovalLink::get($sql->f("id"));
				$aGroupFolderApprovalLinkArray[$iCount] = $oGroupFolderApprovalLink;
				$iCount++;
			}
			return $aGroupFolderApprovalLinkArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>

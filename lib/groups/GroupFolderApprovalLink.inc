<?php
/**
 * $Id$
 *
 * Represents a group/folder/approval link as per the groups_folders_approval_link.
 * Used to set the approval workflow for a document
 * The precedence_id determines the order of the workflow.
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
 * @package lib.groups 
 */
class GroupFolderApprovalLink extends KTEntity {
	
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

    function _fieldValues () {
        return array(
            'folder_id' => $this->iFolderID,
            'group_id' => $this->iGroupID,
            'precedence' => $this->iPrecedence,
            'role_id' => $this->iRoleID,
        );
    }

    function _table () {
        global $default;
        return $default->groups_folders_approval_table;
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
		$result = $sql->query(array("SELECT * FROM $default->groups_folders_approval_table WHERE id = ?", $iGroupFolderLinkID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oGroupFolderApprovalLink = & new GroupFolderApprovalLink($sql->f("folder_id"), $sql->f("group_id"), $sql->f("precedence"), $sql->f("role_id"), $sql->f("datetime"));
				$oGroupFolderApprovalLink->iId = $iGroupFolderLinkID;
				return $oGroupFolderApprovalLink;
			}

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
            return KTEntityUtil::getList(GroupFolderApprovalLink::_table(), 'GroupFolderApprovalLink', $sWhereClause);
	}
	
}
?>

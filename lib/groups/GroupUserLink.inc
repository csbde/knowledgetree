<?php
/**
 * $Id$
 *
 * Represents a group, unit link as per the database table groups_units_link.
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
class GroupUserLink extends KTEntity {
	
	/** primary key of object */
	var $iId;
	/** primary key of group to which unit is linked */
	var $iGroupID; 
	/** primary key of unit to which group is linked */
	var $iUserID;
	
	function GroupUserLink($iNewGroupID, $iNewUserID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iGroupID = $iNewGroupID;
		$this->iUserID = $iNewUserID;
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
	* Get the primary key of the group to which the unit is linked
	*
	* @return int primary key of group to which unit is linked
	*
	*/
	function getGroupID() {
		return $this->iGroupID;
	}
	
	/**
	* Set the primary key of the group to which the unit is linked
	*
	* @param 	int		Primary key of group to which unit is ilinked
	*
	*/
	function setGroupID($iNewValue) {
		$this->iGroupID = $iNewValue;
	}
	
	/**
	* Get the prijmary key of the unit to which the group is linked
	*
	* @return int primary key of unit to which the group is linked
	*
	*/
	function getUserID() {
		return $this->iUserID;
	}
	
	/**
	* Set the primary key of the unit to which the group is linked
	*
	* @param 	int		Primary key of unit to which the group is linked
	*
	*/
	function setUserID($iNewValue) {
		$this->iUserID = $iNewValue;
	}

    function _fieldValues () {
        return array(
            'group_id' => $this->iGroupID,
            'user_id' => $this->iUserID,
        );
    }

    function _table () {
        global $default;
        return $default->users_groups_table;
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
			$query = "SELECT user_id, group_id FROM ". $default->users_groups_table ." WHERE user_id = ? and group_id = ?";/*ok*/
            $aParams = array($this->iUserID, $this->iGroupID);
            $sql->query(array($query, $aParams));
            $rows = $sql->num_rows($sql);
        
            if ($rows > 0) {
                $_SESSION["errorMessage"] = "GroupUserLink::The id " . $this->iUnitID . " already exists!";
                return false;
            }
        }
        $res = parent::create();
        if (($res === false) || (PEAR::isError($res))) {
            return $res;
        }
        $this->updateSearchPermissions();
        return $res;
	}

    function delete() {
        $res = parent::delete();
        if (($res === false) || (PEAR::isError($res))) {
            return $res;
        }
        $this->updateSearchPermissions();
        return $res;
    }
	
	/**
	* Static function.
	* Given a groups_units_link primary key it will create a 
	* GroupUserLink object and populate it with the 
	* corresponding database values
	*
	* @return GroupUserLink populated GroupUserLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupUserLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->users_groups_table WHERE id = ?", $iGroupUserLinkID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oGroupUserLink = & new GroupUserLink($sql->f("group_id"), $sql->f("user_id"));
				$oGroupUserLink->iId = $iGroupUserLinkID;
				return $oGroupUserLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iGroupUserLinkID . " table = $default->users_groups_table";
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
	* @return Array array of GroupUserLink objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
            return KTEntityUtil::getList(GroupUserLink::_table(), 'GroupUserLink', $sWhereClause);
	}
	
	/**
	 * Updates the search permissions for a group
	 */
	function updateSearchPermissions() {
        Permission::updateSearchPermissionsForUser($this->getUserID());
	}
	
	function getGroups($iUserID) {
		global $default, $lang_err_database;
		$aGroupUserLink;
		settype($aGroupUserLink, "array");
		$sql = $default->db;
		$result = $sql->query(array("SELECT group_id FROM " . $default->users_groups_table  . " Where user_id = ?", $iUserID));/*ok*/
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				//$oGroupUserLink = & GroupUserLink::get($sql->f("id"));
				$aGroupUserLink[$iCount] = $sql->f("group_id");
				$iCount++;
			}
			return $aGroupUserLink;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
     * static function
     * test to see if group exists already
     *
     * @param   false or a value
     */
	function userBelongsToGroup($iUserID){
		global $default;
		$value = lookupField("$default->users_groups_table", "group_id", "user_id", $iUserID );
		return $value;
	}
	
	/**
     * static function
     * sets the id of the groupunit using their groupid
     *
     * @param   String
     *   The unit_ID
     */
     
	function setUserGroupID($iGroupId, $iUserId) {
	   	global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->users_groups_table WHERE user_id = ? and group_id = ?";/*ok*/
        $aParams = array($iUserId, $iGroupId);
		$result = $sql->query(array($sQuery, $aParams));
		if ($result) {
			if ($sql->next_record()) {
				$id = $sql->f("id");
			} else{
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
		} else{	
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$this->iId = $id;
	}
}
?>

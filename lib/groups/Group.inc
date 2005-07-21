<?php
/**
 * $Id$
 *
 * Represents a Group as per the database table groups.
 *
 * Copyright (c) 2003, 2005 Jam Warehouse http://www.jamwarehouse.com
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
class Group extends KTEntity {
	
	/** primary key of current object */
	var $iId;
	/** group name */
	var $sName;
	/** is the group a unit admin */
	var $bIsUnitAdmin;
	/** is the group a sys admin */
	var $bIsSysAdmin;
	
	
	function Group($sNewName, $bNewIsUnitAdmin = false, $bNewIsSysAdmin = false) {
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->bIsUnitAdmin = $bNewIsUnitAdmin;
		$this->bIsSysAdmin = $bNewIsSysAdmin;
	}

    // {{{ ktentity requirements
    // ---------------------
    // KTEntity requirements
    // ---------------------
    function _fieldValues () {
        return array(
            'name' => $this->sName,
            'is_sys_admin' => KTUtil::anyToBool($this->bIsSysAdmin),
            'is_unit_admin' => KTUtil::anyToBool($this->bIsUnitAdmin),
        );
    }

    function _table () {
        global $default;
        return $default->groups_table;
    }
    // }}}

    // {{{ getters/setters
    // ---------------
    // Getters/setters
    // ---------------

    function getUnitAdmin() { return $this->bIsUnitAdmin; }
    function setUnitAdmin($bNewValue) { $this->bIsUnitAdmin = $bNewValue; }
    function setID($iNewValue) { $this->iId = $iNewValue; }
    function getID() { return $this->iId; }
    function getSysAdmin() { return $this->bIsSysAdmin; }
    function setSysAdmin($bNewValue) { $this->bIsSysAdmin = $bNewValue; }
    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    // }}}

		
	/**
	 * Checks if this group has users mapped to it or not
	 */
	function hasUsers() {
		global $default;
		$sql = $default->db;
		$sql->query(array("SELECT id FROM $default->users_groups_table WHERE group_id = ?", $this->iId));/*ok*/
       	$rows = $sql->num_rows();
        if ($rows > 0) {
        	return true;
        } else {
        	return false;
        }
	}

	/**
	 * Checks if this Group is in a Unit
	 */	
	function hasUnit() {
		global $default;

		$sql = $default->db;
		$query = "SELECT id FROM $default->groups_units_table WHERE group_id = ?";/*ok*/
        $aParams = array($this->iId);
	    $sql->query(array($query, $aParams));
	   	$rows = $sql->num_rows();
		if ($rows > 0){
	    	return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Checks if this group has outstanding approval process
     * requirements
	 */	
	function hasRoutingSteps() {
		global $default;

		$sql = $default->db;
		$query = "SELECT id FROM $default->groups_folders_approval_table WHERE group_id = ?";/*ok*/
        $aParams = array($this->iId);
	    $sql->query(array($query, $aParams));
	   	$rows = $sql->num_rows();
		if ($rows > 0){
	    	return true;
		} else {
			return false;
		}
	}

	/**
	* Static function.
	* Given a groups primary key it will create a 
	* Group object and populate it with the 
	* corresponding database values
	*
	* @return Group populated Group object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->groups_table WHERE id = ?", $iGroupID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oGroup = & new Group($sql->f("name"), $sql->f("is_unit_admin"), $sql->f("is_sys_admin"));
				$oGroup->iId = $iGroupID;
				return $oGroup;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iGroupID . " table = $default->groups_table";
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
	* @return Array array of Group objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
            return KTEntityUtil::getList(Group::_table(), 'Group', $sWhereClause);
	}
	
	/**
	 * Returns an array of Users in this group.
	 */
	function getUsers() {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT user_id FROM $default->users_groups_table WHERE group_id = ?", $this->iId));/*ok*/
		$aUsers = array();		
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oUser = & User::get($sql->f("user_id"));
				$aUsers[$iCount++] = $oUser;
			}
		}
		return $aUsers;		
	}
	
    function &getMembers() {
        global $default;
        require_once(KT_LIB_DIR . '/users/User.inc');
        $sQuery = "SELECT user_id FROM $default->users_groups_table WHERE group_id = ?";
        $aParams = array($this->getID());
        $aUserIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), "user_id");
        $aMembers = array();
        foreach ($aUserIDs as $iUserID) {
            $oUser = User::get($iUserID);
            if ($oUser !== false) {
                $aMembers[] = $oUser;
            }
        }
        return $aMembers;
    }

    function delete() {
        global $default;

        $sQuery = "DELETE FROM $default->users_groups_table WHERE group_id = ?";
        $aParams = array($this->getID());
        DBUtil::runQuery(array($sQuery, $aParams));

        $sQuery = "DELETE FROM $default->groups_units_table WHERE group_id = ?";
        $aParams = array($this->getID());
        DBUtil::runQuery(array($sQuery, $aParams));

        return parent::delete();
    }

    // {{{ hasMember
    function hasMember($oUser) {
        global $default;

        $sQuery = "SELECT COUNT(*) AS number_of_entries FROM $default->users_groups_table
            WHERE group_id = ? AND user_id = ?";
        $aParams = array($this->getID(), $oUser->getID());
        $res = (int)DBUtil::getOneResultKey(array($sQuery, $aParams), "number_of_entries");
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res === 1) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ addMember
    function addMember($oUser) {
        global $default;
        if ($this->hasMember($oUser)) {
            return true;
        }
        $aParams = array(
            "user_id" => $oUser->getID(),
            "group_id" => $this->getID(),
        );
        $res = DBUtil::autoInsert($default->users_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        Permission::updateSearchPermissionsForUser($oUser->getID());
        return true;
    }
    // }}}

    // {{{ removeMember
    function removeMember($oUser) {
        global $default;
        if (!$this->hasMember($oUser)) {
            return true;
        }
        $aParams = array(
            "user_id" => $oUser->getID(),
            "group_id" => $this->getID(),
        );
        $res = DBUtil::whereDelete($default->users_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($this->hasMember($oUser)) {
            return PEAR::raiseError("Tried to remove member from database, apparently successfully, but hasMember thinks they're still members?");
        }
        Permission::updateSearchPermissionsForUser($oUser->getID());
        return true;
    }
    // }}}
}

/**
* Static function
*
* Creates a Group object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & groupCreateFromArray($aParameters) {
	$oGroup = & new Group($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oGroup;
}

?>

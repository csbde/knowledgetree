<?php
/**
 * $Id$
 *
 * Represents a Group as per the database table groups.
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
class Group {
	
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
	
	function getUnitAdmin() {
		return $this->bIsUnitAdmin;
	}
	
	function setUnitAdmin($bNewValue) {
		$this->bIsUnitAdmin = $bNewValue;
	}
	
	function setID($iNewValue) {
		$this->iId = $iNewValue;
	}
	
	function getID() {
		return $this->iId;
	}
	
	function getSysAdmin() {
		return $this->bIsSysAdmin;
	}
	
	function setSysAdmin($bNewValue) {
		$this->bIsSysAdmin = $bNewValue;
	}
	
	function getName() {
		return $this->sName;
	}
	
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	

	/**
	 * Checks if this group has users mapped to it or not
	 */
	function hasUsers() {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT id FROM $default->users_groups_table WHERE group_id = $this->iId");
       	$rows = $sql->num_rows();
        if ($rows > 0) {
        	return true;
        } else {
        	return false;
        }
	}

	/**
	 * Checks if this group has users mapped to it or not
	 */	
	function hasUnit() {
		global $default;

		$sql = $default->db;
		$query = "SELECT id FROM $default->groups_units_table WHERE group_id = $this->iId";
	    $sql->query($query);
	   	$rows = $sql->num_rows();
		if ($rows > 0){
	    	return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Checks if this group has users mapped to it or not
	 */	
	function hasRoutingSteps() {
		global $default;

		$sql = $default->db;
		$query = "SELECT id FROM $default->groups_folders_approval_table WHERE group_id = $this->iId";
	    $sql->query($query);
	   	$rows = $sql->num_rows();
		if ($rows > 0){
	    	return true;
		} else {
			return false;
		}
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
            $sTable = $default->groups_table;
            $aFieldValues = array(
                'name' => $this->sName,
                'is_sys_admin' => $this->bIsSysAdmin,
                'is_unit_admin' => $this->bIsUnitAdmin,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
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
			$result = $sql->query("UPDATE " . $default->groups_table . " SET name = '$this->sName', is_sys_admin = " . ($this->bIsSysAdmin ? 1 : 0) . ", is_unit_admin = " . ($this->bIsUnitAdmin ? 1 : 0) . " WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->groups_table WHERE id = $this->iId");
			return $result;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
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
		$result = $sql->query("SELECT * FROM $default->groups_table WHERE id = $iGroupID");
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
		global $default, $lang_err_database;
		$aGroupArray;
		settype($aGroupArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->groups_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oGroup = & Group::get($sql->f("id"));
				$aGroupArray[$iCount] = $oGroup;
				$iCount++;
			}
			return $aGroupArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	 * Returns an array of Users in this group.
	 */
	function getUsers() {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT user_id FROM $default->users_groups_table WHERE group_id = $this->iId");
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
	
    /**
     * static function
     *
     * gets the name of a unit using their id
     *
     * @param   String
     *   The name
     *   
     */
	function getGroupName($id) {
        global $default;
        $sName = lookupField("$default->groups_table", "name", "id", $id );
        return $sName;
	}
}
/*
     * static function
     *
     * gets the name of a unit using their id
     *
     * @param   String
     *   The name
     *   
     */
     
	function getUnitName($id)
	 {
	       global $default;
	
	        $name = lookupField("$default->groups_table", "name", "id", $id );
	        
	        $this->sName= $name;
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

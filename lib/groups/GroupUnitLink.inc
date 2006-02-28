<?php 
/**
 * $Id$
 *
 * Represents a group, unit link as per the database table groups_units_link.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
class GroupUnitLink extends KTEntity {
	
	/** primary key of object */
	var $iId;
	/** primary key of group to which unit is linked */
	var $iGroupID; 
	/** primary key of unit to which group is linked */
	var $iUnitID;
	
	function GroupUnitLink($iNewGroupID, $iNewUnitID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iGroupID = $iNewGroupID;
		$this->iUnitID = $iNewUnitID;
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
	function getUnitID() {
		return $this->iUnitID;
	}
	
	/**
	* Set the primary key of the unit to which the group is linked
	*
	* @param 	int		Primary key of unit to which the group is linked
	*
	*/
	function setUnitID($iNewValue) {
		$this->iUnitID = $iNewValue;
	}

    function _fieldValues () {
        return array(
            'group_id' => $this->iGroupID,
            'unit_id' => $this->iUnitID,
        );
    }

    function _table () {
        global $default;
        return $default->groups_units_table;
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
		if ($this->iId < 0) 
		{
			$sql = $default->db;
			$query = "SELECT unit_id, group_id FROM ". $default->groups_units_table ." WHERE unit_id = ? and group_id = ?";/*ok*/
            $aParams = array($this->iUnitID, $this->iGroupID);
            $sql->query(array($query, $aParams));
            $rows = $sql->num_rows($sql);
        
            if ($rows > 0) {
                 $_SESSION["errorMessage"] = "GroupUnitlink::The id " . $this->iUnitID . " already exists!";
                 return false;
            }
        }

        return parent::create();
	}
	
	/**
	* Static function.
	* Given a groups_units_link primary key it will create a 
	* GroupUnitsLink object and populate it with the 
	* corresponding database values
	*
	* @return GroupUnitsLink populated GroupUnitsLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupUnitLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->groups_units_table WHERE id = ?", $iGroupUnitLinkID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oGroupUnitLink = & new GroupUnitLink($sql->f("group_id"), $sql->f("unit_id"));
				$oGroupUnitLink->iId = $iGroupUnitLinkID;
				return $oGroupUnitLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iGroupUnitLinkID . " table = $default->groups_units_table";
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
	* @return Array array of GroupUnitLink objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
            return KTEntityUtil::getList(GroupUnitLink::_table(), 'GroupUnitLink', $sWhereClause);
	}
	
	
	
	/*
     * static function
     *
     * test to see if group exists already
     *
     * @param   false or a value
     *   
     */
     
	function groupBelongsToUnit($groupId)
	 {
	       global $default;
	
	        $value = lookupField("$default->groups_units_table", "unit_id", "group_id", $groupId );
	        
	        return $value;
	        
	}
	
	/*
     * static function
     *
     * sets the id of the groupunit using their groupid
     *
     * @param   String
     *   The unit_ID
     *   
     */
     
	function setGroupUnitID($groupId)
	 {
	       global $default;
	
	        $id = lookupID($default->groups_units_table, "group_id",  $groupId);
	        
	        $this->iId= $id;
	}
	
	
}
?>

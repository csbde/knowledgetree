<?php 
/**
*
* Class GroupUnitLink
* Represents a group, unit link as per the database table groups_units_link
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package lib.groups
*/

class GroupUnitLink {
	
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
			$result = $sql->query("INSERT INTO " . $default->owl_groups_units_table . " (group_id, unit_id) VALUES ($this->iGroupID, $this->iUnitID)");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_groups_units_table";
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
			$result = $sql->query("UPDATE " . $default->owl_groups_units_table . " SET group_id = $this->iGroupID, unit_id = $this->iUnitID WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_groups_units_table WHERE id = $this->iId");
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
	* Given a groups_units_link primary key it will create a 
	* GroupUnitsLink object and populate it with the 
	* corresponding database values
	*
	* @return GroupUnitsLink populated GroupUnitsLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupUnitLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_groups_units_table WHERE id = $iGroupUnitLinkID");
		if ($result) {
			if ($sql->next_record()) {
				$oGroupUnitLink = & new GroupUnitLink($sql->f("group_id"), $sql->f("unit_id"));
				$oGroupUnitLink->iId = $iGroupUnitLinkID;
				return $oGroupUnitLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iGroupUnitLinkID . " table = $default->owl_groups_units_table";
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
		global $default, $lang_err_database;
		$aGroupUnitLink;
		settype($aGroupUnitLink, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_groups_units_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oGroupUnitLink = & GroupUnitLink::get($sql->f("id"));
				$aGroupUnitLink[$iCount] = $oGroupUnitLink;
				$iCount++;
			}
			return $aGroupUnitLink;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>

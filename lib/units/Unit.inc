<?php
/**
* Class Unit
* Represents a unit as per the database table units
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 21 January 2003
* @package lib.units
*/

class Unit {
	
	/** object's primary key */
	var $iId;
	/** unit's name */
	var $sName;
	/** primary key to which the unit belongs */
	var $iOrganisationID;
	/** primary key of parent unit */
	var $iParentID;
	
	function Unit($sNewName, $iNewOrganisationID, $iNewParentID) {
		//object has not been created in database yet
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->iOrganisationID = $iNewOrganisationID;
		$this->iParentID = $iNewParentID;
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
	* Get the unit's name
	*
	* @return String unit's name
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	/**
	* Set the unit's name
	*
	* @param 	String		Unit's name
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/**
	* Get the primary key of the organisation to which this unit belongs
	*
	* @return int primary key of organisation to which this unit belongs
	*
	*/
	function getOrganisationID() {
		return $this->iOrganisationID;
	}
	
	/**
	* Set the primary key of the organisation to which this unit belongs
	*
	* @param 	int		Primary key of organisation to which this unit belongs
	*
	*/
	function setOrganisationID($iNewValue) {
		$this->iOrganisationID = $iNewValue;
	}
	
	/**
	* Get the primary key of the parent unit
	*
	* @return int primary key of parent unit
	*
	*/
	function getParentID() {
		return $this->iParentID;
	}
	
	/**
	* Set the primary key of the parent unit
	*
	* @param 	int		Primary key of parent unit
	*
	*/
	function setParentID($iNewValue) {
		$this->iParentID = $iNewValue;
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
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_units_table . " (name, organisation_id, parent_id) VALUES ('" . addslashes($this->sName) . "', $this->iOrganisationID, $this->iParentID)");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_units_table";
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
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_units_table . " SET name = '" . addslashes($this->sName) . "', organisation_id = $this->iOrganisationID, parent_id = $this->iParentID WHERE id = $this->iId");
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
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM $default->owl_units_table WHERE id = $this->iId");
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
	* Unit object and populate it with the 
	* corresponding database values
	*
	* @return Unit populated Unit object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iUnitID) {
		global $default;
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM $default->owl_units_table WHERE id = $iUnitID");
		if ($result) {
			if ($sql->next_record()) {
				$oUnit = & new Unit(stripslashes($sql->f("name")), $sql->f("organization_id"), $sql->f("parent_id"));
				$oUnit->iId = $iUnitID;
				return $oUnit;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iUnitID . " table = $default->owl_units_table";
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
	* @return Array array of Unit objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aUnitArray;
		settype($aUnitArray, "array");
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM " . $default->owl_units_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oUnit = & Unit::get($sql->f("id"));
				$aUnitArray[$iCount] = $oUnit;
				$iCount++;
			}
			return $aUnitArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}

?>

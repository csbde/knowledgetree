<?php
/**
*
* Class UnitOrganisationLink
* Represents a unit, organisation link as per the database table units_organisations_link
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package lib.groups
*/


class UnitOrganisationLink {
	
	/** primary key of object */
	var $iId;
	/** primary key of unit to which unit is linked */
	var $iUnitID;
	/** primary key of group to which group is linked */
	var $iOrgID; 
	
	
	function UnitOrganisationLink($iNewUnitID,$iNewOrgID) {
		//object not created in database yet
		$this->iId = -1;
		$this->iOrgID = $iNewOrgID;
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
	* Get the primary key of the org to which the unit is linked
	*
	* @return int primary key of org to which unit is linked
	*
	*/
	function getOrgID() {
		return $this->iOrgID;
	}
	
	/**
	* Set the primary key of the org to which the unit is linked
	*
	* @param 	int		Primary key of group to which unit is ilinked
	*
	*/
	function setOrgID($iNewValue) {
		$this->iOrgID = $iNewValue;
	}
	
	/**
	* Get the primary key of the unit to which the org is linked
	*
	* @return int primary key of unit to which the org is linked
	*
	*/
	function getUnitID() {
		return $this->iUnitID;
	}
	
	/**
	* Set the primary key of the unit to which the org is linked
	*
	* @param 	int		Primary key of unit to which the org is linked
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
			$query = "SELECT unit_id FROM $default->owl_units_organisations_table WHERE unit_id = $this->iUnitID AND organisation_id = $this->iOrgID";
    		$sql->query($query);
   			$rows = $sql->num_rows($sql);
        
  			if ($rows > 0) {
				// duplicate username
				$_SESSION["errorMessage"] = "UnitOrganisationlink::The id " . $this->iUnitID . " is already exist!";
				return false;
       		} else{
				$result = $sql->query("INSERT INTO " . $default->owl_units_organisations_table . " (unit_id,organisation_id) VALUES ($this->iUnitID,$this->iOrgID)");
				if ($result) {
					$this->iId = $sql->insert_id();
					return  true;
				}
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_units_organisations_link_table";
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
			$result = $sql->query("UPDATE " . $default->owl_units_organisations_table . " SET organisation_id = $this->iOrgID, unit_id = $this->iUnitID WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_units_organisations_table WHERE id = $this->iId");
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
	* UnitsOrganisationsLink object and populate it with the 
	* corresponding database values
	*
	* @return UnitsOrganisationsLink populated UnitsOrganisationLink object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iUnitOrganisationLinkID) {
		global $default;	
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_units_organisations_table WHERE id = $iUnitOrganisationLinkID");
		if ($result) {
			if ($sql->next_record()) {
				$oUnitOrganisationLink = & new UnitOrganisationLink($sql->f("unit_id"),$sql->f("organisation_id") );
				$oUnitOrganisationLink->iId = $iUnitOrganisationLinkID;
				return $oUnitOrganisationLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iUnitOrganisationLinkID . " table = $default->owl_units_organisations_link_table";
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
	* @return Array array of UnitOrganisationLink objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aUnitOrganisationLink;
		settype($aUnitOrganisationLink, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM "  . $default->owl_units_organisations_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oUnitOrganisationLink = & UnitOrganisationLink::get($sql->f("id"));
				$aUnitOrganisationLink[$iCount] = $oUnitOrganisationLink;
				$iCount++;
			}
			return $aUnitOrganisationLink;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
     * static function
     *
     * test to see if unit belongs to org
     *
     * @param   false or a value
     */
	function unitBelongsToOrg($unitId) {
		global $default;
		return lookupField("$default->owl_units_organisations_table", "organisation_id", "unit_id", $unitId );
	}
	
	/*
     * static function
     *
     * sets the id of the unitorg using their unit id
     *
     * @param   String
     *   The unit_ID
     *   
     */
	function setUnitOrgID($unitId) {
		global $default;
		$id = lookupID($default->owl_units_organisations_table, "unit_id",  $unitId);
		$this->iId= $id;
	}
	
	function getByUnitID($unitId) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_units_organisations_table WHERE unit_id = $unitId");
		if ($result) {		    	
			if ($sql->next_record()) {
				$oUnitOrganisationLink = & UnitOrganisationLink::get($sql->f("id"));
				return $oUnitOrganisationLink;	        		
			} else { 
				return false;
			}
		} else { 
			return false; 
		}
	}
}
?>
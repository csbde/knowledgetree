<?php

/**
* @package lib
* 
* Class DefaultLookup
*
* Represents the base class for any table in the database that ends in a _lookup
* i.e. any tables containing only an ID and name column
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*/

class DefaultLookup {
	
	/** database table name */
	var $sTableName;
	/** primary key */
	var $iId;
	/** name value */
	var $sName;
	
	function DefaultLookup($sNewTableName, $sNewName) {
		//object not stored yed
		$this->iId = -1;
		$this->sTableName = $sNewTableName;
		$this->sName = $sNewName;
	}
	
	/**
	* Get the primary key for this object
	*
	* @return int primary key for this object
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the name value for this object
	*
	* @return String name value for this object
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	/**
	* Set the name value for this object
	*
	* @param 	New name value
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/**
	* Create the object in the database
	*
	* @return boolean true on successful creation, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $lang_err_database, $lang_err_object_exists;		
		//don't create the object if it's aready been created
		if ($this->iId < 0) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO $this->sTableName (name) VALUES ( '". addslashes($this->sName) . "')");
			if ($result) {
				//set the primary key;
				$this->iId = $sql->insert_id();
				return true;
			} 
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $this->sTableName";
		return false;
	}
	
	/**
	* Update the entry in the database with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $lang_err_database, $lang_err_object_key;
		//can only update if the object has been stored
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE $this->sTableName SET name = '" . addslashes($this->sName) . "' WHERE id = $this->iId");
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
		global $lang_err_database, $lang_err_object_not_exist;
		//can only delete and object if it has been stored already
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM $this->sTableName WHERE id = $this->iId");
			if ($result) {
				//reset the primary key
				$this->iId = -1;
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
	* Given a primary key and a tablename, will create
	* the corresponding lookup object
	*
	* @param 	Database table to query
	* @param 	Primary key of object to retrieve
	*
	* @return Lookup lookup object populated with relevant values
	*/
	function & get($sTableName, $iId) {
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM $sTableName WHERE id = $iId");		
		if ($result) {
			if ($sql->next_record()) {
				$oLookup = & new DefaultLookup($sTableName, stripslashes($sql->f("name")));
				$oLookup->iId = $iId;
				return $oLookup;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iId . " table = $sTableName";			 
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;		
	}
	
}

?>

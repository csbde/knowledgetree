<?php
/**
* Class Unit
* Represents a unit as per the database table units
*
* @author Mukhtar Dharsey
* @date 28 January 2003
* @package lib.units
*/

class Unit {
	
	/** object's primary key */
	var $iId;
	/** unit's name */
	var $sName;
	
	
	function Unit($sNewName) 
	{
		//object has not been created in database yet
		$this->iId = -1;
		$this->sName = $sNewName;
		
	}
	
	/**
	* Get the object's primary key
	*
	* @return int object's primary key
	*
	*/
	function getID() 
	{
		return $this->iId;
	}
	
	/**
	* Get the unit's name
	*
	* @return String unit's name
	*
	*/
	function getName() 
	{
		return $this->sName;
	}
	
	/**
	* Set the unit's name
	*
	* @param 	String		Unit's name
	*
	*/
	function setName($sNewValue)
	 {
		$this->sName = $sNewValue;
	}
	
	
	/**
	* Create the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create()
	 {
		global $default, $lang_err_database, $lang_err_object_exists;
		//if the object hasn't been created
		if ($this->iId < 0) 
		{	//check to see if name exsits
			$sql = new Owl_DB();
			$query = "SELECT name FROM ". $default->owl_units_table ." WHERE name = '" . $this->sName . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0) 
       		 	{
            		 // duplicate username
           		 $_SESSION["errorMessage"] = "Unit::The name " . $this->sName . " is already in use!";
           		 return false;
	       		 }
       			 else
       			 {
				$result = $sql->query("INSERT INTO " . $default->owl_units_table . " (name) VALUES ('" . addslashes($this->sName) . "')");
				if ($result) 
				{
					$this->iId = $sql->insert_id();
					return  true;
				}
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
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
	function update() 
	{
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this->iId > 0) 
		{
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_units_table . " SET name = '" . addslashes($this->sName) . "' WHERE id = $this->iId");
			if ($result)
			 {
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
	function delete() 
	{
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) 
		{
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM $default->owl_units_table WHERE id = $this->iId");
			if ($result) 
			{
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
	function & get($iUnitID) 
	{
		global $default;
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM $default->owl_units_table WHERE id = $iUnitID");
		if ($result) 
		{
			if ($sql->next_record()) 
			{
				$oUnit = & new Unit(stripslashes($sql->f("name")));
				$oUnit->iId = $iUnitID;
				return $oUnit;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iUnitID . " table = $default->owl_units_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

    /*
     * static function
     *
     * gets the id of a unit using their name
     *
     * @param   String
     *   The username for which we want its ID
     *   
     */
     
	function getUnitID($name)
	 {
	       global $default;
	
	        $id = lookupID($default->owl_units_table, "name",  $name);
	        
	        $this->iId= $id;
	}
	
      /**
	* Static function
	* Get a list of web documents
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of Unit objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) 
	{
		global $default, $lang_err_database;
		$aUnitArray;
		settype($aUnitArray, "array");
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM " . $default->owl_units_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result)
		 {			
			$iCount = 0;
			while ($sql->next_record())
			 {
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

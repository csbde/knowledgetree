<?php
/**
* 
* Class Role
* Represents a role as per the roles database table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* package lib.roles
*/

class Role {
	
	/** role object primary key */
	var $iId;
	/** role name */
	var $sName;
	/** role has document read permission */
	var $bCanRead;
	/** role has document write */
	var $bCanWrite;
	
	/**
	* Default constructor
	*
	* @param 	String		Role name
	* @param 	boolean		Role has document read permission
	* @param 	boolean		Role has document write permission
	*
	*/
	function Role($sNewName, $bNewCanRead, $bNewCanWrite) {
		//object not yet created in database
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->bCanRead = $bNewCanRead;
		$this->bCanWrite = $bNewCanWrite;
	}
	
	function getName() {
		return $this->sName;
	}
	
	function getID() {
		return $this->iId;
	}
	
	function getReadable() {
		return $this->bCanRead;
	}
	
	function setReadable($bNewValue) {
		$this->bCanRead = $bNewValue;
	}
	
	function getWriteable() {
		return $this->bCanWrite;
	}
	
	function setWriteable($bNewValue) {
		$this->bCanWrite = $bNewValue;
	}
	
	function setName($bNewValue) {
		$this->sName = $bNewValue;
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
			//check to see if name exsits
			$sql = $default->db;
			$query = "SELECT name FROM ". $default->owl_roles_table ." WHERE name = '" . $this->sName . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0){
            			 // duplicate username
           			 $_SESSION["errorMessage"] = "Role::The Role name " . $this->sName . " is already in use!";
           			 return false;
	       		 }else
	      		 {
				$sql = $default->db;
				$result = $sql->query("INSERT INTO " . $default->owl_roles_table . " (name, can_read, can_write) VALUES ('" . addslashes($this->sName) . "', " . ($this->bCanRead ? 1 : 0) . ", " . ($this->bCanWrite ? 1 : 0) . ")");
				if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
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
			$result = $sql->query("UPDATE " . $default->owl_roles_table . " SET name = '" . addslashes($this->sName) . "', can_read = " . ($this->bCanRead ? 1 : 0) . ", can_write = " . ($this->bCanWrite ? 1 : 0) . " WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_roles_table WHERE id = $this->iId");
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
	* Given a roles primary key it will create a 
	* Roles object and populate it with the 
	* corresponding database values
	*
	* @return WebDocument populated WebDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iRoleID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_roles_table WHERE id = $iRoleID");
		if ($result) {
			if ($sql->next_record()) {
				$oRole = & new Role($sql->f("name"), $sql->f("can_read"), $sql->f("can_write"));
				$oRole->iId = $iRoleID;
				return $oRole;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iRoleID . " table = $default->owl_roles_table";
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
	* @return Array array of WebDocument objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aRoleArray;
		settype($aRoleArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_roles_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oRole = & Role::get($sql->f("id"));
				$aRoleArray[$iCount] = $oRole;
				$iCount++;
			}
			return $aRoleArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}



/**
* Static function
*
* Creates a roles object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & roleCreateFromArray($aParameters) {
	$oRole = & new Role($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oRole;
}

?>

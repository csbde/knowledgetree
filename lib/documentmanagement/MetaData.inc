<?php
/**
*
* Class DocumentType
*
* Represents a MetaData as per the database document_types_lookup table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package lib.documentmanagement
*/

class MetaData {
	
	/** primary key value */
	var $iId;
	//document field id
	var $iDocFieldId;
	/** MetaData name */
	var $sName;
	
	/**
	* Default constructor
	*
	* @param 	Name of MetaData
	* @param 	MetaData data type
	*
	*/
	function MetaData($iNewDocFieldID,$sNewName) {
		//object not created yet
		$this->iId = -1;
		$this->iDocFieldID = $iNewDocFieldID;
		$this->sName = $sNewName;
		
	}
	
	/**
	* Get the MetaData's primary key value
	*
	* @return int MetaData's primary key value
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the MetaData's name
	*
	* @return String MetaData's name
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	
	/**
	* Set the MetaData's name
	*
	* @param 	MetaData's new name
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	/**
	* Set the MetaData's docField
	*
	* @param 	MetaData's new name
	*
	*/
	function setDocFieldID($sNewValue) {
		$this->iDocFieldID = $sNewValue;
	}
	
	/**
	* Get the MetaData's docfield
	*
	* @return String MetaData's name
	*
	*/
	function getDocFieldID() {
		return $this->iDocFieldID;
	}
	
	
	
     /**
	* Store the current object in the database
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
			$query = "SELECT name FROM ". $default->owl_metadata_table ." WHERE name = '" . $this->sName . "' and document_field_id = '". $this->iDocFieldID . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0){
            		 	// duplicate username
           		 	$_SESSION["errorMessage"] = "MetaData::TheMetaData name " . $this->sName . " for the specfic field exists already!";
           		 	return false;
           		 
	       		 }else{
				$sql = $default->db;
				$result = $sql->query("INSERT INTO " . $default->owl_metadata_table . " (document_field_id,name) VALUES ('". $this->iDocFieldID . "','" . addslashes($this->sName) . "')");
				if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		   }
		}
		
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = document_types";
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
			$result = $sql->query("UPDATE " . $default->owl_metadata_table. " SET name = '" . addslashes($this->sName) . "' WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM " . $default->owl_metadata_table. " WHERE id = $this->iId");
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
	* Given a document_fields primary key it will create a 
	*MetaDatas object and populate it with the 
	* corresponding database values
	*
	* @returnMetaData populatedMetaData object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iMetaDataID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM ". $default->owl_metadata_table." WHERE id = $iMetaDataID");
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentType = & new MetaData($sql->f("document_field_id"),stripslashes($sql->f("name")));
				$oDocumentType->iId = $sql->f("id");
				return $oDocumentType;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = document_types";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
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
     
	function setMetaDataID($iDocFieldID, $sMetaDataName)
	 {
	   	global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT id FROM $default->owl_metadata_table WHERE document_field_id = $iDocFieldID and name = '" . $sMetaDataName . "' ");
		if ($result) {
			if ($sql->next_record()) {
				$id = $sql->f("id");
				
			}else{
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
		
		}else{	
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		
		$this->iId = $id;
		
	}

}
/**
* Static function
*
* Creates a MetaData object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & metadataCreateFromArray($aParameters) {
	$oMetaData = & new MetaData($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oMetaData;
}

?>

<?php
/**
*
* Class DocumentType
*
* Represents a document type as per the database document_types_lookup table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package lib.documentmanagement
*/

class DocumentType {
	
	/** primary key value */
	var $iId;
	/** document type name */
	var $sName;
	
	/**
	* Default constructor
	*
	* @param 	Name of document type
	* @param 	document type data type
	*
	*/
	function DocumentType($sNewName) {
		//object not created yet
		$this->iId = -1;
		$this->sName = $sNewName;
		
	}
	
	/**
	* Get the document type's primary key value
	*
	* @return int document type's primary key value
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the document type's name
	*
	* @return String document type's name
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	/**
	* Set the document type's name
	*
	* @param 	document type's new name
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
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
			$query = "SELECT name FROM ". $default->owl_document_types_table ." WHERE name = '" . $this->sName . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0){
            		 	// duplicate username
           		 	$_SESSION["errorMessage"] = "DocTypes::The DocumentType name " . $this->sName . " is already in use!";
           		 	return false;
           		 
	       		 }else{
				$sql = $default->db;
				$result = $sql->query("INSERT INTO " . $default->owl_document_types_table . " (name) VALUES ('" . addslashes($this->sName) . "')");
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
			$result = $sql->query("UPDATE " . $default->owl_document_types_table . " SET name = '" . addslashes($this->sName) . "' WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM " . $default->owl_document_types_table . " WHERE id = $this->iId");
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
	* DocumentTypes object and populate it with the 
	* corresponding database values
	*
	* @return DocumentType populated DocumentType object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDocumentTypeID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM ". $default->owl_document_types_table ." WHERE id = $iDocumentTypeID");
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentType = & new DocumentType(stripslashes($sql->f("name")));
				$oDocumentType->iId = $sql->f("id");
				return $oDocumentType;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = document_types";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}


/**
* Static function
*
* Creates a document type object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & documenttypeCreateFromArray($aParameters) {
	$oDocType = & new DocumentType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocType;
}

?>

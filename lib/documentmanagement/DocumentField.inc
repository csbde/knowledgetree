<?php
/**
*
* Class DocumentField
*
* Represents a document field as per the database document_fields table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package lib.documentmanagement
*/

class DocumentField {
	
	/** primary key value */
	var $iId;
	/** document field name */
	var $sName;
	/** document field data type */
	var $sDataType;
	
	/**
	* Default constructor
	*
	* @param 	Name of document field
	* @param 	Document field data type
	*
	*/
	function DocumentField($sNewName, $sNewDataType) {
		//object not created yet
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sDataType = $sNewDataType;
	}
	
	/**
	* Get the document field's primary key value
	*
	* @return int document field's primary key value
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the document field's name
	*
	* @return String document field's name
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	/**
	* Set the document field's name
	*
	* @param 	Document field's new name
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/**
	* Get the document field's data type
	*
	* @return String document field's data type
	*
	*/
	function getDataType() {
		return $this->sDataType;
	}
	
	/**
	* Set the document field's data type
	*
	* @param 	Document field's new data type
	*
	*/
	function setDataType($sNewValue) {
		$this->sDataType = $sNewValue;
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
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_fields_table . " (name, data_type) VALUES ('" . addslashes($this->sName) . "', '" . addslashes($this->sDataType) . "')");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
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
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_fields_table . " SET name = '" . addslashes($this->sName) . "', data_type = '" . addslashes($this->sDataType) . "' WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_fields_table WHERE id = $this->iId");
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
	* DocumentFields object and populate it with the 
	* corresponding database values
	*
	* @return DocumentField populated DocumentField object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDocumentFieldsID) {
		global $default;
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM $default->owl_fields_table WHERE id = $iDocumentFieldsID");
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentField = & new DocumentField(stripslashes($sql->f("name")), stripslashes($sql->f("data_type")));
				$oDocumentField->iId = $sql->f("id");
				return $oDocumentField;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = $default->owl_fields_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}

?>

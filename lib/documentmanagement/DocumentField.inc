<?php
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
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
	/** is_generic */
	var $bIsGeneric;
	/**has lookup*/
	var $bHasLookup;
	
	/**
	* Default constructor
	*
	* @param 	Name of document field
	* @param 	Document field data type
	*
	*/
	function DocumentField($sNewName, $sNewDataType, $bNewIsGeneric, $bNewHasLookup) {
		//object not created yet
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sDataType = $sNewDataType;
		$this->bIsGeneric = $bNewIsGeneric;
		$this->bHasLookup = $bNewHasLookup;
		
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
	* Get the document field's generic or not
	*
	* @return String document field's data type
	*
	*/
	function getIsGeneric() {
		return $this->bIsGeneric;
	}
	
	/**
	* Set the document field's genericness
	*
	* @param 	Document field's new data type
	*
	*/
	function setIsGeneric($sNewValue) {
		$this->bIsGeneric = $sNewValue;
	}
	
	
		
	/**
	* Get the document field's lookup or not
	*
	* @return String document field's data type
	*
	*/
	function getHasLookup() {
		return $this->bHasLookup;
	}
	
	/**
	* Set the document field's lookup
	*
	* @param 	Document field's new data type
	*
	*/
	function setHasLookup($sNewValue) {
		$this->bHasLookup = $sNewValue;
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
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_fields_table . " (name, data_type,is_generic,has_lookup) VALUES ('" . addslashes($this->sName) . "', '" . addslashes($this->sDataType) . "', '" . $this->bIsGeneric . "', '" . $this->bHasLookup ."')");
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
			$sql = $default->db;
			$result = $sql->query("UPDATE " . $default->owl_fields_table . " SET name = '" . addslashes($this->sName) . "', data_type = '" . addslashes($this->sDataType) . "', is_generic = '" . $this->bIsGeneric . "', has_lookup = '" . $this->bHasLookup . "' WHERE id = $this->iId");
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
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_fields_table WHERE id = $iDocumentFieldsID");
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentField = & new DocumentField(stripslashes($sql->f("name")), stripslashes($sql->f("data_type")), $sql->f("is_generic"), $sql->f("has_lookup"));
				$oDocumentField->iId = $sql->f("id");
				return $oDocumentField;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = $default->owl_fields_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
 	 * Static- Get a list of document fieldss;
	 *
	 * @param 	String		Where clause (not required)
	 *
	 * @return Array array of DocumentField objects, false otherwise
	*/
	function getList($sWhereClause = null) {
		global $default;
		$aDocumentFieldArray = array();
		$sql = $default->db;
		$sQuery = "SELECT id FROM " . $default->owl_fields_table  . (isset($sWhereClause) ? " WHERE $sWhereClause" : "");
		$result = $sql->query($sQuery);
		if ($result) {			
			while ($sql->next_record()) {
				$aDocumentFieldArray[] = & DocumentField::get($sql->f("id"));
			}
			return $aDocumentFieldArray;
		}
		return false;
	}	

	/**
	 * Returns the DocumentTypes mapped to this document field
	 */
	function getDocumentTypes() {
		global $default;
		$aDocumentTypes = array();
		$sql = $default->db;
		$query = "SELECT document_type_id FROM " . $default->owl_document_type_fields_table  . " WHERE field_id = $this->iId";
		if ($sql->query($query)) {
			while ($sql->next_record()) {
      			$aDocumentTypes[] = & DocumentType::get($sql->f("document_type_id"));
      		}
			return $aDocumentTypes;
		} else{
			return false;
		}
	}
	
	/** 
	 * Given a documenttype...will find all fields belonging to it
 	 *
	 * @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	 */
	function isFieldLinkedToType() {
		global $default;
		$sql = $default->db;
		$query = "SELECT document_type_id FROM " . $default->owl_document_type_fields_table  . " WHERE field_id = $this->iId";
		if ($sql->query($query)) {
			$rows = $sql->num_rows($sql);
	      	if ($rows > 0) {
	      		// instantiate the document types and return
				return true;
			} else{
				return false;
			}
		} else {
			return false;
		}
	}

	// FIXME: add a data type class!	
	/* Static function.  Given a documenttype...will find all fields belongin to it
	*
	* @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getAllDataTypes() {
		global $default, $lang_err_database;
		$aDataTypes;
		settype($aDataTypes, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT id, name FROM " . $default->owl_data_types_table );
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				
				$aDataTypes[$iCount]["id"] = $sql->f("id");
				$aDataTypes[$iCount]["name"] = $sql->f("name");
				$iCount++;
			}
			return $aDataTypes;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	function getLookupCount($iDocumentFieldID){
		global $default;
			
		$sql = $default->db;
		$result = $sql->query("SELECT COUNT(*) AS count FROM " . $default->owl_metadata_table . " WHERE document_field_id = " . $iDocumentFieldID);
		if ($result) {			
			if ($sql->next_record()) {		
				return $sql->f("count");
			}
		}
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
function & documentfieldCreateFromArray($aParameters) {
	$oDocField = & new DocumentField($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocField;
}


?>
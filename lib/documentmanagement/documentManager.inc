<?php

/**
* Class DocumentManager
*
* Contains all functions required for document management,
* such as the creation/deletion/removal of document types.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 8 January 2003
*
* @todo Complete updateDocumentType() function
* 
*/

require_once ("$default->owl_root_url/lib/owl.lib.php");

class DocumentManager {
	
	/** 
	* Create a new document type
	* 
	* @param $sName		Name of new document type
	*
	* @return true on successful creation, false otherwise and set $default->errorMessage 
	*/
	function createDocumentType($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		//if the document type doesn't exist
		if (!($this->documentTypeExists($sName))) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO  " . $default->owl_document_types_table . "  (name) values ('" . $sName . "')");
			if (!$result) {
				$default->errorMessage = "Database Error. Failed to insert document type " . $sName;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type with this name already exists";
		return false;
	}
	
	/**
	* Delete an existing document type
	*
	* @param $sName		Name of document type to delete
	* 
	* @return true on successful removal, false otherwise and set $default->errorMessage
	*/
	function deleteDocumentType($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		//only remove the document type if it exists
		if ($this->documentTypeExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_document_types_table . "  WHERE name = '" . $sName . "'");
			if (!$result) {
				$default->errorMessage = "Database Error.  Failed to delete document type " . $sName;
				return false;
			}			
			return true;
		}
		$default->errorMessage = "There is no document type with this name";
		return false;		
	}

	/**
	* Update a document type
	* 
	* @param $iDocumentTypeID	Primary key of document type to updatee
	* @param $sName				New document type name
	*
	* @return true on successful update, false otherwise and set $default->errorMessage
	*/
	function updateDocumentType($iDocumentTypeID, $sName) {
		
	}
	
	
	/**
	* Get the primary key for a document type
	*
	* @param $sName		Name of document type to get ID for
	*
	* @return int document type id if the document type exists, false otherwise
	*/
	function & getDocumentTypeID($sName) {
		global $lang_err_document_type_field_does_not_exist, $default;
		//escape special characters that may interfere with the db query
		$sName = addslashes($sName);
		
		if ($this->documentTypeExists($sName)) {
			$sql = new Owl_DB();
			$sql->query("SELECT ID FROM " .  $default->owl_document_types_table . " WHERE name = '" . $sName . "'");
			$sql->next_record();
			return $sql->f("ID");
		}
		$default->errorMessage = $lang_err_document_type_does_not_exist . $sName;
		return false;
	}
	
	/**
	* Checks to see if a document type with a given name
	* already exists
	*
	* @param $sName		Name of document type to check
	*
	* @return true if the document type exists, false otherwise
	*/
	function documentTypeExists($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_document_types_table . "  WHERE Name = '" . $sName . "'");		
		return $sql->next_record();
	}
	
	/**
	* Link a document type field with a specific document type
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document field type
	* @param $bIsMandatory			Whether or not the field is mandatory
	*
	* @return true on successful creation, false otherwise and set $default->errorMessage
	*/
	function createDocumentTypeFieldLink($iDocumentTypeID, $iDocumentTypeFieldID, $bIsMandatory) {
		//Get hold of the global error string
		global $default;
		//if the document field type is not associated with the document
		if (!($this->documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID))) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO  " . $default->owl_document_type_fields_table . "  (document_type_id, field_id, is_mandatory) VALUES (" . $iDocumentTypeID . ", " . $iDocumentTypeFieldID . ", " . $bIsMandatory . ")");
			if (!$result) {
				$default->errorMessage =  "Database Error.  Failed to insert document field type with ID " . $iDocumentTypeFieldID;
				return false;
			}
			return true;
		}
		$default->errorMessage = "This field type is already linked to this document type";
		return false;
	}
	
	/**
	* Delete the link between a document type field and a specific document type
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document type field
	*/
	function deleteDocumentTypeFieldLink($iDocumentTypeID, $iDocumentTypeFieldID) {
		global $default;
		//Get hold of the global error string
		global $default;
		if ($this->documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_document_type_fields_table . "  where document_type_id = " . $iDocumentTypeID . " AND field_id = " . $iDocumentTypeFieldID);
			if (!result) {
				$default->errorMessage = "Database Error. Failed to deleted document type field with document_type_id " . $iDocumentTypeID . " and field_id " . $iDocumentTypeFieldID;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A dcoument field type with document_type_id " . $iDocumentTypeID . " and a document_id " . $iDocumentTypeID . " does not exist";
		return false;		
	}
	
	
	/**
	* Checks to see if the given document type field is already linked to the given
	* document type.
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document field type
	*
	* @return true is the document field type is linked to the document type, false otherwise
	*/
	function documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM  " . $default->owl_document_type_fields_table . "  WHERE document_type_id = " . $iDocumentTypeID . " AND field_id = " . $iDocumentTypeFieldID);
		return $sql->next_record();
	}
	
	/**
	* Creates a document type field
	*
	* @param $sName		Document type field name
	* @param $sDataType	Field data type
	*
	* @return true on successful insertion, false otherwise and sets $default->errorMessage
	*/
	function createDocumentTypeField($sName, $sDataType) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);		
		$sDataType = addslashes($sDataType);
		//Get hold of the global error string
		global $default;
		if (!$this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_fields_table . " (name, data_type) VALUES ('" . $sName . "', '" . $sDataType . "')");			
			if (!$result) {
				$default->errorMessage = "Database Error.  Could not insert document field " . $sName . " with data type " . $sDataType . " into table " . $default->owl_fields_table;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type field with this name already exists";
		return false;
	}
	
	/**
	* Deletes a document type field
	*
	* @param $sName		Name of document field type to delete
	*
	* @return true on successful deletion, false otherwise and set $default->errorMessage
	*/
	function deleteDocumentTypeField($sName) {
		//escape any characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		if ($this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM " . $default->owl_fields_table . " WHERE Name = '" . $sName . "'");
			if (!$result) {
				$default->errorMessage = "Database Error.  Could not delete document type field " . $sName . " from table " . $default->owl_field_table;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type field with the name " . $sName . " does not exist";
		return false;
	}
	
	/**
	* Get the primary key for a document type field
	*
	* @param $sName		Document type field name to get primary key for
	*
	* @return ID if document type field exists, false otherwise and sets $default->errorMessage
	*/
	function & getDocumentTypeFieldID($sName) {
		global $lang_err_document_type_field_does_not_exist, $default;
		$sName = addslashes($sName);
		if ($this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$sql->query("SELECT id FROM " . $default->owl_fields_table . " WHERE name = '" . $sName . "'");
			$sql->next_record();
			return $sql->f("id");
		}
		$default->errorMessage = $lang_err_document_type_field_does_not_exist . $sName;
		return false;
	}
	
	/**
	* Checks whether a document type field exists
	*
	* @param $sName		Document type field name to check
	*
	* @return true if document type field exists, false otherwise
	*/
	function documentTypeFieldExists($sName) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_fields_table . " WHERE name = '" . $sName . "'");
		return $sql->next_record();
	}
	
	
	
}
?>

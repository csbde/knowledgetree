<?php

/**
* Class DocumentFieldLink
*
* Represents a document field link as per the database table document_fields_link
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*/

class DocumentFieldLink {
	
	/** document field link primary key */
	var $iId;
	/** primary key of document to which field is linked */
	var $iDocumentID;
	/** primary key of field to which document is linked */
	var $iDocumentFieldID;
	/** field value */
	var $sValue;
	
	
	/**
	* Default constructor 
	*
	* @param 	Primary key of document to which field is linked
	* @param 	Primary key of field to which document is linked
	* @param 	Value of field
	*
	*/
	function DocumentFieldLink($iNewDocumentID, $iNewDocumentFieldID, $sNewValue) {
		//object not create in database yet
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iDocumentFieldID = $iNewDocumentFieldID;
		$this->sValue = $sNewValue;
	}
	
	/**
	* Get the document field link primary key
	*
	*  @return int document field link primary key
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the primary key of the document to which the field is linked
	*
	* @return int document primary key to which the field is linked
	*
	*/
	function getDocumentID() {
		return $this->iDocumentID;
	}
	
	/**
	* Set the primary key of the document to which the field is linked
	*
	* @param 	Document primary key to which field is linked
	*
	*/
	function setDocumentID($iNewValue) {
		$this->iDocumentID = $iNewValue;
	}
	
	/**
	* Get the primary key of the field to which the document is linked
	*
	* @return int primary key of field to which the document is related
	*
	*/
	function getDocumentFieldID() {
		return $this->iDocumentFieldID;
	}
	
	/**
	* Set the primary key of the field to which the document is related
	*
	* @param 	New primary key of field to which document is related
	*
	*/
	function setDocumentFieldID($iNewVale) {
		$this->iDocumentFieldID = $iNewValue;
	}
	
	/**
	* Get the value of the field
	*
	* @return String value of the field
	*
	*/
	function getValue() {
		return $this->sValue;
	}
	
	/**
	* Set the value of the field
	*
	* @param 	New value of the field
	*
	*/
	function setValue($sNewValue) {
		$this->sValue = $sNewValue;
	}
	
	/**
	* Insert the current document into the database
	*
	* @return boolean true on successful insert, false otherwise and set $_SESSION["errorMessage"]
	*/
	function create() {
		global $default, $lang_err_doc_exist, $lang_err_database;
		//if the id >= 0, then the object has already been created
		if ($this->iId < 0) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_document_fields_table . " (document_id, document_field_id, value) " .
						"VALUES ($this->iDocumentID, $this->iDocumentFieldID, '" . addslashes($this->sValue) . "')");
			if ($result) {
				//set the current documents primary key
				$this->iId = $sql->insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;			
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_document_fields_table";
		return false;
		
	}
	
	/**
	* Update the documents current values in the database
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_document_fields_table . " SET " .
					"document_id = $this->iDocumentID, document_field_id = $this->iDocumentFieldID, value = '" . addslashes($this->sValue) . "'" .
					"WHERE id = $this->iId");
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
	* Delete the current document from the database.  Set the primary key to -1 
	* on successful deletion
	*
	* @return boolean true and reset id to -1 on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*/
	function delete() {	
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_document_fields_table . "  WHERE id = $this->iId");
			if ($result) {
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
	*
	* Static function.  Given a document fields link primary key will create
	* a DocumentFieldLink object and populate it with the corresponding
	* database values
	*
	* @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDocumentFieldLinkID) {
		global $default, $lang_err_doc_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_document_fields_table . " WHERE id = " . $iDocumentFieldLinkID);
		if ($sql->next_record()) {			
			$oDocumentFieldLink = & new DocumentFieldLink($sql->f("document_id"), $sql->f("document_field_id"), $sql->f("value"));		
			$oDocumentFieldLink->iId = $iDocumentFieldLinkID;
			return $oDocumentFieldLink;
		}
		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = $default->owl_document_fields_table";
		return false;		
	}
}
?>

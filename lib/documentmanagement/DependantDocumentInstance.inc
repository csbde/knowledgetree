<?php 
/**
*
* Class DependantDocumentInstance
*
* Represents a dependant document as per the database table dependant_document
* Used to chain documents together in the collaboration process
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 14 May 2003
* @package lib.documentmanagement
*/


class DependantDocumentInstance {

	/** primary key value */
	var $iId;
	/** primary key of user responsible for creating document */
	var $iUserID;
	/** document title name */	
	var $sDocumentTitle;
	/** primary key of document to use as template */
	var $iTemplateDocumentID;
	/** parent document to which this document will be linked */
	var $iParentDocumentID;	

	/**
	* Default constructor
	*
	* @param 	Name of document field
	* @param 	Document field data type
	*
	*/
	function DependantDocumentInstance($sNewDocumentTitle, $iNewUserID, $iNewTemplateDocumentID, $iNewParentDocumentID) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->sDocumentTitle = $sNewDocumentTitle;
		$this->iUserID = $iNewUserID;
		$this->iTemplateDocumentID = $iNewTemplateDocumentID;
		$this->iParentDocumentID = $iNewParentDocumentID;
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
	* Get the primary key of the user responsbile for new document creation
	*/
	function getUserID() {
		return $this->iUserID;
	}

	/**
	* Set the document field's name
	*
	* @param 	Document field's new name
	*
	*/
	function setDocumentTitle($sNewValue) {
		$this -> sDocumentTitle = $sNewValue;
	}

	/**
	* Get the depedant document's title
	*
	* @return String dependant document's title	*
	*/
	function getDocumentTitle() {
		return $this->sDocumentTitle;
	}

	
	/**
	* Get the primary key of the template document
	*
	* @return int Primary key of template document
	*
	*/
	function getTemplateDocumentID() {
		return $this -> iTemplateDocumentID;
	}

	/**
	* Set the template document's primary key
	*
	* @param 	Template document's primary key
	*
	*/
	function setHasLookup($sNewValue) {
		$this -> iTemplateDocumentID = $sNewValue;
	}
	
	function getParentDocumentID() {
		return $this->iParentDocumentID;
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
		if ($this -> iId < 0) {			
			$sql = $default -> db;
			$result = $sql -> query("INSERT INTO $default->owl_dependant_document_instance_table (document_title, user_id,template_document_id, parent_document_id) VALUES ('" . addslashes($this->sDocumentTitle) . "', $this->iUserID, " . (isset($this->iTemplateDocumentID) ? "$this->iTemplateDocumentID" : "NULL") . ", " . $this->iParentDocumentID . ")");
			if ($result) {
				$this -> iId = $sql -> insert_id();
				return true;
			}			
			$_SESSION["errorMessage"] = $lang_err_database;			
			return false;
		}		
		$_SESSION["errorMessage"] = $lang_err_object_exists."id = ".$this -> iId." table = dependant_document_instance";		
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
		if ($this -> iId > 0) {
			$sql = $default -> db;
			$result = $sql -> query("UPDATE $default -> owl_dependant_documents SET document_title = '".addslashes($this -> sDocumentTitle)."', user_id = $this->iUserID, template_document_id = $this->iTemplateDocumentID, parent_document_id = $this->iParentDocumentID WHERE id = $this->iId");
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
		if ($this -> iId >= 0) {
			$sql = $default -> db;
			$result = $sql -> query("DELETE FROM $default->owl_dependant_document_instance_table WHERE id = $this->iId");
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
	* Given a dependant_documents primary key it will create a 
	* DependantDocument object and populate it with the 
	* corresponding database values
	*
	* @return DependantDocument populated DependantDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDependantDocumentID) {
		global $default;
		$sql = $default -> db;
		$result = $sql -> query("SELECT * FROM $default->owl_dependant_document_instance_table WHERE id = $iDependantDocumentID");
		if ($result) {
			if ($sql -> next_record()) {
				$oDependantDocument = & new DependantDocumentInstance(stripslashes($sql -> f("document_title")), $sql -> f("user_id"), $sql -> f("template_document_id"), $sql->f("parent_document_id"));
				$oDependantDocument -> iId = $sql -> f("id");
				return $oDependantDocument;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist."id = ".$iDependantDocumentID." table = $default->owl_dependant_document_instance_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}

?>
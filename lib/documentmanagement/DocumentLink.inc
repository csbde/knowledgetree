<?php 
/**
*
* Class DocumentLink
*
* Represents a document link as per the database document_link table
* Links a child document to a parent document.  Direction of relationship
* is defined through document collaboration
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 14 May 2003
* @package lib.documentmanagement
*/


class DocumentLink {

	/** primary key value */
	var $iId;
	/** primary key of user responsible for creating document */
	var $iParentDocumentID;
	/** document title name */	
	var $iChildDocumentID;
	

	/**
	* Default constructor
	*
	* @param 	Parent document primary key
	* @param 	Child document primary key
	*
	*/
	function DocumentLink($iNewParentDocumentID, $iNewChildDocumentID) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->iParentDocumentID = $iNewParentDocumentID;
		$this->iChildDocumentID = $iNewChildDocumentID;
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
	* Get the primary key of the parent document
	*/
	function getParentDocumentID() {
		return $this->iParentDocumentID;
	}

	/**
	* Set the primary key of the parent document
	*
	* @param 	Primary key of parent document
	*
	*/
	function setParentDocumentID($iNewValue) {
		$this -> iParentDocumentID = $iNewValue;
	}

	/**
	* Get the child document's primary key
	*
	* @return int primary key of child document
	*
	*/
	function getChildDocumentID() {
		return $this->iChildDocumentID;
	}

	/**
	* Set the child document's primary key
	*
	* @param 	Primary key of child document
	*
	*/
	function setChildDocumentID($iNewValue) {
		$this -> iChildDocumentID = $iNewValue;
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
			$result = $sql -> query("INSERT INTO " . $default -> owl_document_link_table . " (parent_document_id, child_document_id) VALUES ($this->iParentDocumentID, $this->iChildDocumentID)");
			if ($result) {
				$this -> iId = $sql -> insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists."id = ".$this -> iId." table = document_fields";
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
			$result = $sql -> query("UPDATE ".$default -> owl_document_link_table." SET parent_document_id = $this->iParentDocumentID, child_document_id = $this->iChildDocumentID WHERE id = $this->iId");
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
			$result = $sql -> query("DELETE FROM $default->owl_document_link_table WHERE id = $this->iId");
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
	function & get($iDocumentLinkID) {
		global $default;
		$sql = $default -> db;
		$result = $sql -> query("SELECT * FROM $default->owl_document_link_table WHERE id = $iDocumentLinkID");
		if ($result) {
			if ($sql -> next_record()) {
				$oDocumentLink = & new DocumentLink($sql -> f("parent_document_id"), $sql -> f("parent_document_id"));
				$oDocumentLink -> iId = $sql -> f("id");
				return $oDocumentLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist."id = ".$iDocumentLinkID." table = $default->owl_document_link_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}
?>
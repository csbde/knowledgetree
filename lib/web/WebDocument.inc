<?php

DEFINE("PENDING",1);
DEFINE("PUBLISHED", 2);
DEFINE("NOT_PUBLISHED",3);

/**
* 
* Class Web Documents
*
* Represents a web document as per the web_documents table in the database
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package lib.web
*/

class WebDocument {
	
	/** primary key of web document */
	var $iId;
	/** primary key of document to which this web document related*/
	var $iDocumentID;
	/** primary key of web site on which document is published */
	var $iWebSiteID;
	/** unit to which this document belongs */
	var $iUnitID;
	/** status of document */
	var $iStatusID;
	
	var $dDateTime;
	
	function WebDocument($iNewDocumentID, $iNewWebSiteID, $iNewUnitID, $iNewStatusID, $dNewDateTime) {
		//object not yet created in database
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iWebSiteID = $iNewWebSiteID;
		$this->iUnitID = $iNewUnitID;
		$this->iStatusID = $iNewStatusID;
		$this->dDateTime = $dNewDateTime;
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
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_web_documents_table . " (document_id, web_site_id, unit_id, status_id, datetime) VALUES ($this->iDocumentID, $this->iWebSiteID, $this->iUnitID, $this->iStatusID, '$this->dDateTime')");
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
			$result = $sql->query("UPDATE " . $default->owl_web_documents_table . " SET document_id = $this->iDocumentID, web_site_id = $this->iWebSiteID, unit_id = $this->iUnitID, status_id = $this->iStatusID, datetime = '$this->dDateTime' WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_web_documents_table WHERE id = $this->iId");
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
	* Given a web_documents primary key it will create a 
	* WebDocuments object and populate it with the 
	* corresponding database values
	*
	* @return WebDocument populated WebDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iWebDocumentsID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_web_documents_table WHERE id = $iWebDocumentsID");
		if ($result) {
			if ($sql->next_record()) {
				$oWebDocument = & new WebDocument($sql->f("document_id"), $sql->f("web_site_id"), $sql->f("unit_id"), $sql->f("status_id"), $sql->f("datetime"));
				$oWebDocument->iId = $iWebDocumentsID;
				return $oWebDocument;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iWebDocumentsID . " table = $default->owl_web_documents_table";
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
		$aWebDocumentArray;
		settype($aWebDocumentArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_web_documents_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oWebDocument = & WebDocument::get($sql->f("id"));
				$aWebDocumentArray[$iCount] = $oWebDocument;
				$iCount++;
			}
			return $aWebDocumentArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>

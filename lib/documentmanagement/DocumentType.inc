<?php
/**
 * $Id$
 *
 * Represents a document type as per the database document_types_lookup table.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
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
			$query = "SELECT name FROM ". $default->document_types_table ." WHERE name = '" . $this->sName . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0){
            		 	// duplicate username
           		 	$_SESSION["errorMessage"] = "DocTypes::The DocumentType name " . $this->sName . " is already in use!";
           		 	return false;
           		 
	       		 }else{
				$sql = $default->db;
				$result = $sql->query("INSERT INTO " . $default->document_types_table . " (name) VALUES ('" . addslashes($this->sName) . "')");
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
			$result = $sql->query("UPDATE " . $default->document_types_table . " SET name = '" . addslashes($this->sName) . "' WHERE id = $this->iId");
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
		if ($this->iId > 0) {
			//check to see if name exsits
			$sql = $default->db;
			$query = "SELECT folder_id FROM ". $default->folder_doctypes_table ." WHERE document_type_id = '" . $this->iId . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0){
            		 	// duplicate username
           		 	$_SESSION["errorMessage"] = "DocTypes::Assigned to DocFolder name " . $this->iId . " is already exists!";
           		 	return false;
           		 
	       		 }else{
				$sql = $default->db;
				$result = $sql->query("DELETE FROM " . $default->document_types_table . " WHERE id = '" . $this->iId . "'");
				if ($result) {
					return true;
				}
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
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
	function deleteAllFieldLinks() {	
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM  " . $default->document_type_fields_table . "  WHERE document_type_id = $this->iId");
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
		$result = $sql->query("SELECT * FROM ". $default->document_types_table ." WHERE id = $iDocumentTypeID");
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
	
	/**
 	 * Static- Get a list document types;
	 *
	 * @param 	String		Where clause (not required)
	 *
	 * @return Array array of DocumentType objects, false otherwise
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aDocumentTypeArray = array();
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->document_types_table  . (isset($sWhereClause) ? " WHERE $sWhereClause" : ""));
		if ($result) {			
			while ($sql->next_record()) {
				$aDocumentTypeArray[] = & DocumentType::get($sql->f("id"));
			}
			return $aDocumentTypeArray;
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
function & documenttypeCreateFromArray($aParameters) {
	$oDocType = & new DocumentType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocType;
}

?>

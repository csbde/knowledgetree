<?php
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
/**
 * $Id$
 *
 * Represents a document field as per the database document_fields table.
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
class DocumentField extends KTEntity {
	
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

    function _fieldValues () {
        return array(
            'name' => $this->sName,
            'data_type' => $this->sDataType,
            'is_generic' => KTUtil::anyToBool($this->bIsGeneric),
            'has_lookup' => KTUtil::anyToBool($this->bHasLookup),
        );
    }

    function _table () {
        global $default;
        return $default->document_fields_table;
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
		$result = $sql->query(array("SELECT * FROM $default->document_fields_table WHERE id = ?", $iDocumentFieldsID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentField = & new DocumentField($sql->f("name"), $sql->f("data_type"), $sql->f("is_generic"), $sql->f("has_lookup"));
				$oDocumentField->iId = $sql->f("id");
				return $oDocumentField;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = $default->document_fields_table";
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
            return KTEntityUtil::getList(DocumentField::_table(), 'DocumentField', $sWhereClause);
	}	

	/**
	 * Returns the DocumentTypes mapped to this document field
	 */
	function getDocumentTypes() {
		global $default;
		$aDocumentTypes = array();
		$sql = $default->db;
		$sQuery = "SELECT document_type_id FROM " . $default->document_type_fields_table  . " WHERE field_id = ?";/*ok*/
        $aParams = array($this->iId);
		if ($sql->query(array($sQuery, $aParams))) {
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
		$sQuery = "SELECT document_type_id FROM " . $default->document_type_fields_table  . " WHERE field_id = ?";/*ok*/
        $aParams = array($this->iId);
		if ($sql->query(array($sQuery, $aParams))) {
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
		$result = $sql->query("SELECT id, name FROM " . $default->data_types_table );/*ok*/
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
		$result = $sql->query(array("SELECT COUNT(*) AS count FROM " . $default->metadata_table . " WHERE document_field_id = ?", $iDocumentFieldID));/*ok*/
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

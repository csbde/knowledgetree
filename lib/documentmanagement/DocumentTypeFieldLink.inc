<?php
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
/**
 * $Id$
 *
 * Represents a document type field link as per the database table document_types_fields_link
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
class DocumentTypeFieldLink {
	
	/** document field link primary key */
	var $iId;
	/** primary key of document to which field is linked */
	var $iDocumentTypeID;
	/** primary key of field to which document is linked */
	var $iFieldID;
	/** field value */
	var $bIsMandatory;
	
	
	/**
	* Default constructor 
	*
	* @param 	Primary key of document to which field is linked
	* @param 	Primary key of field to which document is linked
	* @param 	Value of field
	*
	*/
	function DocumentTypeFieldLink($iNewDocumentTypeID, $iNewFieldID, $bNewIsMandatory) {
		//object not create in database yet
		$this->iId = -1;
		$this->iDocumentTypeID = $iNewDocumentTypeID;
		$this->iFieldID = $iNewFieldID;
		$this->bIsMandatory = $bNewIsMandatory;
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
	function getDocumentTypeID() {
		return $this->iDocumentTypeID;
	}
	
	/**
	* Set the primary key of the document to which the field is linked
	*
	* @param 	Document primary key to which field is linked
	*
	*/
	function setDocumentTypeID($iNewValue) {
		$this->iDocumentTypeID = $iNewValue;
	}
	
	/**
	* Get the primary key of the field to which the document is linked
	*
	* @return int primary key of field to which the document is related
	*
	*/
	function getFieldID() {
		return $this->iFieldID;
	}
	
	/**
	* Set the primary key of the field to which the document is related
	*
	* @param 	New primary key of field to which document is related
	*
	*/
	function setFieldID($iNewVale) {
		$this->iFieldID = $iNewValue;
	}
	
	/**
	* Get the value of the field
	*
	* @return String value of the field
	*
	*/
	function getIsMandatory() {
		return $this->bIsMandatory;
	}
	
	/**
	* Set the value of the field
	*
	* @param 	New value of the field
	*
	*/
	function setIsMandatory($bNewValue) {
		$this->bIsMandatory = $bNewValue;
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
			//check to see if name exsits
			$sql = $default->db;
			$query = "SELECT id FROM ". $default->document_type_fields_table ." WHERE document_type_id = '" . $this->iDocumentTypeID . "' and field_id = '". $this->iFieldID . "'";
            $sql->query($query);
            $rows = $sql->num_rows($sql);
        
             if ($rows > 0){
                $_SESSION["errorMessage"] = "DocTypes::The DocumentType name " . $this->sName . " is already in use!";
                return false;
            }

            $sTable = $default->document_type_fields_table;
            $aFieldValues = array(
                'document_type_id' => $this->iDocumentTypeID,
                'field_id' => $this->iFieldID,
                'is_mandatory' => $this->bIsMandatory,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->document_type_fields_table";
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
			$sql = $default->db;
			$sQuery = "UPDATE " . $default->document_type_fields_table . " SET " .
					"document_type_id = $this->iDocumentTypeID, field_id = $this->iFieldID, is_mandatory = '" . $this->bIsMandatory . "'" .
					"WHERE id = $this->iId";
			$result = $sql->query($sQuery);
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
			$sql = $default->db;
			$result = $sql->query("DELETE FROM  " . $default->document_type_fields_table . "  WHERE id = $this->iId");
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
	function & get($iDocumentTypeFieldLinkID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT * FROM " . $default->document_type_fields_table . " WHERE id = " . $iDocumentTypeFieldLinkID);
		if ($sql->next_record()) {			
			$oDocumentTypeFieldLink = & new DocumentTypeFieldLink($sql->f("document_type_id"), $sql->f("field_id"), $sql->f("is_mandatory"));		
			$oDocumentTypeFieldLink->iId = $iDocumentTypeFieldLinkID;
			return $oDocumentTypeFieldLink;
		}
		return false;		
	}
	
	/**
     * Static- Lookup a document field associated with a document type id
     *
     * @param integer the document type id
     * @return integer a document field id  
     */
	function docTypeBelongsToField($iDocTypeID) {
		global $default;
		return lookupField("$default->document_type_fields_table", "field_id", "document_type_id", $iDocTypeID );
	}

	/**
     * Static- Lookup the DocumentTypeFieldLink by document type and document field ids
     *
     * @param integer the document type id
	 * @param integer the document field id     
     * @return DocumentTypeFieldLink the object representing this database row
     */
	function getByFieldAndTypeIDs($iDocTypeID, $iDocFieldID) {
	   	global $default;
		$sql = $default->db;
		$sQuery = "SELECT id FROM $default->document_type_fields_table WHERE field_id = $iDocFieldID AND document_type_id = $iDocTypeID";
		$result = $sql->query($sQuery);
		if ($result) {
			if ($sql->next_record()) {
				return DocumentTypeFieldLink::get($sql->f("id"));
			} else{
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Static- Returns DocumentFields mapped to a DocumentType
	 *
	 * @param integer primary key of the document type
	 * @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	 */
	function getDocumentTypeFields($iDocTypeID) {
		global $default;
		$aFields = array();
		$sql = $default->db;
		$result = $sql->query("SELECT field_id FROM " . $default->document_type_fields_table  . " WHERE document_type_id = ". $iDocTypeID);
		if ($result) {			
			while ($sql->next_record()) {
				$aFields[] = DocumentField::get($sql->f("field_id"));
			}
			return $aFields;
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
function & documenttypefieldlinkCreateFromArray($aParameters) {
	$oDocTypeField = & new DocumentTypeFieldLink($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocTypeField;
}

?>

<?php
/**
 * $Id$
 *
 * Represents a document field link as per the database table document_fields_link.
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
class DocumentFieldLink extends KTEntity {
	
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

    function _fieldValues () {
        return array(
            'document_id' => $this->iDocumentID,
            'document_field_id' => $this->iDocumentFieldID,
            'value' => $this->sValue,
        );
    }

    function _table () {
        global $default;
        return $default->document_fields_link_table;
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
		$sql = $default->db;
		$sql->query(array("SELECT * FROM " . $default->document_fields_link_table . " WHERE id = ?", $iDocumentFieldLinkID));/*ok*/
		if ($sql->next_record()) {			
			$oDocumentFieldLink = & new DocumentFieldLink($sql->f("document_id"), $sql->f("document_field_id"), $sql->f("value"));		
			$oDocumentFieldLink->iId = $iDocumentFieldLinkID;
			return $oDocumentFieldLink;
		}
		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = $default->document_fields_link_table";
		return false;		
	}
}
?>

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
class DocumentType extends KTEntity {
	
	/** primary key value */
	var $iId;
	/** document type name */
	var $sName;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
    );
	
	/**
	* Default constructor
	*
	* @param 	Name of document type
	* @param 	document type data type
	*
	*/
	function DocumentType($sNewName = null) {
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

    function _fieldValues () {
        return array(
            'name' => $this->sName,
        );
    }

    function _table () {
        global $default;
        return $default->document_types_table;
    }
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
        if ($this->isUsed()) {
            return PEAR::raiseError('Document type still in use');
        }
        return parent::delete();
	}

    function isUsed() {
        $sTable = KTUtil::getTableName('documents');
        $res = DBUtil::getOneResultKey(array(
            "SELECT COUNT(id) AS cnt FROM documents WHERE document_type_id = ?",
            array($this->getId()),
        ), 'cnt');
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res != 0) {
            return true;
        }
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
		$result = $sql->query(array("SELECT * FROM ". $default->document_types_table ." WHERE id = ?", $iDocumentTypeID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentType = & new DocumentType($sql->f("name"));
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
	function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('DocumentType', $sWhereClause);
	}

    function &createFromArray($aArray) {
        return KTEntityUtil::createFromArray('DocumentType', $aArray);
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

<?php
/**
 * $Id$
 *
 * Represents a document field lookups as per the database document_types_lookup table
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
class MetaData extends KTEntity {
	
	/** primary key value */
	var $iId;
	//document field id
	var $iDocFieldId;
	/** MetaData name */
	var $sName;
	
	/**
	* Default constructor
	*
	* @param 	Name of MetaData
	* @param 	MetaData data type
	*
	*/
	function MetaData($iNewDocFieldID,$sNewName) {
		//object not created yet
		$this->iId = -1;
		$this->iDocFieldID = $iNewDocFieldID;
		$this->sName = $sNewName;
		
	}
	
	/**
	* Get the MetaData's primary key value
	*
	* @return int MetaData's primary key value
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the MetaData's name
	*
	* @return String MetaData's name
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	
	/**
	* Set the MetaData's name
	*
	* @param 	MetaData's new name
	*
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	/**
	* Set the MetaData's docField
	*
	* @param 	MetaData's new name
	*
	*/
	function setDocFieldID($sNewValue) {
		$this->iDocFieldID = $sNewValue;
	}
	
	/**
	* Get the MetaData's docfield
	*
	* @return String MetaData's name
	*
	*/
	function getDocFieldID() {
		return $this->iDocFieldID;
	}

    function _fieldValues () {
        return array(
            'document_field_id' => $this->iDocFieldID,
            'name' => $this->sName,
        );
    }

    function _table () {
        global $default;
        return $default->metadata_table;
    }
	
	
	
     /**
	* Store the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default;
		if ($this->iId < 0) {
			//check to see if name exsits
			$sql = $default->db;
			$query = "SELECT name FROM ". $default->metadata_table ." WHERE name = ? and document_field_id = ?";/*ok*/
            $aParams = array($this->sName, $this->iDocFieldID);
            $sql->query(array($query, $aParams));
            $rows = $sql->num_rows($sql);
        
            if ($rows > 0){
                $_SESSION["errorMessage"] = "MetaData::TheMetaData name " . $this->sName . " for the specfic field exists already!";
                return false;
            }
        }
        return parent::create();
	}
	
	/**
	* Static function.
	* Given a document_fields primary key it will create a 
	*MetaDatas object and populate it with the 
	* corresponding database values
	*
	* @returnMetaData populatedMetaData object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iMetaDataID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM ". $default->metadata_table." WHERE id = ?", $iMetaDataID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentType = & new MetaData($sql->f("document_field_id"),$sql->f("name"));
				$oDocumentType->iId = $sql->f("id");
				return $oDocumentType;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = document_types";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	



/*
     * static function
     *
     * sets the id of the groupunit using their groupid
     *
     * @param   String
     *   The unit_ID
     *   
     */
     
	function setMetaDataID($iDocFieldID, $sMetaDataName)
	 {
	   	global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->metadata_table WHERE document_field_id = ? and name = ?";/*ok*/
        $aParams = array($iDocFieldID, $sMetaDataName);
		$result = $sql->query(array($sQuery, $aParams));
		if ($result) {
			if ($sql->next_record()) {
				$id = $sql->f("id");
				
			}else{
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
		
		}else{	
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		
		$this->iId = $id;
		
	}

}
/**
* Static function
*
* Creates a MetaData object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & metadataCreateFromArray($aParameters) {
	$oMetaData = & new MetaData($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oMetaData;
}

?>

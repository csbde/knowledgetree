<?php
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

require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');

class DocumentField extends KTEntity {
	
	/** primary key value */
	var $iId = -1;
	var $sName;
	var $sDataType;
	var $sDescription;
	var $bIsGeneric;
	var $bHasLookup;
    var $iParentFieldset;
	var $bHasLookupTree;
	var $bIsMandatory;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
		'sDescription' => 'description',
        'sDataType' => 'data_type',
        'bIsGeneric' => 'is_generic',
        'bHasLookup' => 'has_lookup',
        'bHasLookupTree' => 'has_lookuptree',
        'iParentFieldset' => 'parent_fieldset',
        'bIsMandatory' => 'is_mandatory',
    );

    // FIXME deprecate constructor use.  Use DocumentField::createFromArray instead.	
	function DocumentField($sNewName = null, $sNewDataType = null, $bNewIsGeneric = null, $bNewHasLookup = null, $iParentFieldset = null, $bHasLookupTree = null) {

		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sDataType = $sNewDataType;
		$this->bIsGeneric = $bNewIsGeneric;
		$this->bHasLookup = $bNewHasLookup;
		$this->iParentFieldset = $iParentFieldset;
		$this->bHasLookupTree = $bHasLookupTree;
	}
	
	function getID() { return $this->iId; }
	function getName() { return $this->sName; }
	function setName($sNewValue) { $this->sName = $sNewValue; }
	function setDescription($sNewValue) { $this->sDescription = $sNewValue; }
	function getDescription() { return $this->sDescription; }	
	function getDataType() { return $this->sDataType; }
    function setDataType($sNewValue) { $this->sDataType = $sNewValue; }
	function getIsGeneric() { return $this->bIsGeneric; }
	function setIsGeneric($sNewValue) { 	$this->bIsGeneric = $sNewValue;	}
    function getHasLookup() { return $this->bHasLookup; }
	function setHasLookup($iNewValue) {	$this->bHasLookup = $iNewValue; }
    function getParentFieldset() { return $this->iParentFieldset; }
	function setParentFieldset($iNewValue) { $this->iParentFieldset = $iNewValue; }
    function getParentFieldsetId() { return $this->iParentFieldset; }
	function setParentFieldsetId($iNewValue) { $this->iParentFieldset = $iNewValue; }
    function getHasLookupTree() { return $this->bHasLookupTree; }
	function setHasLookupTree($iNewValue) { $this->bHasLookupTree = KTUtil::anyToBool($iNewValue); }
    function getIsMandatory() { return $this->bIsMandatory; }
	function setIsMandatory($iNewValue) { $this->bIsMandatory = KTUtil::anyToBool($iNewValue); }

    function _fieldValues () {
        return array(
            'name' => $this->sName,
			'description' => $this->sDescription,
            'data_type' => $this->sDataType,
            'is_generic' => KTUtil::anyToBool($this->bIsGeneric),
            'has_lookup' => KTUtil::anyToBool($this->bHasLookup),
            'parent_fieldset' => $this->iParentFieldset,
            'has_lookuptree' => KTUtil::anyToBool($this->bHasLookupTree),
            'is_mandatory' => KTUtil::anyToBool($this->bIsMandatory),
        );
    }

    function _table () {
        global $default;
        return $default->document_fields_table;
    }

	function &getList($sWhereClause = null) {
            return KTEntityUtil::getList2('DocumentField', $sWhereClause);
	}	

    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('DocumentField', $aOptions);
    }

    function &getByFieldset($oFieldset) {
        if (is_object($oFieldset)) {
            $iFieldsetId = $oFieldset->getId();
        } else {
            $iFieldsetId = $oFieldset;
        }
        return KTEntityUtil::getByDict('DocumentField', array(
            'parent_fieldset' => $iFieldsetId,
        ), array(
            'multi' => true,
        ));
    }

    function &getByFieldsetAndName($oFieldset, $sName) {
        $iFieldsetId = KTUtil::getId($oFieldset);
        return KTEntityUtil::getByDict('DocumentField', array(
            'parent_fieldset' => $iFieldsetId,
            'name' => $sName,
        ));
    }
	
    function &get($iId) {
        return KTEntityUtil::get('DocumentField', $iId);
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

    function getType() {
        if (empty($this->bHasLookup)) {
            return "Normal";
        }
        if (empty($this->bHasLookupTree)) {
            return "Lookup";
        }
        return "Tree";
    }

    function getValues() {
        return MetaData::getByDocumentField($this);
    }

    function getEnabledValues() {
        return MetaData::getEnabledByDocumentField($this);
    }

    function getDisabledValues() {
        return MetaData::getDisabledByDocumentField($this);
    }

    function getParentFieldId() {
        return KTMetadataUtil::getParentFieldId($this);
    }

    function getChildFieldIds() {
        return KTMetadataUtil::getChildFieldIds($this);
    }

    function hasChildren() {
        return (boolean)KTMetadataUtil::getChildFieldIds($this);
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

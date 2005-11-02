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
	var $iId = -1;
	//document field id
	var $iDocFieldId;
	/** MetaData name */
	var $sName;
    /** _if_ this field is a tree, which node in said tree is this one's parent. */
    var $iTreeParent;
    var $bDisabled = false;

    var $_bUsePearError = true;
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iDocFieldId' => 'document_field_id',
        'sName' => 'name',
        'iTreeParent' => 'treeorg_parent',
        'bDisabled' => 'disabled',
    );
	
	/**
	* Default constructor
	*
	* @param 	Name of MetaData
	* @param 	MetaData data type
	*
	*/
	function MetaData($iNewDocFieldID = null,$sNewName = null, $iNewParent = null) {
		//object not created yet
		$this->iDocFieldId = $iNewDocFieldID;
		$this->sName = $sNewName;
		$this->iTreeParent = $iNewParent;
	}
	
	function getID() { return $this->iId; }
	function getName() { return $this->sName; }
	function setName($sNewValue) { $this->sName = $sNewValue; }
	function getDocFieldId() { return $this->iDocFieldId; }
	function setDocFieldId($iNewValue) { $this->iDocFieldId = $iNewValue; }
    function getTreeParent() { return $this->iTreeParent; }
    function setTreeParent($iNewValue) { $this->iTreeParent = $iNewValue; }
    function getDisabled() { return $this->bDisabled; }
    function setDisabled($bNewValue) { $this->bDisabled = $bNewValue; }

    function _table () {
        global $default;
        return $default->metadata_table;
    }
	
	function & get($iId) {
        return KTEntityUtil::get('MetaData', $iId);
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
     
	function setMetaDataID($iDocFieldId, $sMetaDataName)
	 {
	   	global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->metadata_table WHERE document_field_id = ? and name = ?";/*ok*/
        $aParams = array($iDocFieldId, $sMetaDataName);
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

    function getList($sWhereClause = null) {
        return KTEntityUtil::getList2('MetaData', $sWhereClause);
    }

    function getByDocumentField($oDocumentField) {
        if (is_object($oDocumentField)) {
            $iDocumentFieldId = $oDocumentField->getId();
        } else {
            $iDocumentFieldId = $oDocumentField;
        }
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
        ), array(
            'multi' => 'true'
        ));
    }

    function getValuesByDocumentField($oDocumentField) {
        if (is_object($oDocumentField)) {
            $iDocumentFieldId = $oDocumentField->getId();
        } else {
            $iDocumentFieldId = $oDocumentField;
        }
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
        ), array(
            'multi' => 'true',
            'ids' => true,
            'idfield' => 'name',
        ));
    }

    function getEnabledByDocumentField($oDocumentField) {
        if (is_object($oDocumentField)) {
            $iDocumentFieldId = $oDocumentField->getId();
        } else {
            $iDocumentFieldId = $oDocumentField;
        }
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'disabled' => false,
        ), array(
            'multi' => 'true'
        ));
    }

    function getDisabledByDocumentField($oDocumentField) {
        $iDocumentFieldId = KTUtil::getId($oDocumentField);
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'disabled' => true,
        ), array(
            'multi' => true,
        ));
    }

    function getEnabledValuesByDocumentField($oDocumentField) {
        $iDocumentFieldId = KTUtil::getId($oDocumentField);
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'disabled' => false,
        ), array(
            'multi' => 'true',
            'ids' => true,
            'idfield' => 'name',
        ));
    }

    function getDisabledValuesByDocumentField($oDocumentField) {
        $iDocumentFieldId = KTUtil::getId($oDocumentField);
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'disabled' => true,
        ), array(
            'multi' => 'true',
            'ids' => true,
            'idfield' => 'name',
        ));
    }

    function getByValueAndDocumentField($sValue, $oDocumentField) {
        $iDocumentFieldId = KTUtil::getId($oDocumentField);
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'name' => $sValue,
        ));
    }

    function &createFromArray($aData) {
        return KTEntityUtil::createFromArray('MetaData', $aData);
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

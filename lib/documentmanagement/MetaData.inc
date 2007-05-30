<?php
/**
 * $Id$
 *
 * Represents a document field lookups as per the database document_types_lookup table
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
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
    /**
     * If the metadata is permanent, it will never be automatically
     * changed from enabled to disabled or vice versa
     */
    var $bIsStuck = false;

    var $_bUsePearError = true;
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iDocFieldId' => 'document_field_id',
        'sName' => 'name',
        'iTreeParent' => 'treeorg_parent',
        'bDisabled' => 'disabled',
        'bIsStuck' => 'is_stuck',
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
    function getIsStuck() { return $this->bIsStuck; }
    function setIsStuck($bNewValue) { $this->bIsStuck = $bNewValue; }

    function _table () {
        global $default;
        return $default->metadata_table;
    }
	
	function & get($iId) {
        return KTEntityUtil::get('MetaData', $iId);
	}
	
    // FIXME this function makes no sense.
	function setMetaDataID($iDocFieldId, $sMetaDataName)
	 {
	    $sTable = KTUtil::getTableName('metadata');
	    $sQuery = "SELECT id FROM $sTable WHERE document_field_id = ? and name = ?";
        $aParams = array($iDocFieldId, $sMetaDataName); 
	    $res = DBUtil::getResultArray(array($sQuery, $aParams));
	    
	    if (PEAR::isError($res)) {
	        return false; // return $res;
	    } 
	    
	    if (count($res) != 0) {
	        $this->iId = $id;
	        return $res[0]['id'];
	    } else {
	        return false; // return PEAR::raiseError(_kt('No such plugin pack'))
	    }
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
            'multi' => 'true',
			'orderby' => 'name ASC',
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
			'orderby' => 'name ASC',			
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
            'multi' => 'true',
			'orderby' => 'name ASC',			
        ));
    }

    function getDisabledByDocumentField($oDocumentField) {
        $iDocumentFieldId = KTUtil::getId($oDocumentField);
        return KTEntityUtil::getByDict('MetaData', array(
            'document_field_id' => $iDocumentFieldId,
            'disabled' => true,
        ), array(
            'multi' => true,
			'orderby' => 'name ASC',			
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
			'orderby' => 'name ASC',			
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
			'orderby' => 'name ASC',			
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

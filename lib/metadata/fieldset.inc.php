<?php
/**
 * $Id$
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
 */

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

/**
 * class KTFieldset
 *
 * Represents the basic grouping of fields into a fieldset.
 */
class KTFieldset extends KTEntity {
	
	/** primary key value */
	var $iId = -1;
	/** document fieldset name */
	var $sName;
	/** document fieldset description. */
	var $sDescription;
	/** document fieldset namespace */
	var $sNamespace;
	
	/** document fieldset mandatory flag */
	var $bMandatory = false;
	var $iMasterFieldId;

    var $bIsGeneric = false;
    // By default, we're complete.  When we become conditional, then we
    // become incomplete until made complete.
    var $bIsComplete = true;
	var $bIsConditional = false;
    var $bIsComplex = false;
    /**
     * A System fieldset is one that is never displayed to a user, and
     * is used only by the document management system.
     */
    var $bIsSystem = false;
	
    var $_bUsePearError = true;
	
	function getId() { return $this->iId; }
	function getName() { return sanitizeForSQLtoHTML($this->sName); }
	function setDescription($sNewValue) { $this->sDescription = sanitizeForSQL($sNewValue); }
	function getDescription() { return sanitizeForSQLtoHTML($this->sDescription); }
	function setName($sNewValue) { $this->sName = sanitizeForSQL($sNewValue); }
	function getNamespace() { return $this->sNamespace; }
    function setNamespace($sNewValue) {	$this->sNamespace = $sNewValue; }
	function getMandatory() { return $this->bMandatory; }
	function setMandatory($bNewValue) {	$this->bMandatory = $bNewValue; }	
	function getIsConditional () { return $this->bIsConditional; }
	function setIsConditional ($bNewValue) {	$this->bIsConditional = $bNewValue; }	
	function getMasterFieldId () { return $this->iMasterFieldId; }
	function setMasterFieldId ($iNewValue) {	$this->iMasterFieldId = $iNewValue; }	
	function getIsGeneric () { return $this->bIsGeneric; }
	function setIsGeneric ($bNewValue) {	$this->bIsGeneric = $bNewValue; }	
	function getIsComplete () { return $this->bIsComplete; }
	function setIsComplete ($bNewValue) {	$this->bIsComplete = $bNewValue; }	
	function getIsComplex () { return $this->bIsComplex; }
	function setIsComplex ($bNewValue) {	$this->bIsComplex = $bNewValue; }	
	function getIsSystem () { return $this->bIsSystem; }
	function setIsSystem ($bNewValue) {	$this->bIsSystem = $bNewValue; }	

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
		"sDescription" => "description",
        "sNamespace" => "namespace",
        "bMandatory" => "mandatory",
		"bIsConditional" => "is_conditional",
		"iMasterFieldId" => "master_field",
        "bIsGeneric" => "is_generic",
        "bIsComplete" => "is_complete",
        "bIsComplex" => "is_complex",
        "bIsSystem" => "is_system",
    );
	
    // returns TRUE if all children are lookup enabled, false otherwise.
    function canBeMadeConditional() {
	if ($this->getIsConditional()) {
	    return false;
	}
	   
	// DEBUG
	return false;
    }
	
    function _table () {
        return KTUtil::getTableName('fieldsets');
    }





    /* 
     * get document types using this field
     * for listing displays                 
     */
    function &getDocumentTypesUsing($aOptions = null) {
        $bIds = KTUtil::arrayGet($aOptions, 'ids');
        
        $sTable = KTUtil::getTableName('document_type_fieldsets');

        $aQuery = array(
            "SELECT document_type_id FROM $sTable WHERE fieldset_id = ?",
            array($this->getId()),
        );
        $aIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');

        if ($bIds) {
            return $aIds;
        }

        $aRet = array();
        foreach ($aIds as $iID) {
            $aRet[] =& call_user_func(array('DocumentType', 'get'), $iID);
        }
        return $aRet;
    }







    // Static function
    function &get($iId) { return KTEntityUtil::get('KTFieldset', $iId); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTFieldset', $sWhereClause); }	
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTFieldset', $aOptions); }

	function &getNonGenericFieldsets($aOptions = null) {
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'multi' => true,
        ));
        return KTEntityUtil::getByDict('KTFieldset', array(
            'is_generic' => false,
            'disabled' => false,
        ), $aOptions);
    }	

    function &getGenericFieldsets($aOptions = null) {
	$aOptions = KTUtil::meldOptions(
	    $aOptions, 
	    array('multi' => true,)
	);
        return KTEntityUtil::getByDict('KTFieldset', array(
            'is_generic' => true,
            'disabled' => false,
        ), $aOptions);
    }	

    function &getForDocumentType($oDocumentType, $aOptions = null) {
        $bIds = KTUtil::arrayGet($aOptions, 'ids');
        if (is_object($oDocumentType)) {
            $iDocumentTypeId = $oDocumentType->getId();
        } else {
            $iDocumentTypeId = $oDocumentType;
        }
        
        $sTable = KTUtil::getTableName('document_type_fieldsets');
        $aQuery = array(
            "SELECT fieldset_id FROM $sTable WHERE document_type_id = ?",
            array($iDocumentTypeId),
        );
        $aIds = DBUtil::getResultArrayKey($aQuery, 'fieldset_id');

        if ($bIds) {
            return $aIds;
        }

        $aRet = array();
        foreach ($aIds as $iID) {
            $aRet[] =& call_user_func(array('KTFieldset', 'get'), $iID);
        }
        return $aRet;
    }
	
	function &getAssociatedTypes() {
	    // NOTE:  this returns null if we are generic (all is the wrong answer)
		if ($this->getIsGeneric()) { return array(); }
		
		$sTable = KTUtil::getTableName('document_type_fieldsets');
        $aQuery = array(
            "SELECT document_type_id FROM $sTable WHERE fieldset_id = ?",
            array($this->getId()),
        );
        $aIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');
		
		$aRet = array();
		foreach ($aIds as $iID) {
		    $oType = DocumentType::get($iID);
			if (!PEAR::isError($oType)) { 
			    $aRet[] = $oType;
			}
		}
		return $aRet;
	}

    function &getFields() {
        return DocumentField::getByFieldset($this);
    }

    function &getByField($oField) {
        $oField =& KTUtil::getObject('DocumentField', $oField);
        $iFieldsetId = $oField->getParentFieldsetId();
        return KTFieldset::get($iFieldsetId);
    }

    function &getByNamespace($sNamespace) {
        return KTEntityUtil::getByDict('KTFieldset', array(
            'namespace' => $sNamespace,
            'disabled' => false,
        ));
    }
    
    function &getByName($sName) {
        return KTEntityUtil::getByDict('KTFieldset', array(
            'name' => $sName,
            'disabled' => false,
        ));
    }	    
}

?>

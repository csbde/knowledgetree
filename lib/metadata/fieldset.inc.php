<?php

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");

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
	/** document fieldset namespace */
	var $sNamespace;
	/** document fieldset mandatory flag*/
	var $bMandatory = false;
	var $bIsConditional = false;
	var $iMasterFieldId;

    var $bIsGeneric = false;
    // By default, we're complete.  When we become conditional, then we
    // become incomplete until made complete.
    var $bIsComplete = true;
    var $bIsComplex = false;
	
    var $_bUsePearError = true;
	
	function getId() { return $this->iId; }
	function getName() { return $this->sName; }
	
	function setName($sNewValue) { $this->sName = $sNewValue; }
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

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sNamespace" => "namespace",
        "bMandatory" => "mandatory",
		"bIsConditional" => "is_conditional",
		"iMasterFieldId" => "master_field",
        "bIsGeneric" => "is_generic",
        "bIsComplete" => "is_complete",
        "bIsComplex" => "is_complex",
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

    // Static function
    function &get($iId) { return KTEntityUtil::get('KTFieldset', $iId); }
	function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTFieldset', $sWhereClause);	}	
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTFieldset', $aOptions); }

	function &getNonGenericFieldsets() {
        return KTEntityUtil::getByDict('KTFieldset', array(
            'is_generic' => false,
        ), array(
            'multi' => true,
        ));
    }	

	function &getGenericFieldsets() {
        return KTEntityUtil::getByDict('KTFieldset', array(
            'is_generic' => true,
        ), array(
            'multi' => true,
        ));
    }	

    function &getForDocumentType($oDocumentType) {
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

        $aRet = array();
        foreach ($aIds as $iID) {
            $aRet[] =& call_user_func(array('KTFieldset', 'get'), $iID);
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
}

?>

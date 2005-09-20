<?php

require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/ktentity.inc");

/**
 * class DocumentFieldSet
 *
 * Represents the basic grouping of fields into a fieldset.
 */
class DocumentFieldSet extends KTEntity {
	
	/** primary key value */
	var $iId = -1;
	/** document fieldset name */
	var $sName;
	/** document fieldset namespace */
	var $sName;
	/** document fieldset mandatory flag*/
	var $bMandatory;
	var $bIsConditional;
	var $iMasterField;
	
    var $_bUsePearError = true;
	
	function getId() { return $this->iId; }
	function getName() { return $this->sName; }
	
	function setName($sNewValue) { $this->sName = $sNewValue; }
	function getNamespace() { return $this->sNamespace; }
    function setNamespace($sNewcreValue) {	$this->sNamespace = $sNewValue; }
	function getMandatory() { return $this->bMandatory; }
	function setMandatory($bNewValue) {	$this->bMandatory = $bNewValue; }	
	function getIsConditional () { return $this->bIsConditional; }
	function setIsConditional ($bNewValue) {	$this->bIsConditional = $bNewValue; }	
	function getMasterField () { return $this->bIsConditional; }
	function setMasterField ($iNewValue) {	$this->bIsConditional = $iNewValue; }	

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sNamespace" => "namespace",
        "bMandatory" => "mandatory",
		"bIsConditional" => "is_conditional",
		"iMasterField" => "master_field",
    );
	
	// returns TRUE if all children are lookup enabled, false otherwise.
	function canBeMadeConditional() {
	   if ($this->getIsConditional()) {
	      return false;
	   }
	   
	   // DEBUG
	   return false;
	}
	
    function _fieldValues () {
        return array(
            'name' => $this->sName,
			'namespace' => $this->sNamespace,
			'mandatory' => $this->bMandatory,
			'is_conditional' => KTUtil::anyToBool($this->bMandatory),
			'master_field' => $this->iMasterField,
        );
    }

    function _table () {
        global $default;
        return $default->document_fieldset_table;
    }

    // Static function
    function &get($iId) { return KTEntityUtil::get('DocumentFieldSet', $iId); }
	function &getList($sWhereClause = null) { return KTEntityUtil::getList2('DocumentFieldSet', $sWhereClause);	}	
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('DocumentFieldSet', $aOptions); }
}

?>

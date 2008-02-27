<?php
/**
 * $Id$
 *
 * Represents a document field as per the database document_fields table.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

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

	// FIXME: add a data type class!
	/* Static function.  Given a documenttype...will find all fields belongin to it
	*
	* @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getLookupCount($iDocumentFieldID){
	    $sTable = 'metadata';
		$sQuery = "SELECT COUNT(*) AS count FROM " . $sTable . " WHERE document_field_id = ?";
		$aParams = array($iDocumentFieldID);

        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'count');
        if (PEAR::isError($res)) {
            return false; // return $res;
        }
        return $res;
	}

    function getType() {
        if (empty($this->bHasLookup)) {
            return _kt("Normal");
        }
        if (empty($this->bHasLookupTree)) {
            return _kt("Lookup");
        }
        return _kt("Tree");
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

    function getTypeDescription() {
        $aDescriptors = array();
        // type
        $aDescriptors[] = $this->getType();

        // required
        if ($this->getIsMandatory()) {
            $aDescriptors[] = _kt("Required");
        } else {
            $aDescriptors[] = _kt("Not Required");
        }

        return implode(', ', $aDescriptors);
    }

    function getNamespace() {
        $oFieldset = KTFieldset::get($this->getParentFieldsetId());
        if(PEAR::isError($oFieldset)) {
            $sFS = 'nofieldset';
        } else {
            $sFS = $oFieldset->getNamespace();
        }
        return $sFS . '.' . $this->getName();
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
	$oDocField = new DocumentField($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocField;
}


?>

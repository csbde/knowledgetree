<?php
/**
 * $Id$
 *
 * Represents a document field as per the database document_fields table.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
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

/**
 * Compare the fields within a fieldset for alphabetising.
 *
 * @param object $a
 * @param object $b
 * @return integer
 */
function compareFields($a, $b)
{
    if ($a->getName() == $b->getName()) return 0;
    if ($a->getName() < $b->getName()) return -1;
    return 1;
}


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
    var $iPosition;
    var $bIsHTML;
    var $iMax;
    var $bHasInetLookup;
    var $sInetLookupType;

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
        'iPosition' => 'position',
        'bIsHTML' => 'is_html',
        'iMax' => 'max_length',
        'bHasInetLookup' => 'has_inetlookup',
        'sInetLookupType' => 'inetlookup_type',
    );

    // FIXME deprecate constructor use.  Use DocumentField::createFromArray instead.
    function DocumentField($sNewName = null, $sNewDataType = null, $bNewIsGeneric = null, $bNewHasLookup = null, $iParentFieldset = null, $bHasLookupTree = null, $bNewIsHTML = null, $iNewMaxLength = null, $bHasNewInetLookup = null, $sNewInetLookupType = null) {

        $this->iId = -1;
        $this->sName = $sNewName;
        $this->sDataType = $sNewDataType;
        $this->bIsGeneric = $bNewIsGeneric;
        $this->bHasLookup = $bNewHasLookup;
        $this->iParentFieldset = $iParentFieldset;
        $this->bHasLookupTree = $bHasLookupTree;
        $this->iPosition = $this->getNextFieldPosition($iParentFieldset);
        $this->bIsHTML = $bNewIsHTML;
        $this->iMax = $iNewMaxLength;
        $this->sInetLookupType = $bHasNewInetLookup;
        $this->sInetLookupType = $sNewInetLookupType;
    }

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function setDescription($sNewValue) { $this->sDescription = $sNewValue; }
    function getDescription() { return $this->sDescription; }
    function getDataType() { return $this->sDataType; }
    function setDataType($sNewValue) { $this->sDataType = $sNewValue; }
    function getIsGeneric() { return $this->bIsGeneric; }
    function setIsGeneric($sNewValue) {     $this->bIsGeneric = $sNewValue; }
    function getHasLookup() { return $this->bHasLookup; }
    function setHasLookup($iNewValue) { $this->bHasLookup = $iNewValue; }
    function getParentFieldset() { return $this->iParentFieldset; }
    function setParentFieldset($iNewValue) { $this->iParentFieldset = $iNewValue; }
    function getParentFieldsetId() { return $this->iParentFieldset; }
    function setParentFieldsetId($iNewValue) { $this->iParentFieldset = $iNewValue; }
    function getHasLookupTree() { return $this->bHasLookupTree; }
    function setHasLookupTree($iNewValue) { $this->bHasLookupTree = KTUtil::anyToBool($iNewValue); }
    function getIsMandatory() { return $this->bIsMandatory; }
    function setIsMandatory($iNewValue) { $this->bIsMandatory = KTUtil::anyToBool($iNewValue); }
    function getPosition() { return $this->iPosition; }
    function setPosition($iNewValue) { $this->iPosition = $iNewValue; }
    function getIsHTML() { return $this->bIsHTML; }
    function setIsHTML($iNewValue) { $this->bIsHTML = $iNewValue; }
    function getMaxLength() { return $this->iMax; }
    function setMaxLength($iNewValue) { $this->iMax = $iNewValue; }
    function getHasInetLookup() { return $this->bHasInetLookup; }
    function setHasInetLookup($iNewValue) { $this->bHasInetLookup = $iNewValue; }
    function getInetLookupType() { return $this->sInetLookupType; }
    function setInetLookupType($sNewValue) {    $this->sInetLookupType = $sNewValue; }
    function getInetLookupTypeFriendlyName()
    {
        $aDescriptors = array();
        if($this->getInetLookupType() == "multiwithlist")
        {
            $aDescriptors[] = _kt("Multiselect with a list");
        }
        elseif($this->getInetLookupType() == "multiwithcheckboxes")
        {
            $aDescriptors[] = _kt("Multiselect with checkboxes");
        }

        // required
        if ($this->getIsMandatory()) {
            $aDescriptors[] = _kt("Required");
        } else {
            $aDescriptors[] = _kt("Not Required");
        }

        return implode(', ', $aDescriptors);
    }


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
            'position' => $this->iPosition,
            'is_html' => KTUtil::anyToBool($this->bIsHTML),
            'max_length' => $this->iMax,
            'has_inetlookup' => $this->bHasInetLookup,
            'inetlookup_type' => $this->sInetLookupType,
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
        $aParams = array('parent_fieldset' => $iFieldsetId);
        $aOptions = array('multi' => true, 'orderby' => 'position');

        $aFields = KTEntityUtil::getByDict('DocumentField', $aParams, $aOptions);

        /* Alphabetise the metadata fields within a fieldset if set in config
        $oKTConfig =& KTConfig::getSingleton();
        $use_sort = $oKTConfig->get('ui/metadata_sort', false);

        if($use_sort){
            usort($aFields, 'compareFields');
        }
        */
        return $aFields;
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
        if($this->bHasInetLookup){
            return  _kt('Multiselect');
    }
        if (empty($this->bHasLookup)) {
            switch(strtolower($this->getDataType())){
                case 'date':
                    return _kt('Date');
                case 'large text':
                    return _kt('Large Text');
            }

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

    /**
     * Reset the field positions for a given fieldset
     *
     */
    function reorderFields($iFieldsetId) {
        // Get the fields in the order they'll appear - first by position then by id
        $sql = "SELECT id, position FROM document_fields
           WHERE parent_fieldset = ?
           ORDER BY position, id ASC";
        $aParams = array($iFieldsetId);

        $result = DBUtil::getResultArray(array($sql, $aParams));

        if(PEAR::isError($result) || empty($result)){
            return false;
        }

        // Set all positions to be unique and in order
        foreach($result as $key => $field){
            $position = $field['position'];

            // If the fields position is correct in the order, continue
            if($position == $key){
                continue;
            }

            // Reset the position
            $aFields = array();
            $aFields['position'] = $key;

            $res = DBUtil::autoUpdate('document_fields', $aFields, $field['id']);
        }
        return true;
    }

    /**
     * Get the postion of the last field
     */
    function getNextFieldPosition($iFieldsetId){
        $sql = "SELECT position FROM document_fields
           WHERE parent_fieldset = ?
           ORDER BY position DESC LIMIT 1";
        $aParams = array($iFieldsetId);

        $result = DBUtil::getResultArray(array($sql, $aParams));

        if(PEAR::isError($result) || empty($result)){
            return 0;
        }
        return $result[0]['position'] + 1;
    }

    /**
     * Get the next postion up / down
     */
    function getNextPosition($iFieldsetId, $iId, $position, $dir = 'up') {
        switch($dir){
            case 'down':
                $comp = '>';
                $order = 'ASC';
                break;
            default:
                $comp = '<';
                $order = 'DESC';
        }

        // Get the column above / below to swop position
        $sql = "SELECT id, position FROM document_fields
           WHERE parent_fieldset = ? AND (position {$comp} ? || (position = ? AND id {$comp} ?))
           ORDER BY position {$order} LIMIT 1";
        $aParams = array($iFieldsetId, $position, $position, $iId);

        $result = DBUtil::getOneResult(array($sql, $aParams));

        if(PEAR::isError($result) || empty($result)){
            return false;
        }
        return $result;
    }

    /**
     * Get the updated position of the field
     *
     * @param int $iId
     * @return int
     */
    function getNewPosition($iId){
        // Get the new position
        $sql = "SELECT id, position FROM document_fields
               WHERE id = ?";
        $aParams = array($iId);
        $result = DBUtil::getOneResult(array($sql, $aParams));

        if(PEAR::isError($result) || empty($result)){
            return false;
        }
        return $result['position'];
    }

    /**
     * Update the position of a field
     */
    function updatePosition($iId, $position) {
        $aFields = array('position' => $position);
        DBUtil::autoUpdate('document_fields', $aFields, $iId);
    }

    /**
     * Move the display position of the field up
     */
    function movePosition($iFieldsetId, $iId, $dir = 'up') {
        $position = $this->getPosition();

        // Get the field to swop position with
        $next = $this->getNextPosition($iFieldsetId, $iId, $position, $dir);
        if($next === false){
            return false;
        }

        // Get position of the next field up / down
        $newPos = $next['position'];
        $iNextId = $next['id'];

        if($newPos == $position){
            // 2 fields have the same position - reorder them
            $res = $this->reorderFields($iFieldsetId);
            if($res === false){
                return false;
            }

            $position = $this->getNewPosition($iId);
            if($position === false){
                return false;
            }

            // Get the field to swop with
            $next = $this->getNextPosition($iFieldsetId, $iId, $position, $dir);
            if($next === false){
                return false;
            }
            $newPos = $next['position'];
            $iNextId = $next['id'];
        }

        // update the fields
        $this->updatePosition($iId, $newPos);
        $this->updatePosition($iNextId, $position);
        return true;
    }
}

/**
* Static function
*
* Creates a document type object from an array
*
* @param    Array       Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & documentfieldCreateFromArray($aParameters) {
    $oDocField = new DocumentField($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oDocField;
}


?>

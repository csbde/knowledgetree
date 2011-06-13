<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 *
 */

require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/util/sanitize.inc');

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
    function getName() { return $this->sName; }
    function setDescription($newValue) { $this->sDescription = $newValue; }
    function getDescription() { return $this->sDescription; }
    function setName($newValue) { $this->sName = $newValue; }
    function getNamespace() { return $this->sNamespace; }
    function setNamespace($newValue) {	$this->sNamespace = $newValue; }
    function getMandatory() { return $this->bMandatory; }
    function setMandatory($newValue) {	$this->bMandatory = $newValue; }
    function getIsConditional () { return $this->bIsConditional; }
    function setIsConditional ($newValue) {	$this->bIsConditional = $newValue; }
    function getMasterFieldId () { return $this->iMasterFieldId; }
    function setMasterFieldId ($newValue) {	$this->iMasterFieldId = $newValue; }
    function getIsGeneric () { return $this->bIsGeneric; }
    function setIsGeneric ($newValue) {	$this->bIsGeneric = $newValue; }
    function getIsComplete () { return $this->bIsComplete; }
    function setIsComplete ($newValue) {	$this->bIsComplete = $newValue; }
    function getIsComplex () { return $this->bIsComplex; }
    function setIsComplex ($newValue) {	$this->bIsComplex = $newValue; }
    function getIsSystem () { return $this->bIsSystem; }
    function setIsSystem ($newValue) {	$this->bIsSystem = $newValue; }

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'sDescription' => 'description',
        'sNamespace' => 'namespace',
        'bMandatory' => 'mandatory',
        'bIsConditional' => 'is_conditional',
        'iMasterFieldId' => 'master_field',
        'bIsGeneric' => 'is_generic',
        'bIsComplete' => 'is_complete',
        'bIsComplex' => 'is_complex',
        'bIsSystem' => 'is_system',
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

    /**
     * get document types using this field
     * for listing displays
     */
    function &getDocumentTypesUsing($options = null) {
        $table = KTUtil::getTableName('document_type_fieldsets');
        $query = array("SELECT document_type_id FROM $table WHERE fieldset_id = ?", array($this->getId()));
        $ids = DBUtil::getResultArrayKey($query, 'document_type_id');

        $returnIds = KTUtil::arrayGet($options, 'ids');
        if ($returnIds) {
            return $ids;
        }

        $ret = array();
        foreach ($ids as $id) {
            $ret[] =& call_user_func(array('DocumentType', 'get'), $id);
        }

        return $ret;
    }

    // Static function
    function &get($id) { return KTEntityUtil::get('KTFieldset', $id); }
    function &getList($whereClause = null) { return KTEntityUtil::getList2('KTFieldset', $whereClause); }
    function &createFromArray($options) { return KTEntityUtil::createFromArray('KTFieldset', $options); }

    function &getNonGenericFieldsets($options = null) {
        $options = KTUtil::meldOptions($options, array('multi' => true));

        return KTEntityUtil::getByDict(
                                    'KTFieldset',
                                    array('is_generic' => false, 'disabled' => false),
                                    $options
                            );
    }

    function &getGenericFieldsets($options = null) {
        $options = KTUtil::meldOptions($options, array('multi' => true));

        return KTEntityUtil::getByDict(
                                    'KTFieldset',
                                    array('is_generic' => true, 'disabled' => false),
                                    $options
                            );
    }

    function &getConditionalFieldsets($options = null) {
        $options = KTUtil::meldOptions($options, array('multi' => true));

        return KTEntityUtil::getByDict(
                                    'KTFieldset',
                                    array('is_conditional' => true, 'disabled' => false),
                                    $options
                            );
    }

    function &getForDocumentType($documentType, $options = null)
    {
        if (is_object($documentType)) {
            $documentTypeId = $documentType->getId();
        }
        else {
            $documentTypeId = $documentType;
        }

        $table = KTUtil::getTableName('document_type_fieldsets_link');
        $query = array("SELECT fieldset_id FROM $table WHERE document_type_id = ?", array($documentTypeId));
        $ids = DBUtil::getResultArrayKey($query, 'fieldset_id');

        $returnIds = KTUtil::arrayGet($options, 'ids');
        if ($returnIds) {
            return $ids;
        }

        $ret = array();
        foreach ($ids as $id) {
            $ret[] =& call_user_func(array('KTFieldset', 'get'), $id);
        }

        return $ret;
    }

    function &getAssociatedTypes() {
        // NOTE:  this returns null if we are generic (all is the wrong answer)
        if ($this->getIsGeneric()) {
            return array();
        }

        $table = KTUtil::getTableName('document_type_fieldsets');
        $query = array("SELECT document_type_id FROM $table WHERE fieldset_id = ?", array($this->getId()));
        $ids = DBUtil::getResultArrayKey($query, 'document_type_id');

        $ret = array();
        foreach ($ids as $id) {
            $type = DocumentType::get($id);
            if (!PEAR::isError($type)) {
                $ret[] = $type;
            }
        }

        return $ret;
    }

    function &getFields() {
        return DocumentField::getByFieldset($this);
    }

    function &getByField($field) {
        $field =& KTUtil::getObject('DocumentField', $field);
        $fieldsetId = $field->getParentFieldsetId();
        return KTFieldset::get($fieldsetId);
    }

    function &getByNamespace($namespace) {
        return KTEntityUtil::getByDict(
                                    'KTFieldset',
                                    array('namespace' => $namespace, 'disabled' => false)
                            );
    }

    function &getByName($name) {
        return KTEntityUtil::getByDict(
                                    'KTFieldset',
                                    array('name' => $name, 'disabled' => false)
                            );
    }

}

?>

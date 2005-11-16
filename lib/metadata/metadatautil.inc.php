<?php
/**
 * $Id$
 *
 * Utility functions for retrieving information from and managing
 * fieldsets with conditions.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/metadata/valueinstance.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldbehaviour.inc.php');

class KTMetadataUtil {

    // {{{ _getNextForBehaviour
    /**
     * Recursively traverses a fieldset from the behaviour assigned to
     * the master field and thereafter subsequent fields uncovering the
     * fields that should be filled in given the columns/fields that
     * have already be filled in, and providing the values for them
     * based on the behaviours specified by the existing values in their
     * parent fields (in combination with _their_ parent fields).
     */
    function _getNextForBehaviour($oBehaviour, $aCurrentSelections) {
        /*
         * GENERAL GAME PLAN
         *
         * For this behaviour, get the fields that this behaviour
         * affects.  Also get the values that this behaviour prescribes
         * for those fields.
         *
         * Then, for each of the next fields, check if they are already
         * filled in.
         *
         * If not, leave that field in the set of values that need to be
         * filled in, and move on to the next field for this behaviour.
         *
         * If it is filled in, remove the field from the set of values
         * to be filled in.  But add the set of fields and values that
         * the choice of value in this field prescribe (using a
         * recursive call to this function).
         */

        $oBehaviour =& KTUtil::getObject('KTFieldBehaviour', $oBehaviour);
        $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, behaviour is ' . $oBehaviour->getId());

        $aValues = KTMetadataUtil::getNextValuesForBehaviour($oBehaviour);
        $iFieldId = $oBehaviour->getFieldId();
        $aNextFields = KTMetadataUtil::getChildFieldIds($iFieldId);
        $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, next fields for ' . $iFieldId . ' are: ' . print_r($aNextFields, true)); 

        foreach ($aNextFields as $iThisFieldId) {
            if (!in_array($iThisFieldId, array_keys($aCurrentSelections))) {
                $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, field ' . $iThisFieldId . ' is not selected');

            } else {
                $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, field ' . $iThisFieldId . ' is selected');
                unset($aValues[$iThisFieldId]);

                $oInstance = KTValueInstance::getByLookupAndParentBehaviour($aCurrentSelections[$iThisFieldId], $oBehaviour);
                $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, instance is ' . print_r($oInstance, true));
                $oChildBehaviour =& KTFieldBehaviour::get($oInstance->getBehaviourId());
                $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, field ' . $iThisFieldId . ' is not selected');
                $aMyValues = KTMetadataUtil::_getNextForBehaviour($oChildBehaviour, $aCurrentSelections);
                $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, values are ' . print_r($aMyValues, true));
                foreach ($aMyValues as $k => $v) {
                    $aValues[$k] = $v;
                }
            }
        }

        $GLOBALS['default']->log->debug('KTMetadataUtil::_getNextForBehaviour, final values are ' . print_r($aValues, true));
        return $aValues;
    }
    // }}}
    
    // {{{ getNext
    /**
     * Given a set of selected values (aCurrentSelections) for a given
     * field set (iFieldSet), returns an array (possibly empty) with the
     * keys set to newly uncovered fields, and the contents an array of
     * the value instances that are available to choose in those fields.
     *
     * Return value:
     *
     * array(
     *      array('field' => DocumentField, 'values' => array(Metadata, Metadata)),
     *      ...
     * )
     *
     */
    function getNext($oFieldset, $aCurrentSelections) {
        /*
         * GENERAL GAME PLAN
         *
         * Firstly, if there are no current selections, return the
         * master field and all of its values.
         *
         * If there are selections, get the behaviour for the selected
         * value of the master field, and call _getNextForBehaviour on
         * it, passing in the current selections.  This will return an
         * array keyed on field id with values of an array of lookup ids
         * for that field.
         *
         * Convert these to objects and the return format.
         */
        $oFieldset =& KTUtil::getObject('KTFieldset', $oFieldset);
        $GLOBALS['default']->log->debug('KTMetadataUtil::getNext, selections are: ' . print_r($aCurrentSelections, true));

        if (empty($aCurrentSelections)) {
            $oField =& DocumentField::get($oFieldset->getMasterFieldId());
            return array($oField->getId() => array('field' => $oField, 'values' => $oField->getValues()));
        }

        $oMasterField =& DocumentField::get($oFieldset->getMasterFieldId());
        $aSelectedFields = array_keys($aCurrentSelections);
        $oValueInstance = KTValueInstance::getByLookupSingle($aCurrentSelections[$oMasterField->getId()]);

        $aValues = KTMetadataUtil::_getNextForBehaviour($oValueInstance->getBehaviourId(), $aCurrentSelections);
        $GLOBALS['default']->log->debug('KTMetadataUtil::getNext, values are ' . print_r($aValues, true));
        $aReturn = array();
        foreach ($aValues as $iFieldId => $aValueIds) {
            $aTheseValues = array();
            foreach ($aValueIds as $iLookupId) {
                $aTheseValues[$iLookupId] = MetaData::get($iLookupId);
            }
            $aReturn[$iFieldId] = array(
                'field' => DocumentField::get($iFieldId),
                'values' => $aTheseValues,
            );
        }
        return $aReturn;
    }
    // }}}

    // {{{ getMasterField
    /**
     * A conditional fieldset has a single field which is not affected
     * by other values.  This is the master field.  This function gets
     * the master field for the fieldset provided.
     */
    function getMasterField($oFieldset) {
        $oFieldset =& KTUtil::getObject('KTFieldset', $oFieldset);
        if ($oFieldset->getMasterField()) {
            return DocumentField::get($oFieldset->getMasterField());
        }
    }
    // }}}

    // {{{ removeSetsFromDocumentType
    /**
     * Removes a non-generic fieldset from a given document type.
     *
     * (Generic fieldsets are made available to and are required for all
     * (subsequent) documents.  Non-generic fieldsets are made available
     * to and are required for all (subsequent) documents that have a
     * particular document type.)
     */
    function removeSetsFromDocumentType($oDocumentType, $aFieldsets) {
        if (is_object($oDocumentType)) {
            $iDocumentTypeId = $oDocumentType->getId();
        } else {
            $iDocumentTypeId = $oDocumentType;
        }
        if (!is_array($aFieldsets)) {
            $aFieldsets = array($aFieldsets);
        }
        if (empty($aFieldsets)) {
            return true;
        }
        $aIds = array();
        foreach ($aFieldsets as $oFieldset) {
            if (is_object($oFieldset)) {
                $iFieldsetId = $oFieldset->getId();
            } else {
                $iFieldsetId = $oFieldset;
            }
            $aIds[] = $iFieldsetId;
        }
        // Converts to (?, ?, ?) for query
        $sParam = DBUtil::paramArray($aIds);
        $aWhere = KTUtil::whereToString(array(
            array('document_type_id = ?', array($iDocumentTypeId)),
            array("fieldset_id IN ($sParam)", $aIds),
        ));
        $sTable = KTUtil::getTableName('document_type_fieldsets');
        $aQuery = array(
            "DELETE FROM $sTable WHERE {$aWhere[0]}",
            $aWhere[1],
        );
        return DBUtil::runQuery($aQuery);
    }
    // }}}

    // {{{ addSetsToDocumentType
    /**
     * Adds a non-generic fieldset to a given document type.
     *
     * (Generic fieldsets are made available to and are required for all
     * (subsequent) documents.  Non-generic fieldsets are made available
     * to and are required for all (subsequent) documents that have a
     * particular document type.)
     */
    function addSetsToDocumentType($oDocumentType, $aFieldsets) {
        if (is_object($oDocumentType)) {
            $iDocumentTypeId = $oDocumentType->getId();
        } else {
            $iDocumentTypeId = $oDocumentType;
        }
        if (!is_array($aFieldsets)) {
            $aFieldsets = array($aFieldsets);
        }
        $aIds = array();
        foreach ($aFieldsets as $oFieldset) {
            if (is_object($oFieldset)) {
                $iFieldsetId = $oFieldset->getId();
            } else {
                $iFieldsetId = $oFieldset;
            }
            $aIds[] = $iFieldsetId;
        }

        $sTable = KTUtil::getTableName('document_type_fieldsets');
        foreach ($aIds as $iId) {
            $res = DBUtil::autoInsert($sTable, array(
                'document_type_id' => $iDocumentTypeId,
                'fieldset_id' => $iId,
            ));
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return true;
    }
    // }}}

    // {{{ addFieldOrder
    /**
     * Informs the system that the parent field's values in affects the
     * child field's values in a conditional fieldset.
     */
    function addFieldOrder($oParentField, $oChildField, $oFieldset) {
        $iParentFieldId = KTUtil::getId($oParentField);
        $iChildFieldId = KTUtil::getId($oChildField);
        $iFieldsetId = KTUtil::getId($oFieldset);

        $aOptions = array('noid' => true);
        $sTable = KTUtil::getTableName('field_orders');
        $aValues = array(
            'parent_field_id' => $iParentFieldId,
            'child_field_id' => $iChildFieldId,
            'fieldset_id' => $iFieldsetId,
        );
        return DBUtil::autoInsert($sTable, $aValues, $aOptions);
    }
    // }}}

    // {{{ removeFieldOrdering
    /**
     * Removes all field ordering for the given fieldset.
     */
    function removeFieldOrdering($oFieldset) {
        $iFieldsetId = KTUtil::getId($oFieldset);
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "DELETE FROM $sTable WHERE fieldset_id = ?",
            array($iFieldsetId),
        );
        return DBUtil::runQuery($aQuery);
    }
    // }}}

    // {{{ getParentFieldId
    /**
     * In a conditional fieldset, a field's values is affected by a
     * single parent field's values in an ordered fashion (unless it is
     * the root/master field).  This function gets the field id for the
     * field that this field is affected by.
     */
    function getParentFieldId($oField) {
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array("SELECT parent_field_id FROM $sTable WHERE child_field_id = ?",
            array($oField->getId()),
        );
        return DBUtil::getOneResultKey($aQuery, 'parent_field_id');
    }
    // }}}

    // {{{ getChildFieldIds
    /**
     * In a conditional fieldset, a field's values affect other fields'
     * values in an ordered fashion.  This function gets the field ids
     * for the fields that this field affects.
     */
    function getChildFieldIds($oField) {
        $iFieldId = KTUtil::getId($oField);
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array("SELECT child_field_id FROM $sTable WHERE parent_field_id = ?",
            array($iFieldId),
        );
        return DBUtil::getResultArrayKey($aQuery, 'child_field_id');
    }
    // }}}

    // {{{ getOrCreateValueInstanceForLookup
    /**
     * Used as a helper function in simple conditional fieldset
     * administration, this function either returns the existing value
     * instance for a lookup, or creates a new value instance and
     * returns that.
     */
    function &getOrCreateValueInstanceForLookup(&$oLookup) {
        $oLookup =& KTUtil::getObject('MetaData', $oLookup);
        $oValueInstance =& KTValueInstance::getByLookupSingle($oLookup);
        if (PEAR::isError($oValueInstance)) {
            return $oValueInstance;
        }
        // If we got a value instance, return it.
        if (!is_null($oValueInstance)) {
            return $oValueInstance;
        }
        // Else create one and return it.
        return KTValueInstance::createFromArray(array(
            'fieldid' => $oLookup->getDocFieldId(),
            'fieldvalueid' => $oLookup->getId(),
        ));
    }
    // }}}

    // {{{ getNextValuesForLookup
    /**
     * Used as a helper for simple conditional fieldset administration,
     * this function returns an array of lookup ids (Metadata->id) for
     * each of the columns/fields that this lookup's column affects.
     *
     * Return value:
     *
     * Associative array keyed by field_id, value is an array of lookup
     * ids.
     *
     * array(
     *      1 => array(1, 2, 3, 4),
     *      ...
     * );
     */
    function getNextValuesForLookup($oLookup) {
        /* 
         * GENERAL GAME PLAN
         *
         * Get the instance attached to the lookup, and and call
         * getNextValuesForBehaviour on its behaviour.
         *
         * If there's no instance or behaviour, return an empty array
         * for each field that the lookup's field affects.
         */
        
        $oLookup =& KTUtil::getObject('MetaData', $oLookup);
        $oInstance =& KTValueInstance::getByLookupSingle($oLookup);
        if (PEAR::isError($oInstance)) {
            $GLOBALS['default']->log->error('KTMetadataUtil::getNextValuesForLookup, got dud instance id, returned: ' . print_r($oInstance, true));
            return $oInstance;
        }
        if (!is_null($oInstance) && $oInstance->getBehaviourId()) {
            // if we have an instance, and we have a behaviour, return
            // the actual values for that behaviour.
            $oBehaviour =& KTFieldBehaviour::get($oInstance->getBehaviourId());
            if (PEAR::isError($oBehaviour)) {
                $GLOBALS['default']->log->error('KTMetadataUtil::getNextValuesForLookup, got dud behaviour id, returned: ' . print_r($oBehaviour, true));
                return $res;
            }
            return KTMetadataUtil::getNextValuesForBehaviour($oBehaviour);
        }
        // No instance or no behaviour, so send an empty array for each
        // field that we affect.
        $aChildFieldIds = KTMetadataUtil::getChildFieldIds($oLookup->getDocFieldId());
        if (PEAR::isError($aChildFieldIds)) {
            $GLOBALS['default']->log->error('KTMetadataUtil::getNextValuesForLookup, getChildFieldIds returned: ' . print_r($aChildFieldIds, true));
            return $res;
        }
        foreach ($aChildFieldIds as $iFieldId) {
            $aValues[$iFieldId] = array();
        }
        return $aValues;
    }
    // }}}

    // {{{ getNextValuesForBehaviour
    /**
     * Given a behaviour, return an array of lookup ids (Metadata->id)
     * that are available for each of the columns/fields that this
     * behaviour's column affects.
     *
     * Return value:
     *
     * Associative array keyed by field_id, value is an array of lookup
     * ids.
     *
     * array(
     *      1 => array(1, 2, 3, 4),
     *      ...
     * );
     */
    function getNextValuesForBehaviour($oBehaviour) {
        $oBehaviour =& KTUtil::getObject('KTFieldBehaviour', $oBehaviour);
        $aValues = array();
        $sTable = KTUtil::getTableName('field_behaviour_options');
        $aChildFieldIds = KTMetadataUtil::getChildFieldIds($oBehaviour->getFieldId());
        foreach ($aChildFieldIds as $iFieldId) {
            $aValues[$iFieldId] = array();
        }
        $aQuery = array(
            "SELECT field_id, instance_id FROM $sTable WHERE behaviour_id = ?",
            array($oBehaviour->getId()),
        );
        $aRows = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($aRows)) {
            return $aRows;
        }
        foreach ($aRows as $aRow) {
            $oInstance =& KTValueInstance::get($aRow['instance_id']);
            $aValues[$aRow['field_id']][] = $oInstance->getFieldValueId();
        }
        return $aValues;
    }
    // }}}

    // {{{ checkConditionalFieldsetCompleteness
    /**
     * Checks whether a conditional fieldset has the necessary
     * relationships set up to be usable - this means that for each
     * field, no matter how it is reached, there is at least one option
     * available to choose.
     */
    function checkConditionalFieldsetCompleteness($oFieldset) {
        $oFieldset =& KTUtil::getObject('KTFieldset', $oFieldset);

        if ($oFieldset->getIsConditional() == false) {
            // If we're not conditional, we are fine.
            return true;
        }

        /*
         * First, ensure at least one master field item has a behaviour
         * assigned to it.  That allows at least one item in the master
         * field to be chosen.
         */

        $iMasterFieldId = $oFieldset->getMasterFieldId();
        $sTable = KTUtil::getTableName('field_value_instances');
        $aQuery = array(
            "SELECT COUNT(id) AS cnt FROM $sTable WHERE field_id = ?",
            array($iMasterFieldId),
        );
        $iCount = DBUtil::getOneResultKey($aQuery, 'cnt');
        if (PEAR::isError($iCount)) {
            return $iCount;
        }
        $GLOBALS['default']->log->debug("Number of value instances for master field: $iCount");
        if ($iCount == 0) {
            $GLOBALS['default']->log->debug("Number of value instances for master field is zero, failing");
            return PEAR::raiseError("Master field has no selectable values");
        }
        $GLOBALS['default']->log->debug("Number of value instances for master field is positive, continuing");

        /*
         * Plan: For each behaviour that is assigned on the system,
         * ensure that it allows at least one value instance in each of
         * the fields that it needs to affect.
         */

        $sTable = KTUtil::getTableName('field_value_instances');
        $sFieldTable = KTUtil::getTableName('document_fields');
        $aQuery = array(
            "SELECT DISTINCT FV.behaviour_id AS behaviour_id FROM $sTable AS FV INNER JOIN $sFieldTable AS F ON FV.field_id = F.id WHERE F.parent_fieldset = ? AND FV.behaviour_id IS NOT NULL",
            array($oFieldset->getId()),
        );
        $aBehaviourIds = DBUtil::getResultArrayKey($aQuery, 'behaviour_id');
        if (PEAR::isError($aBehaviourIds)) {
            return $aBehaviourIds;
        }

        foreach ($aBehaviourIds as $iBehaviourId) {
            $GLOBALS['default']->log->debug("Checking behaviour id: " . $iBehaviourId);
            $oBehaviour =& KTFieldBehaviour::get($iBehaviourId);
            $sBehaviourName = $oBehaviour->getName();
            $sBehaviourHumanName = $oBehaviour->getHumanName();
            $iParentFieldId = $oBehaviour->getFieldId();
            $GLOBALS['default']->log->debug("   field is " .  $iParentFieldId);
            $aNextFields = KTMetadataUtil::getChildFieldIds($iParentFieldId);
            $oParentField =& DocumentField::get($iParentFieldId);
            $sParentFieldName = $oParentField->getName();
            $GLOBALS['default']->log->debug("   next fields must include " . print_r($aNextFields, true));
            $sTable = KTUtil::getTableName('field_behaviour_options');
            $aQuery = array(
                "SELECT DISTINCT field_id FROM $sTable WHERE behaviour_id = ?",
                array($iBehaviourId),
            );
            $aFields = DBUtil::getResultArrayKey($aQuery, 'field_id');
            $GLOBALS['default']->log->debug("   actual fields are " . print_r($aNextFields, true));
            foreach ($aNextFields as $iFieldId) {
                if (!in_array($iFieldId, $aFields)) {
                    $GLOBALS['default']->log->debug("   field $iFieldId is not included, failing");
                    $oChildField =& DocumentField::get($iFieldId);
                    $sChildFieldName = $oChildField->getName();
                    return PEAR::raiseError("Child field $sChildFieldName of parent field $sParentFieldName has no selectable values in behaviour $sBehaviourHumanName ($sBehaviourName)");
                }
            }
        }
        $GLOBALS['default']->log->debug("Got through: passed!");
        return true;
    }
    // }}}
    
    // {{{ synchroniseMetadata
    /**
     * This function takes a list of metadata values and synchronises
     * those values into the values that already exist for the field by
     * adding new values and disabling values that aren't in the new
     * list.
     *
     * XXX: Scalability: This function 
     */
    function synchroniseMetadata($oField, $aNewMetadata) {
        $iFieldId = KTUtil::getId($oField);

        $aCurrentAllValues = Metadata::getValuesByDocumentField($iFieldId);
        $aCurrentEnabledValues = Metadata::getEnabledValuesByDocumentField($iFieldId);
        $aCurrentDisabledValues = Metadata::getDisabledValuesByDocumentField($iFieldId);

        $aToBeAddedValues = array_diff($aNewMetadata, $aCurrentAllValues);
        $aToBeDisabledValues = array_diff($aCurrentEnabledValues, $aNewMetadata);
        $aToBeEnabledValues = array_intersect($aCurrentDisabledValues, $aNewMetadata);

        foreach ($aToBeAddedValues as $sValue) {
            $oMetadata =& Metadata::createFromArray(array(
                'name' => $sValue,
                'docfieldid' => $iFieldId,
            ));
        }

        foreach ($aToBeDisabledValues as $sValue) {
            $oMetadata =& Metadata::getByValueAndDocumentField($sValue, $iFieldId);
            if (!$oMetadata->getIsStuck()) {
                $oMetadata->updateFromArray(array(
                    'disabled' => true,
                ));
            }
        }

        foreach ($aToBeEnabledValues as $sValue) {
            $oMetadata =& Metadata::getByValueAndDocumentField($sValue, $iFieldId);
            if (!$oMetadata->getIsStuck()) {
                $oMetadata->updateFromArray(array(
                    'disabled' => false,
                ));
            }
        }
    }
    // }}}

    // {{{  
    function fieldsetsForDocument($oDocument) {
        global $default;
        $oDocument = KTUtil::getObject('Document', $oDocument);
        $iDocumentId = $oDocument->getId();
        $iDocumentTypeId = $oDocument->getDocumentTypeId();

        $sQuery = "SELECT DISTINCT F.id AS fieldset_id " .
            "FROM $default->documents_table AS D INNER JOIN document_fields_link AS DFL ON D.id = DFL.document_id " .
            "INNER JOIN $default->document_fields_table AS DF ON DF.ID = DFL.document_field_id " .
            "INNER JOIN $default->fieldsets_table AS F ON F.id = DF.parent_fieldset " .
            "WHERE D.id = ?";
        $aParam = array($iDocumentId);
        $aDocumentFieldsetIds = DBUtil::getResultArrayKey(array($sQuery, $aParam), 'fieldset_id');

        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => true));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($iDocumentTypeId, array('ids' => true));

        $aFieldsetIds = array_merge($aDocumentFieldsetIds, $aGenericFieldsetIds, $aSpecificFieldsetIds);
        $aFieldsetIds = array_unique($aFieldsetIds);
        sort($aFieldsetIds);

        $aRet = array();
        foreach ($aFieldsetIds as $iID) {
            $aRet[] =& call_user_func(array('KTFieldset', 'get'), $iID);
        }
        return $aRet;
    }
    // }}}
}

?>

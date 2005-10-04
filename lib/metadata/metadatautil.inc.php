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

class KTMetadataUtil {
    // {{{ getNext
    /**
     * Given a set of selected values (aCurrentSelections) for a given
     * field set (iFieldSet), returns an array (possibly empty) with the
     * keys set to newly uncovered fields, and the contents an array of
     * the value instances that are available to choose in those fields.
     */
    function getNext($iFieldSetId, $aCurrentSelections) {
        if (empty($aCurrentSelections)) {
            return array();
        }
    }
    // }}}

    // {{{ getStartFields
    function getStartFields($iFieldSetId) {
        return DocumentField::getList();

    }
    // }}}

    // {{{ removeSetsFromDocumentType
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

    // {{{ getParentFieldId
    function getParentFieldId($oField) {
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array("SELECT parent_field_id FROM $sTable WHERE child_field_id = ?",
            array($oField->getId()),
        );
        return DBUtil::getOneResultKey($aQuery, 'parent_field_id');
    }
    // }}}

    // {{{ getChildFieldIds
    function getChildFieldIds($oField) {
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array("SELECT child_field_id FROM $sTable WHERE parent_field_id = ?",
            array($oField->getId()),
        );
        return DBUtil::getResultArrayKey($aQuery, 'child_field_id');
    }
    // }}}
}

?>

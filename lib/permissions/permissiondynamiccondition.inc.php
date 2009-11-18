<?php
/**
 * $Id$
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
 *
 */

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionDynamicCondition extends KTEntity {
    var $iPermissionObjectId;
    var $iGroupId;
    var $iConditionId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iPermissionObjectId" => "permission_object_id",
        "iGroupId" => "group_id",
        "iConditionId" => "condition_id",
    );

    var $_bUsePearError = true;

    function getId() { return $this->iId; }
    function setId($iId) { $this->iId = $iId; }
    function getPermissionObjectId() { return $this->iPermissionObjectId; }
    function setPermissionObjectId($iPermissionObjectId) { $this->iPermissionObjectId = $iPermissionObjectId; }
    function getGroupId() { return $this->iGroupId; }
    function setGroupId($iGroupId) { $this->iGroupId = $iGroupId; }
    function getConditionId() { return $this->iConditionId; }
    function setConditionId($iConditionId) { $this->iConditionId = $iConditionId; }

    function _table () {
        return KTUtil::getTableName('permission_dynamic_conditions');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionDynamicCondition', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionDynamicCondition', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList2('KTPermissionDynamicCondition', $sWhereClause);
    }

    function getPermissionObjectIdList($sWhereClause, $aParams) {
        $query = 'SELECT DISTINCT(permission_object_id) FROM permission_dynamic_conditions WHERE '.$sWhereClause;
        $aQuery = array($query, $aParams);
        return DBUtil::getResultArray($aQuery);
    }

    function &getByPermissionObject($oPermissionObject) {
        $iPermissionObjectId = KTUtil::getId($oPermissionObject);
        return KTEntityUtil::getByDict('KTPermissionDynamicCondition', array(
            'permission_object_id' => $iPermissionObjectId,
        ), array(
            'multi' => true,
        ));
    }

    function &getByPermissionObjectId($iPermissionObjectId) {
        return KTEntityUtil::getByDict('KTPermissionDynamicCondition', array(
            'permission_object_id' => $iPermissionObjectId,
        ), array(
            'multi' => true,
        ));
    }


    function &getByPermissionObjectAndCondition($oPermissionObject, $oCondition) {
        $iPermissionObjectId = KTUtil::getId($oPermissionObject);
        $iConditionId = KTUtil::getId($oCondition);
        return KTEntityUtil::getByDict('KTPermissionDynamicCondition', array(
            'permission_object_id' => $iPermissionObjectId,
            'condition_id' => $iConditionId,
        ));
    }

    function saveAssignment($aPermissions) {
        $sTable = KTUtil::getTableName('permission_dynamic_assignments');
        $aQuery = array(
            "DELETE FROM $sTable WHERE dynamic_condition_id = ?",
            array($this->getId()),
        );
        $res = DBUtil::runQuery($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        }
        $aInsertOptions = array('noid' => true);
        foreach ($aPermissions as $oPermission) {
            $iPermissionId = KTUtil::getId($oPermission);
            $aInsert = array(
                'dynamic_condition_id' => $this->getId(),
                'permission_id' => $iPermissionId,
            );
            $res = DBUtil::autoInsert($sTable, $aInsert, $aInsertOptions);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
    }

    function getAssignment() {
        $sTable = KTUtil::getTableName('permission_dynamic_assignments');
        $aQuery = array(
            "SELECT permission_id FROM $sTable WHERE dynamic_condition_id = ?",
            array($this->getId()),
        );
        return DBUtil::getResultArrayKey($aQuery, 'permission_id');
    }

    // static
    function deleteByCondition($oCondition) {
        $iConditionId = KTUtil::getId($oCondition);

        $sTable = KTUtil::getTableName('permission_dynamic_conditions');
        $sAssignmentsTable = KTUtil::getTableName('permission_dynamic_assignments');
        $aQuery = array(
            sprintf('SELECT id FROM %s WHERE condition_id = ?', $sTable),
            array($iConditionId),
        );
        $aIds = DBUtil::getResultArrayKey($aQuery, 'id');


        $sParam = DBUtil::paramArray($aIds);

        $aAssignmentQuery = array(
            sprintf('DELETE FROM %s WHERE dynamic_condition_id IN (%s)', $sAssignmentsTable, $sParam),
            $aIds,
        );

        DBUtil::runQuery($aAssignmentQuery);

        $aConditionQuery = array(
            sprintf('DELETE FROM %s WHERE id IN (%s)', $sTable, $sParam),
            $aIds,
        );

        DBUtil::runQuery($aAssignmentQuery);

        return;
    }
}

?>

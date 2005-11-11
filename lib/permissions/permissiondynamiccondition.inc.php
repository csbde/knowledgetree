<?php

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

    function &getByPermissionObject($oPermissionObject) {
        $iPermissionObjectId = KTUtil::getId($oPermissionObject);
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
}

?>

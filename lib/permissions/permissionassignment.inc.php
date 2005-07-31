<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionAssignment extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $iPermissionID;
    var $iPermissionObjectID;
    var $iPermissionDescriptorID;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iPermissionID" => "permission_id",
        "iPermissionObjectID" => "permission_object_id",
        "iPermissionDescriptorID" => "permission_descriptor_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getPermissionID() { return $this->iPermissionID; }
    function setPermissionID($iPermissionID) { $this->iPermissionID = $iPermissionID; }
    function getPermissionObjectID() { return $this->iPermissionObjectID; }
    function setPermissionObjectID($iPermissionObjectID) { $this->iPermissionObjectID = $iPermissionObjectID; }
    function getPermissionDescriptorID() { return $this->iPermissionDescriptorID; }
    function setPermissionDescriptorID($iPermissionDescriptorID) { $this->iPermissionDescriptorID = $iPermissionDescriptorID; }

    function _table () {
        global $default;
        return $default->permission_assignments_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionAssignment', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionAssignment', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permission_assignments_table, 'KTPermissionAssignment', $sWhereClause);
    }

    function &getByPermissionAndObject($oPermission, $oObject) {
        return KTEntityUtil::getByDict('KTPermissionAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_object_id' => $oObject->getId(),
        ));
    }

    function &getByObjectMulti($oObject) {
        return KTEntityUtil::getByDict('KTPermissionAssignment', array(
            'permission_object_id' => $oObject->getId(),
        ), array('multi' => true));
    }
}

?>

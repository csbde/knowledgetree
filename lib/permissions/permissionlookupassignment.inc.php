<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionLookupAssignment extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $iPermissionID;
    var $iPermissionLookupID;
    var $iPermissionDescriptorID;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iPermissionID" => "permission_id",
        "iPermissionLookupID" => "permission_lookup_id",
        "iPermissionDescriptorID" => "permission_descriptor_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getPermissionID() { return $this->iPermissionID; }
    function setPermissionID($iPermissionID) { $this->iPermissionID = $iPermissionID; }
    function getPermissionLookupID() { return $this->iPermissionLookupID; }
    function setPermissionLookupID($iPermissionLookupID) { $this->iPermissionLookupID = $iPermissionLookupID; }
    function getPermissionDescriptorID() { return $this->iPermissionDescriptorID; }
    function setPermissionDescriptorID($iPermissionDescriptorID) { $this->iPermissionDescriptorID = $iPermissionDescriptorID; }

    function _table () {
        global $default;
        return $default->permission_lookup_assignments_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionLookupAssignment', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionLookupAssignment', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList2('KTPermissionLookupAssignment', $sWhereClause);
    }

    function &getByPermissionAndDescriptor($oPermission, $oDescriptor) {
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_descriptor_id' => $oDescriptor->getId(),
        ), array('multi' => true));
    }

    function &getByPermissionAndLookup($oPermission, $oLookup) {
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_lookup_id' => $oLookup->getId(),
        ));
    }

    function &_getLookupIDsByPermissionIDAndDescriptorID($iPermissionID, $iDescriptorID) {
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $iPermissionID,
            'permission_descriptor_id' => $iDescriptorID,
        ), array('multi' => true, 'ids' => true, 'idfield' => 'permission_lookup_id'));
    }

    function &findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc) {
        $aOptions = array();
        foreach ($aMapPermDesc as $iPermissionID => $iDescriptorID) {
            $aThisOptions = array();
            foreach (KTPermissionLookupAssignment::_getLookupIDsByPermissionIDAndDescriptorID($iPermissionID, $iDescriptorID) as $iPLID) {
                $aThisOptions[] = $iPLID;
            }
            $aOptions[] = $aThisOptions;
        }
        $aPLIDs = call_user_func_array('array_intersect', $aOptions);
        if (empty($aPLIDs)) {
            $oPL = KTPermissionLookup::createFromArray(array());
            $iPLID = $oPL->getID();
            foreach ($aMapPermDesc as $iPermissionID => $iDescriptorID) {
                $res = KTPermissionLookupAssignment::createFromArray(array(
                    'permissionlookupid' => $iPLID,
                    'permissionid' => $iPermissionID,
                    'permissiondescriptorid' => $iDescriptorID,
                ));
            }
            return $oPL;
        }
        sort($aPLIDs);
        $res = KTPermissionLookup::get($aPLIDs[0]);
        return $res;
    }
}

?>

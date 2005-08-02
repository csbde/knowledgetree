<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionDescriptor extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sDescriptor" => "descriptor",
        "sDescriptorText" => "descriptor_text",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getDescriptor() { return $this->sDescriptor; }
    function setDescriptor($sDescriptor) { $this->sDescriptor = $sDescriptor; }
    function getDescriptorText() { return $this->sDescriptorText; }
    function setDescriptorText($sDescriptorText) { $this->sDescriptorText = $sDescriptorText; }

    function _table () {
        global $default;
        return $default->permission_descriptors_table;
    }

    function create() {
        if (empty($this->sDescriptor)) {
            $this->sDescriptor = md5($this->sDescriptorText);
        }
        return parent::create();
    }

    function update() {
        if (empty($this->sDescriptor)) {
            $this->sDescriptor = md5($this->sDescriptorText);
        }
        return parent::update();
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionDescriptor', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionDescriptor', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permission_objects_table, 'KTPermissionDescriptor', $sWhereClause);
    }

    // STATIC
    function &getByDescriptor($sDescriptor) {
        return KTEntityUtil::getBy('KTPermissionDescriptor', 'descriptor', $sDescriptor);
    }

    function saveAllowed($aAllowed) {
        foreach ($aAllowed as $k => $aIDs) {
            if ($k === "group") {
                $this->_clearGroups();
                foreach ($aIDs as $iID) {
                    $this->_addGroup($iID);
                }
            }
        }
    }

    function _clearGroups() {
        global $default;
        $sQuery = "DELETE FROM $default->permission_descriptor_groups_table WHERE descriptor_id = ?";
        $aParams = array($this->getID());
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        return $res;
    }

    function _addGroup($iID) {
        global $default;
        $sQuery = "INSERT INTO $default->permission_descriptor_groups_table (descriptor_id, group_id) VALUES (?, ?)";
        $aParams = array($this->getID(), $iID);
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        return $res;
    }

    function hasGroups($aGroups) {
        global $default;
        if (count($aGroups) === 0) {
            return false;
        }
        $aGroupIDs = array();
        foreach ($aGroups as $oGroup) {
            $aGroupIDs[] = $oGroup->getID();
        }
        $sGroupIDs = DBUtil::paramArray($aGroupIDs);
        $sQuery = "SELECT COUNT(group_id) AS num FROM $default->permission_descriptor_groups_table
            WHERE descriptor_id = ? AND group_id IN ($sGroupIDs)";
        $aParams = array($this->getID());
        $aParams = array_merge($aParams, $aGroupIDs);
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'num');
        if (PEAR::isError($res)) {
            return $res;
        }
        if ((int)$res === 0) {
            return false;
        }
        return true;
    }

    function getGroups() {
        global $default;
        $sQuery = "SELECT group_id FROM $default->permission_descriptor_groups_table WHERE descriptor_id = ?";
        $aParams = array($this->getID());
        return DBUtil::getResultArrayKey(array($sQuery, $aParams), 'group_id');
    }

    function &getByGroup($oGroup) {
        global $default;
        $sQuery = "SELECT descriptor_id FROM $default->permission_descriptor_groups_table WHERE group_id = ?";
        $aParams = array($oGroup->getID());
        $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'descriptor_id');
        $aRet = array();
        foreach ($aIDs as $iID) {
            $aRet[] =& KTPermissionDescriptor::get($iID);
        }
        return $aRet;
    }

    function &getByGroups($aGroups) {
        global $default;
        $aGroupIDs = array();
        foreach ($aGroups as $oGroup) {
            $aGroupIDs[] = $oGroup->getID();
        }
        $sGroupIDs = DBUtil::paramArray($aGroupIDs);
        $sQuery = "SELECT DISTINCT descriptor_id FROM $default->permission_descriptor_groups_table WHERE group_id IN ( $sGroupIDs )";
        $aParams = $aGroupIDs;
        $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'descriptor_id');
        $aRet = array();
        foreach ($aIDs as $iID) {
            $aRet[] =& KTPermissionDescriptor::get($iID);
        }
        return $aRet;
    }
}

?>

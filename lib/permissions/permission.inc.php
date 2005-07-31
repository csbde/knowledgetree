<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermission extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sName;
    /** help file name */
    var $sHumanName;
    /** whether it's built into KT */
    var $bBuiltIn = false;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "bBuiltIn" => "built_in",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getBuiltIn() { return $this->bBuiltIn; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setBuiltIn($sBuiltIn) { $this->sBuiltIn = $sBuiltIn; }

    function _table () {
        global $default;
        return $default->permissions_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermission', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermission', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permissions_table, 'KTPermission', $sWhereClause);
    }

    // STATIC
    function &getByName($sName) {
        return KTEntityUtil::getBy('KTPermission', 'name', $sName);
    }
}

?>

<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionObject extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $_aFieldToSelect = array(
        "iId" => "id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }

    function _table () {
        global $default;
        return $default->permission_objects_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionObject', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionObject', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permission_objects_table, 'KTPermissionObject', $sWhereClause);
    }
}

?>

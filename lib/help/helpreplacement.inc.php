<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTHelpReplacement extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sName;
    /** replacement string */
    var $sDescription;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sDescription" => "description",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getDescription() { return $this->sDescription; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setDescription($sDescription) { $this->sDescription = $sDescription; }

    function _table () {
        global $default;
        return $default->help_replacement_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTHelpReplacement', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTHelpReplacement', $aOptions);
    }

    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->help_replacement_table, 'KTHelpReplacement', $sWhereClause);
    }

    function &getByName($sName) {
        return KTEntityUtil::getBy('KTHelpReplacement', 'name', $sName);
    }
}

?>

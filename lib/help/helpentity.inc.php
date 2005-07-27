<?php

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");

class KTHelpEntity extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sSection;
    /** replacement string */
    var $sFilename;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sSection" => "fSection",
        "sFilename" => "help_info",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getSection() { return $this->sSection; }
    function getFilename() { return $this->sFilename; }
    function setID($iId) { $this->iId = $iId; }
    function setSection($sSection) { $this->sSection = $sSection; }
    function setFilename($sFilename) { $this->sFilename = $sFilename; }

    function _table () {
        global $default;
        return $default->help_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTHelpEntity', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTHelpEntity', $aOptions);
    }

    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->help_table, 'KTHelpEntity', $sWhereClause);
    }

    function checkReplacement() {
        $oHelpReplacement = KTHelpReplacement::getByName($this->sFilename);
        if (PEAR::isError($oHelpReplacement)) {
            return false;
        }
        return $oHelpReplacement;
    }

    function &getByFilename($sFilename) {
        return KTEntityUtil::getBy('KTHelpEntity', 'help_info', $sFilename);
    }
}

?>

<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTDocumentTransactionType extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sName;
    /** help file name */
    var $sName;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sNamespace" => "namespace",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getNamespace() { return $this->sNamespace; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setNamespace($sNamespace) { $this->sNamespace = $sNamespace; }

    function _table () {
        global $default;
        return $default->transaction_types_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTDocumentTransactionType', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTDocumentTransactionType', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permissions_table, 'KTDocumentTransactionType', $sWhereClause);
    }

    // STATIC
    function &getByNamespace($sNamespace) {
        return KTEntityUtil::getBy('KTDocumentTransactionType', 'namespace', $sNamespace);
    }
}

?>

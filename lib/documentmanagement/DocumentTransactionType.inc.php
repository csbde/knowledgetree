<?php

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 

class KTDocumentTransactionType extends KTEntity {
    /** primary key */
    var $iId = -1;
    var $sName;
    var $sNamespace;

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
        return KTUtil::getTableName('transaction_types');
    }

    function &get($iId) { return KTEntityUtil::get('KTDocumentTransactionType', $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTDocumentTransactionType', $aOptions); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTDocumentTransactionType', $sWhereClause); }
    function &getByNamespace($sNamespace) { return KTEntityUtil::getBy('KTDocumentTransactionType', 'namespace', $sNamespace); }
}

?>

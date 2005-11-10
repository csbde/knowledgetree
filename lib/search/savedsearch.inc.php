<?php

require_once(KT_LIB_DIR . '/ktentity.inc');

/**
 * Saved searches allow for common searches to be saved.
 *
 * There are two main purposes - saving search setup time for users, and 
 * creating conditions on documents that will determine if someone has
 * permission on the document or if a workflow process can continue.
 */
class KTSavedSearch extends KTEntity {
    /**
     * Human-understandable name for the search.
     *
     * Only unique in combination with iUserId.
     */
    var $sName;
    /**
     * Unique name to look up or add a search from a plugin.
     */
    var $sNamespace;
    /**
     * Whether the search is a user-saved search or a document
     * condition.
     */
    var $bIsCondition = false;
    /**
     * Determines if the search is complete already, or if it needs
     * certain fields to be filled in.  (XXX: Not implemented yet)
     */
    var $bIsComplete = true;
    /**
     * Which user saved this search.  If no user is given, it is a
     * system-wide search.
     */
    var $iUserId;
    /**
     * The saved search in an array.  This is serialised before it is
     * written into the database.
     */
    var $aSearch;

    // {{{ KTEntity setup
    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sNamespace" => "namespace",
        "bIsCondition" => "is_condition",
        "bIsComplete" => "is_complete",
        "iUserId" => "user_id",
        "aSearch" => "search",
    );

    var $_bUsePearError = true;

    function _table () {
        global $default;
        return $default->saved_searches_table;
    }
    function _fieldValues () {
        $aRet = parent::_fieldValues();
        $aRet['search'] = base64_encode(serialize($aRet['search']));
        return $aRet;
    }

    function load($iId) {
        $res = parent::load($iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        $this->aSearch = unserialize(base64_decode($this->aSearch));
        return $res;
    }

    // }}}

    function getId() { return $this->iId; }
    function getName() { return $this->sName; }
    function getNamespace() { return $this->sNamespace; }
    function getIsCondition() { return $this->bIsCondition; }
    function getIsComplete() { return $this->bIsComplete; }
    function getUserId() { return $this->iUserId; }
    function getSearch() { return $this->aSearch; }
    function setId($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setNamespace($sNamespace) { $this->sNamespace = $sNamespace; }
    function setIsCondition($bIsCondition) { $this->bIsCondition = $bIsCondition; }
    function setIsComplete($bIsComplete) { $this->bIsComplete = $bIsComplete; }
    function setUserId($iUserId) { $this->iUserId = $iUserId; }
    function setSearch($aSearch) { $this->aSearch = $aSearch; }

    function &createFromArray($aValues) {
        return KTEntityUtil::createFromArray('KTSavedSearch', $aValues);
    }

    function &getByNamespace($sNamespace) {
        return KTEntityUtil::getBy('KTSavedSearch', 'namespace', $sNamespace);
    }

    function &get($iId) {
        return KTEntityUtil::get('KTSavedSearch', $iId);
    }

    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTSavedSearch', $sWhereClause);
    }

    function &getConditions() {
        return KTEntityUtil::getByDict('KTSavedSearch', array(
            'is_condition' => true,
        ), array(
            'multi' => true,
        ));
    }
    function &getSystemSearches($sWhereClause = null) {
        return KTEntityUtil::getByDict('KTSavedSearch', array(
            'is_condition' => false,
            'user_id' => null,
        ), array(
            'multi' => true,
            'noneok' => true,
        ));
    }
}

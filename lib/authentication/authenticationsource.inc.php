<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTAuthenticationSource extends KTEntity {
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
        "sNamespace" => "namespace",
        "sAuthenticationProvider" => "authentication_provider",
        "sConfig" => "config",
        "bIsUserSource" => "is_user_source",
        "bIsGroupSource" => "is_group_source",
    );

    var $_bUsePearError = true;

    function getName() { return $this->sName; }
    function getNamespace() { return $this->sNamespace; }
    function getAuthenticationProvider() { return $this->sAuthenticationProvider; }
    function getConfig() { return $this->sConfig; }
    function getIsUserSource() { return $this->bIsUserSource; }
    function getIsGroupSource() { return $this->bIsGroupSource; }
    function setName($sName) { $this->sName = $sName; }
    function setNamespace($sNamespace) { $this->sNamespace = $sNamespace; }
    function setAuthenticationProvider($sAuthenticationProvider) { $this->sAuthenticationProvider = $sAuthenticationProvider; }
    function setConfig($sConfig) { $this->sConfig = $sConfig; }
    function getIsUserSource($bIsUserSource) { $this->bIsUserSource = $bIsUserSource; }
    function getIsGroupSource($bIsGroupSource) { $this->bIsGroupSource = $bIsGroupSource; }

    function _table () {
        global $default;
        return $default->authentication_sources_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTAuthenticationSource', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTAuthenticationSource', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList2('KTAuthenticationSource', $sWhereClause);
    }

    // STATIC
    function &getByNamespace($sNamespace) {
        return KTEntityUtil::getBy('KTAuthenticationSource', 'namespace', $sNamespace);
    }
}

?>

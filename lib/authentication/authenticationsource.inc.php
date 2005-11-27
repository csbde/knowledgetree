<?php

require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/ktentity.inc");

class KTAuthenticationSource extends KTEntity {
    var $sName;
    var $sNamespace;
    var $sAuthenticationProvider;
    var $sConfig = "";
    var $bIsUserSource = false;
    var $bIsGroupSource = false;

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
    function setIsUserSource($bIsUserSource) { $this->bIsUserSource = $bIsUserSource; }
    function setIsGroupSource($bIsGroupSource) { $this->bIsGroupSource = $bIsGroupSource; }

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

    function &getForUser($oUser) {
        $oUser =& KTUtil::getObject('User', $oUser);
        $iAuthenticationSourceId = $oUser->getAuthenticationSourceId();
        if (empty($iAuthenticationSourceId)) {
            return null;
        }
        return KTAuthenticationSource::get($iAuthenticationSourceId);
    }
}

?>

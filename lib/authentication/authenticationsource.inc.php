<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

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

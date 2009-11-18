<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTAuthenticationSource extends KTEntity {
    var $sName;
    var $sNamespace;
    var $sAuthenticationProvider;
    var $sConfig = '';
    var $bIsUserSource = false;
    var $bIsGroupSource = false;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'sNamespace' => 'namespace',
        'sAuthenticationProvider' => 'authentication_provider',
        'sConfig' => 'config',
        'bIsUserSource' => 'is_user_source',
        'bIsGroupSource' => 'is_group_source',
    );

    var $_bUsePearError = true;

    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getNamespace() { return $this->sNamespace; }
    function getAuthenticationProvider() { return $this->sAuthenticationProvider; }
    function getConfig() { return $this->sConfig; }
    function getIsUserSource() { return $this->bIsUserSource; }
    function getIsGroupSource() { return $this->bIsGroupSource; }
    function setName($sName) { $this->sName = sanitizeForSQL($sName); }
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

    function &getByAuthenticationProvider($sProvider) {
        return KTEntityUtil::getBy('KTAuthenticationSource', 'authentication_provider', $sProvider);
    }

    function &getSources() {
        return KTEntityUtil::getList2('KTAuthenticationSource');
    }
}

?>

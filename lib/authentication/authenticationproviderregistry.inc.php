<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

/**
 * This is where all authentication providers register themselves as
 * available to the system.  Only the classes are registered here, not
 * specific instances.
 *
 * For instance, an LDAP authentication provider is registered.  It
 * can't, by itself, perform any authentication, as it is not
 * configured.
 *
 * The authenticators table in the database lists specific instances
 * configured in the system.  It contains it's own name (for humans to
 * differentiate between instances), it's own namespace name (for the
 * system and plugins to be able to find it accurately), it's
 * authentication provider namespace name, and some configuration data
 * that is handed over to the authentication provider instance to
 * configure itself.
 *
 * If a user has no authenticator set up, the KnowledgeTree
 * Authentication Provider is used.  This is hard-coded to use the
 * KnowledgeTree users table to check the password against.
 */
class KTAuthenticationProviderRegistry {
    var $_aAuthenticationProvidersInfo = array();
    var $_aAuthenticationProviders = array();

    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTAuthenticationProviderRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTAuthenticationProviderRegistry'] = new KTAuthenticationProviderRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTAuthenticationProviderRegistry'];
    }
    // }}}

    function registerAuthenticationProvider($name, $class, $nsname, $path = '', $sPlugin = null) {
        $this->_aAuthenticationProvidersInfo[$nsname] = array($name, $class, $nsname, $path, $sPlugin);
    }

    function getAuthenticationProviderInfo($nsname) {
        return $this->_aAuthenticationProviderInfo[$nsname];
    }

    function &getAuthenticationProvider($nsname) {
        $oProvider =& KTUtil::arrayGet($this->_aAuthenticationProviders, $nsname);
        if ($oProvider) {
            return $oProvider;
        }
        $aInfo = $this->_aAuthenticationProvidersInfo[$nsname];
        $sClass = $aInfo[1];
        $sPath = $aInfo[3];
        if ($sPath) {
            require_once($sPath);
        }
        $oProvider =& new $sClass;
        $this->_aAuthenticationProviders[$nsname] =& $oProvider;
        return $oProvider;
    }

    function getAuthenticationProvidersInfo() {
        return array_values($this->_aAuthenticationProvidersInfo);
    }
}

?>

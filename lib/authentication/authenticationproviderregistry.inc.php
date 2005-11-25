<?php

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
        if (!KTUtil::arrayGet($GLOBALS, 'oKTAuthenticationProviderRegistry')) {
            $GLOBALS['oKTAuthenticationProviderRegistry'] = new KTAuthenticationProviderRegistry;
        }
        return $GLOBALS['oKTAuthenticationProviderRegistry'];
    }
    // }}}

    function registerAuthenticationProvider($name, $class, $nsname, $path = "", $sPlugin = null) {
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

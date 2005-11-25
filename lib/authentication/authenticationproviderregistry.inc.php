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
        $this->_aAuthenticationProviders[$nsname] = array($name, $class, $path, $nsname, $sPlugin);
    }

    function getAuthenticationProvider($nsname) {
        return $this->_aAuthenticationProviders[$nsname];
    }

    function getAuthenticationProviders() {
        return array_values($this->_aAuthenticationProviders);
    }
}

?>

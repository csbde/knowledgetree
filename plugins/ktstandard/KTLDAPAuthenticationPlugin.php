<?php

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class KTLDAPAuthenticationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.ldapauthentication.plugin";

    function setup() {
        $this->registerAuthenticationProvider('LDAP Authentication',
            'KTLDAPAuthenticationProvider', 'ktstandard.authentication.ldapprovider',
            'ldap/ldapauthenticationprovider.inc.php');
        $this->registerAuthenticationProvider('ActiveDirectory Authentication',
            'KTActiveDirectoryAuthenticationProvider', 'ktstandard.authentication.adprovider',
            'ldap/activedirectoryauthenticationprovider.inc.php');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTLDAPAuthenticationPlugin', 'ktstandard.ldapauthentication.plugin', __FILE__);

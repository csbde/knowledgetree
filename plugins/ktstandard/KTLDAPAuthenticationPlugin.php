<?php

class KTLDAPAuthenticationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.ldapauthentication.plugin";
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTLDAPAuthenticationPlugin', 'ktstandard.ldapauthentication.plugin', __FILE__);
$oPlugin =& $oPluginRegistry->getPlugin('ktstandard.ldapauthentication.plugin');

$oPlugin->registerAuthenticationProvider('LDAP Authentication', 'KTLDAPAuthenticationProvider', 'ktstandard.authentication.ldapprovider', 'ldap/ldapauthenticationprovider.inc.php');

$oPlugin->register();

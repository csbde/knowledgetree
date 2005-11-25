<?php

class KTAuthenticationPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.ldapauthentication.plugin";
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTLDAPAuthenticationPlugin', 'ktstandard.ldapauthentication.plugin', __FILE__);
$oPlugin =& $oPluginRegistry->getPlugin('ktstandard.subscriptions.plugin');

$oPlugin->registerAuthenticationProvider('LDAP Authentication', 'KTLDAPAuthenticationProvider', 'ktstandard.authentication.ldapprovider', 'ldap/ldapauthenticationprovider.inc.php');


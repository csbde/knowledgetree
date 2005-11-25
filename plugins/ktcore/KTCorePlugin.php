<?php

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTCorePlugin extends KTPlugin {
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCorePlugin', 'ktcore.plugin', __FILE__);

$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

require_once('KTDocumentActions.php');
require_once('KTFolderActions.php');
require_once('KTPortlets.php');
require_once('KTPermissions.php');
require_once('KTAdminPlugins.php');

$oPlugin->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', 'Authentication', 'FIXME: describe authentication', 'authentication/authenticationadminpage.inc.php');
$oPlugin->registerAuthenticationProvider('LDAP Authentication', 'KTLDAPAuthenticationProvider', 'ktstandard.authentication.ldapprovider', 'authentication/ldapauthenticationprovider.inc.php');

$oPlugin->register();

require_once(KT_LIB_DIR . '/storage/ondiskpathstoragemanager.inc.php');


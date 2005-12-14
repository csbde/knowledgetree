<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR .  '/authentication/authenticationproviderregistry.inc.php');


$oSource = KTAuthenticationSource::get(2);
$sProvider = $oSource->getAuthenticationProvider();
$oRegistry = KTAuthenticationProviderRegistry::getSingleton();
$oProvider =& $oRegistry->getAuthenticationProvider($sProvider);
$oAuthenticator = $oProvider->getAuthenticator($oSource);
$oUser = User::getByUserName('nbm');
$foo = $oAuthenticator->checkPassword($oUser, 'asdfa');
var_dump($foo);

?>

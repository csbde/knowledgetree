<?php

require_once(KT_LIB_DIR .  '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR .  '/authentication/builtinauthenticationprovider.inc.php');
require_once(KT_LIB_DIR .  '/authentication/authenticationproviderregistry.inc.php');

class KTAuthenticationUtil {
    function checkPassword ($oUser, $sPassword) {
        $oUser =& KTUtil::getObject('User', $oUser);
        $oAuthenticator =& KTAuthenticationUtil::getAuthenticatorForUser($oUser);
        return $oAuthenticator->checkPassword($oUser, $sPassword);
    }

    function &getAuthenticatorForUser($oUser) {
        $iAuthenticationSourceId = $oUser->getAuthenticationSourceId();
        if (empty($iAuthenticationSourceId)) {
            $oProvider = new KTBuiltinAuthenticationProvider;
        } else {
            $oSource = KTAuthenticationSource::get($iAuthenticationSourceId);
            $sProvider = $oSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider = $oRegistry->getAuthenticationProvider($sProvider);
        }
        return $oProvider->getAuthenticator($oSource);
    }
}

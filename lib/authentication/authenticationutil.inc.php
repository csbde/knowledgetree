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
        $iSourceId = $oUser->getAuthenticationSourceId();
        return KTAuthenticationUtil::getAuthenticatorForSource($iSourceId);
    }

    function &getAuthenticatorForSource($oSource) {
        if ($oSource) {
            $oSource =& KTUtil::getObject('KTAuthenticationSource', $oSource);
            $sProvider = $oSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);
        } else {
            $oProvider = new KTBuiltinAuthenticationProvider;
        }
        return $oProvider->getAuthenticator($oSource);
    }

    function synchroniseGroupToSource($oGroup) {
        $oGroup =& KTUtil::getObject('Group', $oGroup);
        $iSourceId = $oGroup->getAuthenticationSourceId();
        $oAuthenticator = KTAuthenticationUtil::getAuthenticatorForSource($iSourceId);
        return $oAuthenticator->synchroniseGroup($oGroup);
    }
}

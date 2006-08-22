<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

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

    function &getAuthenticationProviderForUser($oUser) {
        $iSourceId = $oUser->getAuthenticationSourceId();
        return KTAuthenticationUtil::getAuthenticationProviderForSource($iSourceId);
    }

    function &getAuthenticatorForSource($oSource) {
        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForSource($oSource);
        return $oProvider->getAuthenticator($oSource);
    }

    function &getAuthenticationProviderForSource($oSource) {
        if ($oSource) {
            $oSource =& KTUtil::getObject('KTAuthenticationSource', $oSource);
            $sProvider = $oSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);
        } else {
            $oProvider =& new KTBuiltinAuthenticationProvider;
        }
        return $oProvider;
    }

    function synchroniseGroupToSource($oGroup) {
        $oGroup =& KTUtil::getObject('Group', $oGroup);
        $iSourceId = $oGroup->getAuthenticationSourceId();
        $oAuthenticator = KTAuthenticationUtil::getAuthenticatorForSource($iSourceId);
        return $oAuthenticator->synchroniseGroup($oGroup);
    }

    function autoSignup($sUsername, $aExtra) {
        $aSources = KTAuthenticationSource::getSources();
        foreach ($aSources as $oSource) {
            $oProvider = KTAuthenticationUtil::getAuthenticationProviderForSource($oSource);
            $res = $oProvider->autoSignup($sUsername, $aExtra, $oSource);
            if ($res) {
                return $res;
            }
        }
        return false;
    }
}

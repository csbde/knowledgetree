<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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

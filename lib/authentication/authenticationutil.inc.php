<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR .  '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR .  '/authentication/builtinauthenticationprovider.inc.php');
require_once(KT_LIB_DIR .  '/authentication/authenticationproviderregistry.inc.php');

class KTAuthenticationUtil {
    function checkPassword ($oUser, $sPassword) {
        $oUser =& KTUtil::getObject('User', $oUser);
        if ($oUser->getDisabled() == 2)
        {
        	return false;
        }
        $oAuthenticator =& KTAuthenticationUtil::getAuthenticatorForUser($oUser);
        return $oAuthenticator->checkPassword($oUser, $sPassword);
    }

    function &getAuthenticatorForUser($oUser) {
        $iSourceId = $oUser->getAuthenticationSourceId();
        return KTAuthenticationUtil::getAuthenticatorForSource($iSourceId);
    }

    function &getAuthenticationProviderForUser($oUser) {
        if (PEAR::isError($oUser)) { var_dump($oUser); }
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
            $oProvider = new KTBuiltinAuthenticationProvider;
        }
        return $oProvider;
    }

    function synchroniseGroupToSource($oGroup) {
        $oGroup =& KTUtil::getObject('Group', $oGroup);
        $iSourceId = $oGroup->getAuthenticationSourceId();
        $oAuthenticator = KTAuthenticationUtil::getAuthenticatorForSource($iSourceId);
        return $oAuthenticator->synchroniseGroup($oGroup);
    }

    function autoSignup($sUsername, $sPassword, $aExtra) {
        $aSources = KTAuthenticationSource::getSources();
        foreach ($aSources as $oSource) {
            $oProvider = KTAuthenticationUtil::getAuthenticationProviderForSource($oSource);
            $res = $oProvider->autoSignup($sUsername, $sPassword, $aExtra, $oSource);
            if ($res) {
                return $res;
            }
        }
        return false;
    }
}

<?php
/**
 * $Id: $
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
 */

require_once(KT_LIB_DIR . '/session/Session.inc');

class loginUtil
{
    /**
     * Check if the user is already logged in or if anonymous login is enabled
     *
     * @return boolean false if the user is logged in
     */
    function check() {
        $session = new Session();
        $sessionStatus = $session->verify();

        if ($sessionStatus === true) { // the session is valid
            if ($_SESSION['userID'] == -2 && $default->allowAnonymousLogin) {
                // Anonymous user - we want to login
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Verify the user session
     *
     */
    function do_providerVerify() {
        $this->session = new Session();
        $sessionStatus = $this->session->verify();
        if ($sessionStatus !== true) { // the session is not valid
            $this->redirectToMain();
        }
        $this->oUser =& User::get($_SESSION['userID']);
        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForUser($this->oUser);
        $oProvider->subDispatch($this);
        exit(0);
    }

    /**
     * Log the user into the system
     *
     * @param unknown_type $oUser
     * @return unknown
     */
    function performLogin(&$oUser) {
        if (!is_a($oUser, 'User')) {
        }

        $session = new Session();
        $sessionID = $session->create($oUser);
        if (PEAR::isError($sessionID)) {
            return $sessionID;
        }

		$redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

        // DEPRECATED initialise page-level authorisation array
        $_SESSION["pageAccess"] = NULL;

        $cookietest = KTUtil::randomString();
        setcookie("CookieTestCookie", $cookietest, 0);

        $this->redirectTo('checkCookie', array(
            'cookieVerify' => $cookietest,
            'redirect' => $redirect,
        ));
        exit(0);
    }

    function handleUserDoesNotExist($username, $password, $aExtra = null) {
        if (empty($aExtra)) {
            $aExtra = array();
        }

        // Check if the user has been deleted before allowing auto-signup
        $delUser = User::checkDeletedUser($username);

        if($delUser){
            return ;
        }

        $oKTConfig = KTConfig::getSingleton();
        $allow = $oKTConfig->get('session/allowAutoSignup', true);

        if($allow){
            $res = KTAuthenticationUtil::autoSignup($username, $password, $aExtra);
            if (empty($res)) {
                return $res;
            }
            if (is_a($res, 'User')) {
                $this->performLogin($res);
            }
            if (is_a($res, 'KTAuthenticationSource')) {
                $_SESSION['autosignup'] = $aExtra;
                $this->redirectTo('autoSignup', array(
                    'source_id' => $res->getId(),
                    'username' => $username,
                ));
                exit(0);
            }
        }
    }
}
?>
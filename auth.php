<?php
/**
 * $Id$
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 */

// main library routines and defaults
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/session/Session.inc');

class AuthenticationDispatcher extends KTDispatcher {

    public function do_main()
    {
        if (!empty($_POST['SAMLResponse']) && KTPluginUtil::pluginIsActive('auth.onelogin.plugin')) {
            $this->onelogin();
        }

        $this->relocate('login.php?errorMessage=Login+failed.++Please+check+your+username+and+password%2C+and+try+again.');
    }

    // TODO Consider moving this code inside the onelogin plugin.
    private function onelogin()
    {
        $oneloginErrorMessage = 'Login+failed.++Please+check+your+onelogin+username+and+try+again';

        try {
            require_once(KTPluginUtil::getPluginPath('auth.onelogin.plugin') . 'SAMLConsumer.inc.php');

            $consumer = new SAMLConsumer();
            if ($consumer->authenticate($_POST['SAMLResponse'])) {
                $this->startOneloginSession($consumer->getAuthenticatedUser(), $oneloginErrorMessage);
            }
            else {
                $this->relocate("login.php?errorMessage=$oneloginErrorMessage");
            }
        }
        catch (Exception $e) {
            $this->relocate("login.php?errorMessage=$oneloginErrorMessage.");
        }
    }

    private function startOneloginSession($user, $oneloginErrorMessage)
    {
        global $default;

        $user = $this->getOneloginUser($user, $oneloginErrorMessage);
        $this->createOneloginSession($user, $oneloginErrorMessage);

        $default->log->info('User logged in (OneLogin SAML authentication)');

        $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));
        $cookietest = KTUtil::randomString();
        setcookie('CookieTestCookie', $cookietest, 0);

        // make sure to set referrer to local (does not appear to work)
        header("Referer: {$_SERVER['HTTP_HOST']}");
        $this->redirectTo('checkCookie', array('cookieVerify' => $cookietest, 'redirect' => $redirect));
        exit(0);
    }

    private function getOneloginUser($user, $oneloginErrorMessage)
    {
        global $default;

        // Determine user from supplied username.
        $res = DBUtil::getOneResult("SELECT id FROM users WHERE username = '$user'");
        if (PEAR::isError($res) || empty($res['id'])) {
            $default->log->error("Error finding user $user (OneLogin SAML authentication)"
                                . (PEAR::isError($res) ? ': ' . $res->getMessage() : ''));
            $this->relocate("login.php?errorMessage=$oneloginErrorMessage");
        }

        // set user as logged in
        $user = User::get($res['id']);
        if (PEAR::isError($user)) {
            $default->log->error("User $user does not exist (OneLogin SAML authentication): " . $user->getMessage());
            $this->relocate("login.php?errorMessage=$oneloginErrorMessage");
        }

        return $user;
    }

    private function createOneloginSession($user, $oneloginErrorMessage)
    {
        global $default;
        
        $session = new Session();
        $sessionID = $session->create($user);
        if (PEAR::isError($sessionID)) {
            $default->log->error("Error creating session for user $user (OneLogin SAML authentication): " . $sessionID->getMessage());
            $this->relocate("login.php?errorMessage=$oneloginErrorMessage");
        }

        // Add a flag to check for bulk downloads after login is succesful; this will be cleared in the code which checks.
        $_SESSION['checkBulkDownload'] = true;

        // DEPRECATED initialise page-level authorisation array
        $_SESSION['pageAccess'] = null;
    }

    public function check()
    {
        $oKTConfig = KTConfig::getSingleton();
        $this->session = new Session();
        $sessionStatus = $this->session->verify();
        if ($sessionStatus === true) { // the session is valid
            // User is already logged in - get the redirect
            $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

            $cookietest = KTUtil::randomString();
            setcookie("CookieTestCookie", $cookietest, 0);

            $this->redirectTo(
                        'checkCookie',
                        array(
                            'cookieVerify' => $cookietest,
                            'redirect' => $redirect,
                        )
            );
            exit(0);
            // The old way -> doesn't take the redirect into account
            //exit(redirect(generateControllerLink('dashboard')));
        }

        return true;
    }

    public function do_checkCookie()
    {
        $cookieTest = KTUtil::arrayGet($_COOKIE, "CookieTestCookie", null);
        $cookieVerify = KTUtil::arrayGet($_REQUEST, 'cookieVerify', null);

        $url = $_SERVER["PHP_SELF"];
        $queryParams = array();
        $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

        if (!empty($redirect)) {
            $queryParams[] = 'redirect='. urlencode($redirect);
        }

        if ($cookieTest !== $cookieVerify) {
            Session::destroy();
            $this->simpleRedirectToMain(_kt('You must have cookies enabled to use the document management system.'), $url, $queryParams);
            exit(0);
        }

        // check for a location to forward to
        if (!empty($redirect)) {
            $url = $redirect;
            // else redirect to the dashboard if there is none
        }
        else {
            $url = KTUtil::kt_url();

            $config = KTConfig::getSingleton();
            $redirectToBrowse = $config->get('KnowledgeTree/redirectToBrowse', false);
            $redirectToDashboardList = $config->get('KnowledgeTree/redirectToBrowseExceptions', '');

            if ($redirectToBrowse) {
                $exceptionsList = explode(',', str_replace(' ','',$redirectToDashboardList));
                $user = User::get($_SESSION['userID']);
                $username = $user->getUserName();
                $url .= (in_array($username, $exceptionsList))?'/dashboard.php':'/browse.php';
            }
            else {
                $url .=  '/dashboard.php';
            }
        }

        exit(redirect($url));
    }

    private function relocate($location)
    {
        // make sure to set referrer to local (does not appear to work)
        header("Referer: {$_SERVER['HTTP_HOST']}");
        // TODO use redirectTo instead?
        header("Location: {$location}");
        exit(0);
    }

}

$dispatcher = new AuthenticationDispatcher();
$dispatcher->dispatch();

?>

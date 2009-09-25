<?php
/**
 * $Id$
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
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
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');
require_once(KT_LIB_DIR . '/authentication/interceptorregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

class LoginPageDispatcher extends KTDispatcher {

    function check() {
        $oKTConfig = KTConfig::getSingleton();
        $this->session = new Session();
        $sessionStatus = $this->session->verify();
        if ($sessionStatus === true) { // the session is valid
            if ($_SESSION['userID'] == -2 && $oKTConfig->get('allowAnonymousLogin', false)) {
                ; // that's ok - we want to login.
            }
            else {
                // User is already logged in - get the redirect
                $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

                $cookietest = KTUtil::randomString();
                setcookie("CookieTestCookie", $cookietest, 0);

                $this->redirectTo('checkCookie', array(
                    'cookieVerify' => $cookietest,
                    'redirect' => $redirect,
                ));
                exit(0);
                // The old way -> doesn't take the redirect into account
                //exit(redirect(generateControllerLink('dashboard')));
            }
        }
        return true;
    }

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

    function performLogin(&$oUser) {
        if (!is_a($oUser, 'User')) {
            #var_dump($oUser);
            #var_dump(PEAR::raiseError());
        }

        /*
        Removing the code that redirects to the dashboard as it breaks linking in from external documents.
        The fix below doesn't work if the users are behind a proxy server.

        // If the last user from the same IP address timed out within the last hour then redirect to the dashboard
        // Otherwise allow any other redirect to continue.
        // The user might still be taken to the last page of the previous users session but
        // if we always redirect to dashboard then we break other features such as linking in from emails or documents.
        if (checkLastSessionUserID($oUser->getId()))
        {
        	$_REQUEST['redirect'] = generateControllerLink('dashboard');
        }
        */

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

    function do_main() {
        global $default;

        KTUtil::save_base_kt_url();

        $oUser =& KTInterceptorRegistry::checkInterceptorsForAuthenticated();
        if (is_a($oUser, 'User')) {
            $res = $this->performLogin($oUser);
            if ($res) {
                $oUser = array($res);
            }
        }
        if (is_array($oUser) && count($oUser)) {
            if (empty($_REQUEST['errorMessage'])) {
                $_REQUEST['errorMessage'] = array();
            } else {
                $_REQUEST['errorMessage'] = array($_REQUEST['errorMessage']);
            }
            foreach ($oUser as $oError) {
                $_REQUEST['errorMessage'][] = $oError->getMessage();
            }
            $_REQUEST['errorMessage'] = join('. <br /> ', $_REQUEST['errorMessage']);
        }


        KTInterceptorRegistry::checkInterceptorsForTakeOver();

        $this->check(); // bounce here, potentially.
        header('Content-type: text/html; charset=UTF-8');

        $errorMessage = KTUtil::arrayGet($_REQUEST, 'errorMessage');
        session_start();

        $errorMessageConfirm = $_SESSION['errormessage']['login'];

        $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

        $oReg =& KTi18nregistry::getSingleton();
        $aRegisteredLangs = $oReg->geti18nLanguages('knowledgeTree');
        $aLanguageNames = $oReg->getLanguages('knowledgeTree');
        $aRegisteredLanguageNames = array();

        if(!empty($aRegisteredLangs))
        {
            foreach (array_keys($aRegisteredLangs) as $sLang) {
                $aRegisteredLanguageNames[$sLang] = $aLanguageNames[$sLang];
            }
        }
        $sLanguageSelect = $default->defaultLanguage;

        // extra disclaimer, if plugin is enabled
        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');
        if (!PEAR::isError($oPlugin) && !is_null($oPlugin)) {
            $sDisclaimer = $oPlugin->getLoginDisclaimer();
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/login");
        $aTemplateData = array(
              "context" => $this,
              'errorMessage' => $errorMessage,
              'errorMessageConfirm' => $errorMessageConfirm,
              'redirect' => $redirect,
              'systemVersion' => $default->systemVersion,
              'versionName' => $default->versionName,
              'languages' => $aRegisteredLanguageNames,
              'selected_language' => $sLanguageSelect,
	      	  'disclaimer' => $sDisclaimer,
			  'smallVersion' => substr($default->versionName,-17),
        );
        return $oTemplate->render($aTemplateData);
    }

    function simpleRedirectToMain($errorMessage, $url, $params) {
        $params[] = 'errorMessage='. urlencode($errorMessage);
        $url .= '?' . join('&', $params);
        redirect($url);
        exit(0);
    }

    function do_login() {
        $aExtra = array();
        $oUser =& KTInterceptorRegistry::checkInterceptorsForAuthenticated();
        if (is_a($oUser, 'User')) {
            $res = $this->performLogin($oUser);
            if ($res) {
                $oUser = array($res);
            }
        }
        if (is_array($oUser)) {
            foreach ($oUser as $oError) {
                if (is_a($oError, 'KTNoLocalUser')) {
                    $aExtra = kt_array_merge($aExtra, $oError->aExtra);
                }
            }
        }

        KTInterceptorRegistry::checkInterceptorsForTakeOver();

        $this->check();
        global $default;

        $language = KTUtil::arrayGet($_REQUEST, 'language');
        if (empty($language)) {
            $language = $default->defaultLanguage;
        }
        setcookie("kt_language", $language, 2147483647, '/');

        $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

        $url = $_SERVER["PHP_SELF"];
        $queryParams = array();

        if (!empty($redirect)) {
            $queryParams[] = 'redirect=' . urlencode($redirect);
        }

        $username = KTUtil::arrayGet($_REQUEST,'username');
        $password = KTUtil::arrayGet($_REQUEST,'password');

        if (empty($username)) {
            $this->simpleRedirectToMain(_kt('Please enter your username.'), $url, $queryParams);
        }

        $oUser =& User::getByUsername($username);
        if (PEAR::isError($oUser) || ($oUser === false)) {
            if (is_a($oUser, 'ktentitynoobjects')) {
                $this->handleUserDoesNotExist($username, $password, $aExtra);
            }
            $this->simpleRedirectToMain(_kt('Login failed.  Please check your username and password, and try again.'), $url, $queryParams);
            exit(0);
        }

        if (empty($password)) {
            $this->simpleRedirectToMain(_kt('Please enter your password.'), $url, $queryParams);
        }

        $authenticated = KTAuthenticationUtil::checkPassword($oUser, $password);

        if (PEAR::isError($authenticated)) {
            $this->simpleRedirectToMain(_kt('Authentication failure.  Please try again.'), $url, $queryParams);
            exit(0);
        }

        if ($authenticated !== true) {
            $this->simpleRedirectToMain(_kt('Login failed.  Please check your username and password, and try again.'), $url, $queryParams);
            exit(0);
        }

        $res = $this->performLogin($oUser);

        if ($res) {
            $this->simpleRedirectToMain($res->getMessage(), $url, $queryParams);
            exit(0);
        }
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

    function do_autoSignup() {
        $oSource =& $this->oValidator->validateAuthenticationSource($_REQUEST['source_id']);
        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForSource($oSource);
        $oDispatcher = $oProvider->getSignupDispatcher($oSource);
        $oDispatcher->subDispatch($this);
        exit(0);
    }

    function do_checkCookie() {
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
        } else {
            $url = KTUtil::kt_url();

            $config = KTConfig::getSingleton();
            $redirectToBrowse = $config->get('KnowledgeTree/redirectToBrowse', false);
            $redirectToDashboardList = $config->get('KnowledgeTree/redirectToBrowseExceptions', '');

            if ($redirectToBrowse)
            {
                $exceptionsList = explode(',', str_replace(' ','',$redirectToDashboardList));
                $user = User::get($_SESSION['userID']);
                $username = $user->getUserName();
                $url .= (in_array($username, $exceptionsList))?'/dashboard.php':'/browse.php';
            }
            else
            {
                $url .=  '/dashboard.php';
            }
        }
        exit(redirect($url));
    }
}

/**
 * Check if the last user logging in from the same IP as the current user timed out in the last hour.
 *
 * @param unknown_type $userId
 * @return unknown
 */
function checkLastSessionUserID($userId)
{
    // Get the current users IP Address
    $sIp = '%'.$_SERVER['REMOTE_ADDR'];

    // Get the time for a day ago and an hour ago
    $dif = time() - (24*60*60);
    $sDayAgo = date('Y-m-d H:i:s', $dif);
    $dif2 = time() - (60*60);
    $sHourAgo = date('Y-m-d H:i:s', $dif2);

    // Get the session id for the last user to log in from the current IP address within the last day
    // Use the session id to find if that user logged out or timed out within the last hour.
	$sQuery = 'SELECT user_id, action_namespace FROM user_history
        WHERE datetime > ? AND
        session_id = (SELECT session_id FROM user_history WHERE comments LIKE ? AND datetime > ? ORDER BY id DESC LIMIT 1)
        ORDER BY id DESC LIMIT 1';

	$aParams = array($sHourAgo, $sIp, $sDayAgo);
	$res = DBUtil::getOneResult(array($sQuery, $aParams));

	if(PEAR::isError($res) || empty($res)){
	    return false;
	}

	// Check whether the user timed out and whether it was the current user or a different one
	if($res['action_namespace'] == 'ktcore.user_history.timeout' && $res['user_id'] != $userId){
	    return true;
	}

	return false;
}

$dispatcher =& new LoginPageDispatcher();
$dispatcher->dispatch();

?>

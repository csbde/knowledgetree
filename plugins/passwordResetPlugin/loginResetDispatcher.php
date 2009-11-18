<?php
/**
 * $Id: $
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
require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');


class loginResetDispatcher extends KTDispatcher {

    function do_main() {
        global $default;
        $oPage = $GLOBALS['main'];

        // Check if the user is trying to reset their password.
        $reset_password = $this->checkReset();

        KTUtil::save_base_kt_url();

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

        if(!$this->check() && $_SESSION['userID'] != -2) { // bounce here, potentially.
            // User is already logged in - get the redirect
            $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

            $cookietest = KTUtil::randomString();
            setcookie("CookieTestCookie", $cookietest, 0);

            $this->redirectTo('checkCookie', array(
                'cookieVerify' => $cookietest,
                'redirect' => $redirect,
            ));
            exit(0);
        }

        header('Content-type: text/html; charset=UTF-8');

        $errorMessage = KTUtil::arrayGet($_REQUEST, 'errorMessage');
        session_start();

        $errorMessageConfirm = $_SESSION['errormessage']['login'];

        $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

        // Get the list of languages
        $oReg =& KTi18nregistry::getSingleton();
        $aRegisteredLangs = $oReg->geti18nLanguages('knowledgeTree');
        $aLanguageNames = $oReg->getLanguages('knowledgeTree');
        $aRegisteredLanguageNames = array();

        if(!empty($aRegisteredLangs))
        {
            foreach (array_keys($aRegisteredLangs) as $sLang) {
                $aRegisteredLanguageNames[$sLang] = $aLanguageNames[$sLang];
            }

            asort($aRegisteredLanguageNames);
        }
        $sLanguageSelect = $default->defaultLanguage;

        // extra disclaimer, if plugin is enabled
        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');
        if (!PEAR::isError($oPlugin) && !is_null($oPlugin)) {
            $sDisclaimer = $oPlugin->getLoginDisclaimer();
        }

        $js = array();
        $css = array();
        $js[] = '/thirdpartyjs/extjs/adapter/ext/ext-base.js';
        $js[] = '/thirdpartyjs/extjs/ext-all.js';
        $css[] = '/thirdpartyjs/extjs/resources/css/ext-all.css';

        // Include additional js and css files
	    $oPlugin =& $oRegistry->getPlugin('password.reset.plugin');
        $js[] = $oPlugin->getURLPath('resources/passwordReset.js');
        $css[] = $oPlugin->getURLPath('resources/passwordReset.css');

        $sUrl = KTUtil::addQueryStringSelf('action=');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('login_reset');
        $aTemplateData = array(
              'errorMessage' => $errorMessage,
              'errorMessageConfirm' => $errorMessageConfirm,
              'redirect' => $redirect,
              'systemVersion' => $default->systemVersion,
              'versionName' => $default->versionName,
              'languages' => $aRegisteredLanguageNames,
              'selected_language' => $sLanguageSelect,
	      	  'disclaimer' => $sDisclaimer,
	      	  'js' => $js,
	      	  'css' => $css,
	      	  'sUrl' => $sUrl,
			  'smallVersion' => substr($default->versionName,-17),
			  'reset_password' => $reset_password
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

        if(!$this->check() && $_SESSION['userID'] != -2) { // bounce here, potentially.
            // User is already logged in - get the redirect
            $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));

            $cookietest = KTUtil::randomString();
            setcookie("CookieTestCookie", $cookietest, 0);

            $this->redirectTo('checkCookie', array(
                'cookieVerify' => $cookietest,
                'redirect' => $redirect,
            ));
            exit(0);
        }

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

    function checkReset() {
        $resetKey = (isset($_REQUEST['pword_reset'])) ? $_REQUEST['pword_reset'] : '';
        if(!empty($resetKey)){
            // Get the user id from the key
            $aKey = explode('_', $resetKey);
            $id = isset($aKey[1]) ? $aKey[1] : '';

            // Match the key to the one stored in the database and check the expiry date
            $storedKey = KTUtil::getSystemSetting('password_reset_key-'.$id);
            $expiry = KTUtil::getSystemSetting('password_reset_expire-'.$id);

            if($expiry < time()){
                $_REQUEST['errorMessage'] = _kt('The password reset key has expired, please send a new request.');
            }else if($storedKey != $resetKey){
                $_REQUEST['errorMessage'] = _kt('Unauthorised access denied.');
            }else{
                return true;
            }
        }
        return false;
    }

	function do_sendResetRequest(){
	    $email = $_REQUEST['email'];
	    $user = $_REQUEST['username'];

	    // Check that the user and email match up in the database
	    $sQuery = 'SELECT id FROM users WHERE username = ? AND email = ?';
        $aParams = array($user, $email);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

        if(!is_numeric($id) || $id < 1) {
            return _kt('Please check that you have entered a valid username and email address.');
        }

        // Generate a random key that expires after 24 hours
        $expiryDate = time()+86400;
        $randomKey = rand(20000, 100000)."_{$id}_".KTUtil::getSystemIdentifier();
        KTUtil::setSystemSetting('password_reset_expire-'.$id, $expiryDate);
        KTUtil::setSystemSetting('password_reset_key-'.$id, $randomKey);

        // Create the link to reset the password
        $query = 'pword_reset='.$randomKey;
        $url = KTUtil::addQueryStringSelf($query);
//        $url = KTUtil::kt_url() . '/login.php?' . $query;

        $subject = APP_NAME . ': ' . _kt('password reset request');

        $body = '<dd><p>';
        $body .= _kt('You have requested to reset the password for your account. To confirm that the request was submitted by you
        click on the link below, you will then be able to reset your password.');
        $body .= "</p><p><a href = '$url'>". _kt('Confirm password reset').'</a></p></dd>';

        $oEmail = new Email();
        $res = $oEmail->send($email, $subject, $body);

        if($res === true){
            return _kt('A verification email has been sent to your email address.');
        }

        return _kt('An error occurred while sending the email, please try again or contact the System Administrator.');
    }

    function do_resetPassword(){
	    $email = $_REQUEST['email'];
	    $user = $_REQUEST['username'];
	    $password = $_REQUEST['password'];
	    $confirm = $_REQUEST['confirm'];

	    if(!($password == $confirm)){
	        return _kt('The passwords do not match, please re-enter them.');
	    }
	    $password = md5($password);

        // Get user from db
        $sQuery = 'SELECT id FROM users WHERE username = ? AND email = ?';
        $aParams = array($user, $email);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

        if(!is_numeric($id) || $id < 1) { //PEAR::isError($res) || is_null($res)){
            return _kt('Please check that you have entered a valid username and email address.');
        }

        // Check expiry
        $expiry = KTUtil::getSystemSetting('password_reset_expire-'.$id);
        if($expiry < time()){
            return _kt('The password reset key has expired, please send a new request.');
        }

        // Update password
        $res = DBUtil::autoUpdate('users', array('password' => $password), $id);

        if(PEAR::isError($res) || is_null($res)){
            return _kt('Your password could not be reset, please try again.');
        }

        // Unset expiry date and key
        KTUtil::setSystemSetting('password_reset_expire-'.$id, '');
        KTUtil::setSystemSetting('password_reset_key-'.$id, '');

        // Email confirmation
        $url = KTUtil::addQueryStringSelf('');

        $subject = APP_NAME . ': ' . _kt('password successfully reset');

        $body = '<dd><p>';
        $body .= _kt('Your password has been successfully reset, click the link below to login.');
        $body .= "</p><p><a href = '$url'>". _kt('Login').'</a></p></dd>';

        $oEmail = new Email();
        $res = $oEmail->send($email, $subject, $body);

        if($res === true){
            return _kt('Your password has been successfully reset.');
        }

        return _kt('An error occurred while sending the email, please try again or contact the System Administrator.');
    }
}

$dispatcher = new loginResetDispatcher();
$dispatcher->dispatch();

?>

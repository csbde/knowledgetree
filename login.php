<?php

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

/**
 * $Id$
 *  
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

class LoginPageDispatcher extends KTDispatcher {

    function check() {
        // bounce out immediately.
		$session = new Session();
		if ($session->verify() == 1) { // erk.  neil - DOUBLE CHECK THIS PLEASE.
			exit(redirect(generateControllerLink('dashboard')));
		} else {
		    $session->destroy(); // toast it - its probably a hostile session.
		}
		return true;
	}

	function do_main() {
	    global $default;
	
	    $this->check(); // bounce here, potentially.
	    header('Content-type: text/html; charset=UTF-8');
		$cookietest = KTUtil::randomString();
		setcookie("CookieTestCookie", $cookietest, false);
		
		$errorMessage = KTUtil::arrayGet($_REQUEST, 'errorMessage');
		$redirect = KTUtil::arrayGet($_REQUEST, 'redirect');

        $oReg =& KTi18nregistry::getSingleton();
        $aRegisteredLangs = $oReg->geti18nLanguages('knowledgeTree');
        $aLanguageNames = $oReg->getLanguages('knowledgeTree');
        $aRegisteredLanguageNames = array();
        foreach (array_keys($aRegisteredLangs) as $sLang) {
            $aRegisteredLanguageNames[$sLang] = $aLanguageNames[$sLang];
        }
        $sLanguageSelect = $default->defaultLanguage;
		
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/login");
		$aTemplateData = array(
              "context" => $this,
			  'cookietest' => $cookietest,
			  'errorMessage' => $errorMessage,
			  'redirect' => $redirect,
			  'systemVersion' => $default->systemVersion,
              'languages' => $aRegisteredLanguageNames,
              'selected_language' => $sLanguageSelect,
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
	    $this->check();
		global $default;
		
		$redirect = KTUtil::arrayGet($_REQUEST, 'redirect');
		
		$url = $_SERVER["PHP_SELF"];
		$queryParams = array();
		
		if ($redirect !== null) {
		    $queryParams[] = 'redirect='. urlencode($redirect);
		}
		
		
	    $cookieTest = KTUtil::arrayGet($_COOKIE, "CookieTestCookie", null);
		$cookieVerify = KTUtil::arrayGet($_REQUEST, 'cookieverify', null);
		
		if (($cookieVerify === null) || ($cookieTest !== $cookieVerify)) {
		    $this->simpleRedirectToMain(_('You must have cookies enabled to use the document management system.'), $url, $params);
		    exit(0);
		}
		
		$username = KTUtil::arrayGet($_REQUEST,'username');
		$password = KTUtil::arrayGet($_REQUEST,'password');
		
		if (empty($username)) {
		    $this->simpleRedirectToMain(_('Please enter your username.'), $url, $params);
		}
		
		if (empty($password)) {
		    $this->simpleRedirectToMain(_('Please enter your password.'), $url, $params);
		}

        $oUser =& User::getByUsername($username);
        if (PEAR::isError($oUser) || ($oUser === false)) {
            $this->simpleRedirectToMain(_('Login failed.  Please check your username and password, and try again.'), $url, $params);
            exit(0);
        }
        $authenticated = KTAuthenticationUtil::checkPassword($oUser, $password);

        if (PEAR::isError($authenticated)) {
            $this->simpleRedirectToMain(_('Authentication failure.  Please try again.'), $url, $params);
            exit(0);
        }

        if ($authenticated !== true) {
            $this->simpleRedirectToMain(_('Login failed.  Please check your username and password, and try again.'), $url, $params);
            exit(0);
        }

        $session = new Session();
        $sessionID = $session->create($oUser->getId());

        // DEPRECATED initialise page-level authorisation array
        $_SESSION["pageAccess"] = NULL; 
        $language = KTUtil::arrayGet($_REQUEST, 'language');
        if (empty($language)) {
            $language = $default->defaultLanguage;
        }
		setcookie("kt_language", $language, 2147483647, '/');

        // check for a location to forward to
        if ($redirect !== null) {
            $url = $redirect;
        // else redirect to the dashboard if there is none
        } else {
            $url = generateControllerUrl("dashboard");
        }
        exit(redirect($url));
	}
}


$dispatcher =& new LoginPageDispatcher();
$dispatcher->dispatch();

?>

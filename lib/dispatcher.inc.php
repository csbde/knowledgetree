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

require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_DIR . "/thirdparty/pear/JSON.php");

require_once(KT_DIR . '/thirdparty/pear/Net/URL.php');


class KTDispatchStandardRedirector {
    function redirect($url) {
        redirect($url);
    }
}

class KTDispatcher {
    var $event_var = "action";
    var $action_prefix = "do";
    var $cancel_var = "kt_cancel";
    var $bAutomaticTransaction = false;
    var $bTransactionStarted = false;
    var $oValidator = null;
    var $sParentUrl = null; // it is handy for subdispatched items to have an "exit" url, for cancels, etc.

    var $aPersistParams = array();

    function KTDispatcher() {
        $this->oValidator =new KTDispatcherValidation($this);
        $this->oRedirector =new KTDispatchStandardRedirector($this);
    }

    function redispatch($event_var, $action_prefix = null, $orig_dispatcher = null, $parent_url = null) {
        $previous_event = KTUtil::arrayGet($_REQUEST, $this->event_var);
        $this->sParentUrl = $parent_url;

        if ($action_prefix) {
            $this->action_prefix = $action_prefix;
        }

        if (!is_null($orig_dispatcher)) {
            $this->persistParams($orig_dispatcher->aPersistParams);
            $this->persistParams(array($orig_dispatcher->event_var));
            $core = array('aBreadcrumbs',
                'bTransactionStarted',
                'oUser',
                'session',
                'action_prefix',
                'bJSONMode');
            foreach($core as $k) {
                if(isset($orig_dispatcher->$k)) {
                    $this->$k = $orig_dispatcher->$k;
                }
            }
        }
        $this->event_var = $event_var;

        return $this->dispatch();
    }

    function dispatch () {
        if (array_key_exists($this->cancel_var, $_REQUEST)) {
            $var = $_REQUEST[$this->cancel_var];
            if (is_array($var)) {
                $keys = array_keys($var);
                if (empty($keys[0])) {
                    redirect($_SERVER['PHP_SELF']);
                    exit(0);
                }
                redirect($keys[0]);
                exit(0);
            }
            if (!empty($var)) {
                redirect($_SERVER['PHP_SELF']);
                exit(0);
            }
        }
        $method = sprintf('%s_main', $this->action_prefix);
        if (array_key_exists($this->event_var, $_REQUEST)) {
            $event = strip_tags($_REQUEST[$this->event_var]);
            $proposed_method = sprintf('%s_%s', $this->action_prefix, $event);

            if (method_exists($this, $proposed_method)) {
                $method = $proposed_method;
            }
        }

        if ($this->bAutomaticTransaction) {
            $this->startTransaction();
        }

        if (method_exists($this, 'predispatch')) {
            $this->predispatch();
        }

        $ret = $this->$method();
        $this->handleOutput($ret);

        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }
    }

    function subDispatch(&$oOrigDispatcher) {
        $core = array('aBreadcrumbs',
            'bTransactionStarted',
            'oUser',
            'session',
            'event_var',
            'action_prefix',
            'bJSONMode');
        foreach($core as $k) {
            if(isset($oOrigDispatcher->$k)) {
                $this->$k = $oOrigDispatcher->$k;
            }
        }

        return $this->dispatch();
    }

    function startTransaction() {
        DBUtil::startTransaction();
        $this->bTransactionStarted = true;
    }

    function commitTransaction() {
        DBUtil::commit();
        $this->bTransactionStarted = false;
    }

    function rollbackTransaction() {
        DBUtil::rollback();
        $this->bTransactionStarted = false;
    }

    function errorRedirectTo($event, $error_message, $sQuery = "", $oException = null) {
        if ($this->bTransactionStarted) {
            $this->rollbackTransaction();
        }

        $_SESSION['KTErrorMessage'][] = $error_message;
        /* if ($oException) {
            $_SESSION['Exception'][$error_message] = $oException;
        }*/
        $this->redirectTo($event, $sQuery);
    }

    function successRedirectTo($event, $info_message, $sQuery = "") {
        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }
        if (!empty($info_message)) {
            $_SESSION['KTInfoMessage'][] = $info_message;
        }
        $this->redirectTo($event, $sQuery);
    }

    function errorRedirectToParent($error_message) {
        if ($this->bTransactionStarted) {
            $this->rollbackTransaction();
        }

        $_SESSION['KTErrorMessage'][] = $error_message;
        redirect($this->sParentUrl);
        exit(0);
    }

    function successRedirectToParent($info_message) {
        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }
        if (!empty($info_message)) {
            $_SESSION['KTInfoMessage'][] = $info_message;
        }
        redirect($this->sParentUrl);
        exit(0);
    }

    function redirectTo($event, $sQuery = "") {
        // meld persistant options
        $sQuery = $this->meldPersistQuery($sQuery, $event);

        $sRedirect = KTUtil::addQueryString($_SERVER['PHP_SELF'], $sQuery);
        $this->oRedirector->redirect($sRedirect);
        exit(0);
    }

    function errorRedirectToMain($error_message, $sQuery = "") {
        return $this->errorRedirectTo('main', $error_message, $sQuery);
    }

    function successRedirectToMain($error_message, $sQuery = "") {
        return $this->successRedirectTo('main', $error_message, $sQuery);
    }

    function redirectToMain($sQuery = "") {
        return $this->redirectTo('main', $sQuery);
    }

    function errorRedirectToBrowse($sErrorMessage, $sQuery = "", $event = 'main') {
        if ($this->bTransactionStarted) {
            $this->rollbackTransaction();
        }

        $_SESSION['KTErrorMessage'][] = $sErrorMessage;

        // meld persistant options
        $sQuery = $this->meldPersistQuery($sQuery, $event);

        $server = str_replace('action.php', 'browse.php', $_SERVER['PHP_SELF']);
        $sRedirect = KTUtil::addQueryString($server, $sQuery);
        $this->oRedirector->redirect($sRedirect);
        exit(0);
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }

    /* persist the following parameters between requests (via redirect), unless a value is passed in. */
    function persistParams($aParamKeys) {
        $this->aPersistParams = kt_array_merge($this->aPersistParams, $aParamKeys);
    }

    function meldPersistQuery($sQuery = "", $event = "", $asArray = false) {

        if (is_array($sQuery)) {
            $aQuery = $sQuery;
        } else {
            if (!empty($sQuery)) {
                // need an intermediate step here.
                $aQuery = Net_URL::_parseRawQuerystring($sQuery);
            } else {
                $aQuery = array();
            }
        }
        // now try to grab each persisted entry
        // don't overwrite the existing values, if added.

        if (is_array($this->aPersistParams)) {
            foreach ($this->aPersistParams as $k) {
                if (!array_key_exists($k, $aQuery)) {
                    $v = KTUtil::arrayGet($_REQUEST, $k);
                    if (!empty($v)) {
                        $aQuery[$k] = $v;
                    }
                }
                // handle the case where action is passed in already.
            }
        }
        // if it isn't already set
        if ((!array_key_exists($this->event_var, $aQuery)) && (!empty($event))) {
            $aQuery[$this->event_var] = urlencode($event);
        }
        //var_dump($aQuery);

        if ($asArray) {
            return $aQuery;
        }

        // encode and blend.
        $aQueryStrings = array();
        foreach ($aQuery as $k => $v) {
            $aQueryStrings[] = urlencode($k) . "=" . urlencode($v);
        }
        $sQuery = join('&', $aQueryStrings);
        return $sQuery;
    }
}

class KTStandardDispatcher extends KTDispatcher {
    var $bLogonRequired = true;
    var $bAdminRequired = false;
    var $aBreadcrumbs = array();
    var $sSection = false;
    var $oPage = false;
    var $sHelpPage = null;
    var $bJSONMode = false;

    function KTStandardDispatcher() {
        if (empty($GLOBALS['main'])) {
            $GLOBALS['main'] =new KTPage;
        }
        $this->oPage =& $GLOBALS['main'];
        parent::KTDispatcher();
    }

    function permissionDenied () {
        // handle anonymous specially.
        if ($this->oUser->getId() == -2) {
            redirect(KTUtil::ktLink('login.php','',sprintf("redirect=%s&errorMessage=%s", urlencode($_SERVER['REQUEST_URI']), urlencode(_kt("You must be logged in to perform this action"))))); exit(0);
        }

	global $default;

	$msg = '<h2>' . _kt('Permission Denied') . '</h2>';
	$msg .= '<p>' . _kt('If you feel that this is incorrect, please report both the action and your username to a system administrator.') . '</p>';

        $this->oPage->setPageContents($msg);
        $this->oPage->setUser($this->oUser);
	$this->oPage->hideSection();

        $this->oPage->render();
        exit(0);
    }

    function loginRequired() {
	   $oKTConfig =& KTConfig::getSingleton();
	   if ($oKTConfig->get('allowAnonymousLogin', false)) {
	    // anonymous logins are now allowed.
	    // the anonymous user is -1.
	    //
	    // we short-circuit the login mechanisms, setup the session, and go.

	    $oUser =& User::get(-2);
	    if (PEAR::isError($oUser) || ($oUser->getName() != 'Anonymous')) {
		  ; // do nothing - the database integrity would break if we log the user in now.
	    } else {
		  $session = new Session();
                $sessionID = $session->create($oUser);
                $this->sessionStatus = $this->session->verify();
                if ($this->sessionStatus === true) {
                    return ;
                }
            }
        }

        $sErrorMessage = "";
        if (PEAR::isError($this->sessionStatus)) {
            $sErrorMessage = $this->sessionStatus->getMessage();
        }

	// check if we're in JSON mode - in which case, throw error
	// but JSON mode only gets set later, so gonna have to check action
	if(KTUtil::arrayGet($_REQUEST, 'action', '') == 'json') { //$this->bJSONMode) {
	    $this->handleOutputJSON(array('error'=>true,
					  'type'=>'kt.not_logged_in',
					  'alert'=>true,
					  'message'=>_kt('Your session has expired, please log in again.')));
	    exit(0);
	}

        // redirect to login with error message
        if ($sErrorMessage) {
            // session timed out
            $url = generateControllerUrl("login", "errorMessage=" . urlencode($sErrorMessage));
        } else {
            $url = generateControllerUrl("login");
        }

        $redirect = urlencode(KTUtil::addQueryStringSelf($_SERVER["QUERY_STRING"]));
        if ((strlen($redirect) > 1)) {
            global $default;
            $default->log->debug("checkSession:: redirect url=$redirect");
            // this session verification failure represents either the first visit to
            // the site OR a session timeout etc. (in which case we still want to bounce
            // the user to the login page, and then back to whatever page they're on now)
            $url = $url . urlencode("&redirect=" . urlencode($redirect));
        }
        $default->log->debug("checkSession:: about to redirect to $url");
        redirect($url);
        exit(0);
    }

    function dispatch () {
        if (empty($this->session)) {
            $this->session = new Session();
            $this->sessionStatus = $this->session->verify();
            if ($this->sessionStatus !== true) {
                $this->loginRequired();
            }
            //var_dump($this->sessionStatus);
            $this->oUser =& User::get($_SESSION['userID']);
            $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForUser($this->oUser);
            $oProvider->verify($this->oUser);
        }

        if ($this->bAdminRequired !== false) {
            if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
                $this->permissionDenied();
                exit(0);
            }
        }

        if ($this->check() !== true) {
            $this->permissionDenied();
            exit(0);
        }

        return parent::dispatch();
    }

    function check() {
        return true;
    }

    function addInfoMessage($sMessage) { $_SESSION['KTInfoMessage'][] = $sMessage; }

    function addErrorMessage($sMessage) { $_SESSION['KTErrorMessage'][] = $sMessage; }

    function errorPage($errorMessage, $oException = null) {
        if ($this->bTransactionStarted) {
            $this->rollbackTransaction();
        }
        $sOutput = $errorMessage;
        if ($oException) {
            // $sOutput .= $oException->toString();
        }
        $this->handleOutput($sOutput);
        exit(0);
    }

    function handleOutput($data) {
	if($this->bJSONMode) {
	    return $this->handleOutputJSON($data);
	} else {
	    return $this->handleOutputDefault($data);
	}
    }

    function handleOutputDefault($data) {
	global $default;
	global $sectionName;

        $this->oPage->setSection($this->sSection);
        $this->oPage->setBreadcrumbs($this->aBreadcrumbs);
        $this->oPage->setPageContents($data);
        $this->oPage->setUser($this->oUser);
	$this->oPage->setHelp($this->sHelpPage);

	// handle errors that were set using KTErrorMessage.
	$errors = KTUtil::arrayGet($_SESSION, 'KTErrorMessage', array());
	if (!empty($errors)) {
            foreach ($errors as $sError) {
		$this->oPage->addError($sError);
	    }
	    $_SESSION['KTErrorMessage'] = array(); // clean it out.
	}

	// handle notices that were set using KTInfoMessage.
	$info = KTUtil::arrayGet($_SESSION, 'KTInfoMessage', array());

	if (!empty($info)) {
            foreach ($info as $sInfo) {
		$this->oPage->addInfo($sInfo);
	    }
	    $_SESSION['KTInfoMessage'] = array(); // clean it out.
	}

        // Get the portlets to display from the portlet registry
        $oPRegistry =& KTPortletRegistry::getSingleton();
        $aPortlets = $oPRegistry->getPortletsForPage($this->aBreadcrumbs);
        foreach ($aPortlets as $oPortlet) {
            $oPortlet->setDispatcher($this);
            $this->oPage->addPortlet($oPortlet);
        }

        $this->oPage->render();
    }


    // JSON handling
    function handleOutputJSON($data) {
	$oJSON = new Services_JSON();
	print $oJSON->encode($data);
	exit(0);
    }

    function do_json() {
	$this->bJSONMode = true;
	$this->redispatch('json_action', 'json');
    }

    function json_main() {
	return array('type'=>'error', 'value'=>'Not implemented');
    }



}

class KTAdminDispatcher extends KTStandardDispatcher {
    var $bAdminRequired = true;
    var $sSection = 'administration';

    function KTAdminDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'administration', 'name' => _kt('Administration')),
        );
        return parent::KTStandardDispatcher();
    }
}

class KTErrorDispatcher extends KTStandardDispatcher {
    var $bLogonRequired = true;

    function KTErrorDispatcher($oError) {
        parent::KTStandardDispatcher();
        $this->oError =& $oError;
    }

    function dispatch() {

        require_once(KT_LIB_DIR . '/validation/customerror.php');

        $bCustomCheck = KTCustomErrorCheck::customErrorInit($this->oError);

        if($bCustomCheck)
        {
        	exit(0);
        }


        //if either customer error messages is off or the custom error page doesn't exist the function will run
        //the default error handling here
        $oRegistry =& KTErrorViewerRegistry::getSingleton();
        $oViewer =& $oRegistry->getViewer($this->oError);
        $this->oPage->setTitle($oViewer->view());
        $this->oPage->hideSection();
        $this->handleOutput($oViewer->page());
    }
}


?>

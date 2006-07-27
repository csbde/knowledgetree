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

require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_DIR . "/thirdparty/pear/JSON.php");


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

    function KTDispatcher() {
        $this->oValidator =& new KTDispatcherValidation($this);
        $this->oRedirector =& new KTDispatchStandardRedirector($this);
    }

    function redispatch($event_var, $action_prefix = null) {
        $this->event_var = $event_var;
        if ($action_prefix) {
            $this->action_prefix = $action_prefix;
        }

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
            $event = $_REQUEST[$this->event_var];
            $proposed_method = sprintf('%s_%s', $this->action_prefix, $event);

            if (method_exists($this, $proposed_method)) {
                $method = $proposed_method;
            }
        }

        if ($this->bAutomaticTransaction) {
            $this->startTransaction();
        }

        $ret = $this->$method();
        $this->handleOutput($ret);
        
        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }
    }

    function subDispatch(&$oOrigDispatcher) {
	foreach(array('aBreadcrumbs', 
		      'bTransactionStarted',
		      'oUser',
		      'session',
		      'event_var',
		      'action_prefix',
		      'bJSONMode') as $k) {
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

    function redirectTo($event, $sQuery = "") {
        if (is_array($sQuery)) {
            $sQuery['action'] = $event;
            $aQueryStrings = array();
            foreach ($sQuery as $k => $v) {
                $aQueryStrings[] = urlencode($k) . "=" . urlencode($v);
            }
            $sQuery = join('&', $aQueryStrings);
        } else {
            if (!empty($sQuery)) {
                $sQuery = 'action=' . $event . '&' . $sQuery;
            } else {
                $sQuery = 'action=' . $event;
            }
        }
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

    function handleOutput($sOutput) {
        print $sOutput;
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
            $GLOBALS['main'] =& new KTPage;
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
        require_once(KT_LIB_DIR . '/validation/errorviewer.inc.php');
        $oRegistry =& KTErrorViewerRegistry::getSingleton();
        $oViewer =& $oRegistry->getViewer($this->oError);
        $this->oPage->setTitle($oViewer->view());
        $this->oPage->hideSection();
        $this->handleOutput($oViewer->page());
    }
}


?>

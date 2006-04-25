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

require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

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
        return KTDispatcher::dispatch();
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
        if (isset($oOrigDispatcher->aBreadcrumbs)) {
            $this->aBreadcrumbs = $oOrigDispatcher->aBreadcrumbs;
        }
        if (isset($oOrigDispatcher->bTransactionStarted)) {
            $this->bTransactionStarted = $oOrigDispatcher->bTransactionStarted;
        }
        if (isset($oOrigDispatcher->oUser)) {
            $this->oUser = $oOrigDispatcher->oUser;
        }
        if (isset($oOrigDispatcher->session)) {
            $this->session = $oOrigDispatcher->session;
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
    
    function KTStandardDispatcher() {
        if (empty($GLOBALS['main'])) {
            $GLOBALS['main'] =& new KTPage;
        }
        $this->oPage =& $GLOBALS['main'];
	parent::KTDispatcher();
    }

    function permissionDenied () {
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
				
				return ;
			}
		}	
	
	
        $sErrorMessage = "";
        if (PEAR::isError($this->sessionStatus)) {
            $sErrorMessage = $this->sessionStatus->getMessage();
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
	
    function handleOutput($data) {
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

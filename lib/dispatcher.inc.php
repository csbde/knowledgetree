<?php

require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

class KTDispatcher {
    var $event_var = "action";
    var $bAutomaticTransaction = false;
    var $bTransactionStarted = false;
	var $oValidator = null;

    function KTDispatcher() {
        $this->oValidator =& new KTDispatcherValidation($this);
    }

    function dispatch () {
        require_once(KT_DIR . '/presentation/webpageTemplate.inc');
        $method = 'do_main';
        if (array_key_exists($this->event_var, $_REQUEST)) {
            $event = $_REQUEST[$this->event_var];
            if (method_exists($this, 'do_' . $event)) {
                $method = 'do_' . $event;
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
            $sQuery = "?" . join('&', $aQueryStrings);
        } else {
            if (!empty($sQuery)) {
                $sQuery = '?action=' . $event . '&' . $sQuery;
            } else {
                $sQuery = '?action=' . $event;
            }
        }
        exit(redirect($_SERVER["PHP_SELF"] . $sQuery));
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
    
    function KTStandardDispatcher() {
        global $main;
        $this->oPage =& $main;
		parent::KTDispatcher();
    }

    function permissionDenied () {
        print "Permission denied";
        exit(0);
    }

    function loginRequired() {
        $url = generateControllerUrl("loginForm");
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        if ((strlen($redirect) > 1)) {
            $url = $url . "&redirect=" . $redirect;
        }
        redirect($url);
        exit(0);
    }

    function dispatch () {
        $session = new Session();
        $sessionStatus = $session->verify($bDownload);
        if ($sessionStatus === false) {
            $this->loginRequired();
        }

        if ($this->bLogonRequired !== false) {
            if (empty($_SESSION['userID'])) {
                $this->loginRequired();
                exit(0);
            }
            $this->oUser =& User::get($_SESSION['userID']);
            if (PEAR::isError($this->oUser) || ($this->oUser === false)) {
                $this->loginRequired();
                exit(0);
            }
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

    function handleOutput($data) {
	    global $default;
		global $sectionName;
        $this->oPage->setSection($this->sSection);
        $this->oPage->setBreadcrumbs($this->aBreadcrumbs);
        $this->oPage->setPageContents($data);
        $this->oPage->setUser($this->oUser);
		
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

    function KTAdminDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'administration', 'name' => _('Administration')),
        );
    }
}


?>

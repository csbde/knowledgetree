<?php
/**
 * $Id$
 *
 * Contains the controller helper functions.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.session
 */

/**
 * Redirects to the specified URL
 *
 * @param string the URL to forward to
 */
function redirect($url) {
    header("Location: $url");
}

/**
 * Performs a redirect through the controller.
 * Takes a controller action and queryString and builds url.
 *
 * @param string the controller action
 * @param string additional querystring vars
 */
function controllerRedirect($sAction, $sQueryString = "") {
    global $default;
    // generate url
    $url = generateControllerUrl($sAction, $sQueryString);
    // now redirect
    $default->log->debug("controllerRedirect: redirect to $url");
    redirect($url);
    exit(0);
}

/**
 * Generates a link
 *
 * @param string the url to link to
 * @param string the querystring
 * @param string the link text (optional)
 * @return string the html link if the link text is specified, otherwise just the url
 */
function generateLink($sTargetPage, $sQueryString = "", $sLinkText = "", $bLinkUrl = false) {
    global $default;

    $sLink = "http" . ($default->sslEnabled ? "s" : "") . "://" . $default->serverName . 
             ((substr($sTargetPage, 0, strlen($default->rootUrl)) != $default->rootUrl) ? $default->rootUrl : "") .
             (substr($sTargetPage, 0, 1) == "/" ? "" : "/") . 
             $sTargetPage;
    
    $sLink = KTUtil::addQueryString($sLink, $sQueryString);
	if($bLinkUrl == true){
		$sLinkText = $sLink;
	}
    return (strlen($sLinkText) > 0) ? "<a href=\"$sLink\">$sLinkText</a>" : $sLink;
}

/**
 * Returns a controller url.
 *
 * @param string the controller action to generate a url for
 * @param string additional querystring parameters (optional)
 * @return string the controller url, empty string if no action is supplied
 */
function generateControllerUrl($sAction, $sQueryString = "", $bReturnLink = true) {
    global $default;
    $qs = "";
    if (!empty($sQueryString)) {
        $qs = 'qs=' . urlencode($sQueryString);
    }
    if (strlen($sAction) > 0) {
        if ($bReturnLink) {
            return generateLink("/control.php?action=$sAction", $qs);
        } else {
            if (empty($qs)) {
                return $default->rootUrl .  "/control.php?action=$sAction";
            } else {
                return $default->rootUrl .  "/control.php?action=$sAction&$qs";
            }
        }
    } else {
        return "";
    }
}

/**
 * Generates a link via the control page, with the passed action
 *
 * @param string the controller action to generate a link for
 * @param string the text of the link
 * @param string the querystring (optional)
 * @return string the generated href
 */
function generateControllerLink($sAction, $sQueryString = "", $sLinkText = "", $bLinkUrl = false) {
    $qs = "";
    if (!empty($sQueryString)) {
        $qs = 'qs=' . urlencode($sQueryString);
    }
    return generateLink("/control.php?action=$sAction", $qs, $sLinkText, $bLinkUrl);
}

/**
 * Checks the current session and redirects to the login page
 * if the redirect parameter is true.
 *
 * @param boolean whether to automatically redirect to the login page on session verification failure
 * @param boolean optional parameter set if we're downloading a file
 */
function checkSessionAndRedirect($bRedirect, $bDownload = false) {
    global $default;

    $session = new Session();
    $sessionStatus = $session->verify();

    if ($sessionStatus === true) {
        $default->log->debug("checkSession:: returning true");
        return true;
    }

    // verification failed
    $default->log->debug("checkSession:: session check failed");
    if (empty($bRedirect)) {
        return $sessionStatus;
    }

    $sErrorMessage = "";
    if (PEAR::isError($sessionStatus)) {
        $sErrorMessage = $sessionStatus->getMessage();
    }
    
    $oKTConfig = KTConfig::getSingleton();
    $dest = 'login';
    if ($oKTConfig->get('allowAnonymousLogin', false)) { $dest = 'dashboard'; }
    
    // redirect to login with error message
    if ($sErrorMessage) {
        // session timed out
        $url = generateControllerUrl($dest, "errorMessage=" . urlencode($sErrorMessage));
    } else {
        $url = generateControllerUrl($dest);
    }

    $redirect = urlencode(KTUtil::addQueryStringSelf($_SERVER["QUERY_STRING"]));
    if ((strlen($redirect) > 1)) {
        $default->log->debug("checkSession:: redirect url=$redirect");
        // this session verification failure represents either the first visit to
        // the site OR a session timeout etc. (in which case we still want to bounce
        // the user to the login page, and then back to whatever page they're on now)
        $url = $url . "&redirect=" . $redirect;
    }
    $default->log->debug("checkSession:: about to redirect to $url");
    redirect($url);
    exit;
}

/**
 * Verifies the current session
 * Automatically redirects to the login page on session verification failure
 * @param boolean optional parameter set if we're downloading a file 
 */
function checkSession($bDownload = false, $bHandleNoAccess = true) {
    global $default;
    if (checkSessionAndRedirect(true, $bDownload)) {
        $default->log->debug("control.inc: print the session variables: " . arrayToString($_SESSION));
        // the session is cool, now check if we access to this page
        $pageName = KTUtil::getRequestScriptName($_SERVER);
        $default->log->debug("control.inc: page=" . $pageName . " ? " . $_SESSION["pageAccess"][$pageName]);
        if ($_SESSION["pageAccess"][$pageName]) {
            return true;
        } else {
            if ($bHandleNoAccess === true) {
                print "Access restricted\n";
                exit(0);
            }
            return false;
        }
    }
    // if the check session fails, we'll be redirected to the login page
}

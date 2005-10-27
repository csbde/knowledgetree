<?php
/**
 * $Id$
 *
 * Contains the controller helper functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
}

/**
 * Generates a link
 *
 * @param string the url to link to
 * @param string the querystring
 * @param string the link text (optional)
 * @return string the html link if the link text is specified, otherwise just the url
 */
function generateLink($sTargetPage, $sQueryString = "", $sLinkText = "") {
    global $default;

    $sQueryStringDelimiter = strlen($sQueryString) > 0 ? (strstr($sTargetPage, "?") ? "&" : "?") : "";

    $sLink = "http" . ($default->sslEnabled ? "s" : "") . "://" . $default->serverName . 
             ((substr($sTargetPage, 0, strlen($default->rootUrl)) != $default->rootUrl) ? $default->rootUrl : "") .
             (substr($sTargetPage, 0, 1) == "/" ? "" : "/") . 
             $sTargetPage . $sQueryStringDelimiter . $sQueryString;

    return (strlen($sLinkText) > 0) ? "<a href=\"$sLink\">$sLinkText</a>" : $sLink;
}

/**
 * Generates a link used when setting up template documents for document linking in folder
 * collaboration.  Formatted to send document name and id back to parent window 
 *
 * @param string the url to link to
 * @param string the querystring
 * @param string the link text (optional)
 * @return string the html link if the link text is specified, otherwise just the url
 */
function generateLinkForTemplateDocumentBrowse($sTargetPage, $sQueryString, $sLinkText = "", $sDocumentName, $iDocumentID) {
    global $default;

    if (strlen($sQueryString) > 0) {
        $sQueryStringDelimiter = (strstr($sTargetPage, "?") ? "&" : "?");
    }
    $sLink = "http" . ($default->sslEnabled ? "s" : "") . "://" . $default->serverName . 
             ((substr($sTargetPage, 0, strlen($default->rootUrl)) != $default->rootUrl) ? $default->rootUrl : "") . 
             $sTargetPage . $sQueryStringDelimiter . $sQueryString;

    return (strlen($sLinkText) > 0) ? "<a href=\"$sLink\" onClick=\"load('$sDocumentName', $iDocumentID, top.opener);\">$sLinkText</a>" : $sLink;
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
function generateControllerLink($sAction, $sQueryString = "", $sLinkText = "") {
    $qs = "";
    if (!empty($sQueryString)) {
        $qs = 'qs=' . urlencode($sQueryString);
    }
    return generateLink("/control.php?action=$sAction", $qs, $sLinkText);
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
    $sessionStatus = $session->verify($bDownload);

    if ($sessionStatus != 1) {
        // verification failed
        $default->log->debug("checkSession:: session check failed");
        if ($bRedirect) {
            // redirect to login with error message
            if ($sessionStatus == 2) {
            	// session timed out
            	$url = generateControllerUrl("loginForm", "errorMessage=" . urlencode("Session timed out"));
            } else {
            	$url = generateControllerUrl("loginForm");
            }
            $redirect = urlencode($_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]);
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
        } else {
            return false;
        }
    } else {
        $default->log->debug("checkSession:: returning true");
        return true;
    }
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

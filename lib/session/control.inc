<?php

/**
 * $Id$
 *
 * Contains the controller helper functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
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
    // everything is relative to the root url
    $url = $default->owl_root_url . $url;
    header("Location: $url");
}

/**
 * Performs a redirect through the controller.
 * Takes a controller action and queryString and builds url.
 *
 * @param string the controller action
 * @param string additional querystring vars
 */
function controllerRedirect($action, $queryString) {
    // generate url
    $ctlUrl = generateControllerUrl($action);
    // append the rest of the url
    $url = $ctlUrl . "&$queryString";
    // now redirect
    redirect($url);
}

/**
 * Returns a controller url.
 *
 * @param string the controller action to generate a url for
 * @return string the controller url
 */
function generateControllerUrl($action) {
    global $default;
    return $default->owl_root_url . "/control.php?action=$action";
}

/**
 * Generates a link via the control page, with the passed action
 *
 * @param string the controller action to generate a link for
 * @return string the generated href
 */
function generateControllerLink($action) {
    return "<a href=\"" . generateControllerUrl($action) . "\">";
}

/**
 * Checks the current session and redirects to the login page
 * if the redirect parameter is true.
 *
 * @param boolean whether to automatically redirect to the login page on session verification failure
 */ 
function checkSessionAndRedirect($bRedirect) {
    global $default;
    
    $session = new Session();
    $sessionStatus = $session->verify();
    
    if ($sessionStatus != 1) {
        if ($bRedirect) {
            // verification failed, redirect to login with error message
            $default->log->debug("checkSession:: session check failed");
            $url = $default->owl_root_url . "/control.php?action=loginForm";
            
            $redirect = $_SERVER[PHP_SELF];
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
 */
function checkSession() {
    global $default;
    if (checkSessionAndRedirect(true)) {
        $default->log->debug("control.inc: print the session variables: " . arrayToString($_SESSION));
        // the session is cool, now check if we access to this page
        $default->log->debug("control.inc: page=" . $_SERVER['PHP_SELF'] . " ? " . $_SESSION["pageAccess"][$_SERVER['PHP_SELF']]);
        if ($_SESSION["pageAccess"][$_SERVER['PHP_SELF']]) {
            return true;
        } else {
            return false;
        }
    }
    // if the check session fails, we'll be redirected to the login page
}

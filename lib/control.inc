<?php

/**
 * $Id$
 *
 * Contains the controller helper functions
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com">Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
 
/**
 * Redirects to the specified URL
 *
 * @param $url  the URL to forward to
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
 * @param $action the controller action
 * @param $queryString additional querystring vars
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
 * @param $action  the controller action to generate a url for
 *
 * @return the controller url
 */
function generateControllerUrl($action) {
    return "/control.php?action=$action";
}

/**
 * Generates a link via the control page, with the passed action
 *
 * @param $action
 *        the controller action to generate a link for
 * @return the generated href
 */
function generateLink($action) {
    return "<a href=\"" . generateControllerUrl($action) . "\">";
}

/**
 * Verifies the current session
 * Automatically redirects to
 */
function checkSession() {
    global $default;
    
    $session = new Session();
    $sessionStatus = $session->verify();
    
    if ($sessionStatus != 1) {
        // verification failed, redirect to login with error message
        $default->log->debug("checkSession:: session check failed");
        $url = $default->owl_root_url . "/login.php?loginAction=loginForm";
        
        $redirect = $_SERVER[PHP_SELF];
        if ((strlen($redirect) > 1) && ($redirect != "/control.php")) {
            $default->log->debug("checkSession:: redirect url=$redirect");
            // this session verification failure represents either the first visit to 
            // the site OR a session timeout etc. (in which case we still want to bounce
            // the user to the login page, and then back to whatever page they're on now)
            $url = $url . "&redirect=" . $redirect;
        }
        $default->log->debug("checkSession:: about to redirect to $url");
        redirect($url);
    } else {
        $default->log->debug("checkSession:: returning true");
        return true;
    }
}

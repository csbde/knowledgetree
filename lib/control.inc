<?php

/**
 * $Id$
 *
 * Contains the controller helper functions
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
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
    //echo "redirect:url = $url<br>";
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
    return "control.php?action=$action";
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
 */
function checkSession() {
    session_start();
    $session = new Session();
    $sessionStatus = $session->verify();
    if ($sessionStatus["status"] != 1) {
        // verification failed, redirect to login with error message
        $url = "login.php?loginAction=loginForm";
        if (isset($default->errorMessage) && (strlen($default->errorMessage) > 0) ) {
            $url = $url . "&errorMessage=$default->errorMessage";
        }
        $qs = $_SERVER[QUERY_STRING];
        // redirect to login page with original uri unless the original uri is the login page,
        // which means that the login attempt failed
        if (strstr($qs, "action=LOGIN_FORM")) {
            // redirecting to login- ensure error message is set
            // FIXME: is this presumptious?  more rigor?  use $default?
            $url = $url . "&errorMessage=" . urlencode($errorMessage);
        } else if (strlen($_SERVER[QUERY_STRING]) > 1) {
            // not redirecting to login, so this session verification failure
            // represents either the first visit to the site
            // OR a session timeout etc. (in which case we still want to bounce
            // the user to the login page, and then back to whatever page they're on now)
            $originalRequest = $_SERVER[QUERY_STRING];
            $url = $url . "&redirect=" . $originalRequest;
        }
            
        redirect($url);
        
    }   
}

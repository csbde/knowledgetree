<?php

/**
 * control.php -- Controller page
 *  
 * This page controls the web application by responding to a set of
 * defined actions.  The controller performs session handling, page-level
 * authentication and forwards the request to the appropriate handling
 * page.  
 *
  *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Id$
 * @Copyright (c) 1999-2002 The Owl Project Team
 * @author michael
 * @package dms
 */

// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("./lib/owl.lib.php");
require_once("./config/html.php");
require_once("./lib/control.inc");
require_once("./lib/Session.inc");
require_once("./lib/SiteMap.inc");

// -------------------------------
// page start
// -------------------------------

if (!checkSession()) {
    // no session, redirect to login
    $action = "LOGIN_FORM";
    // redirect to login page with redirect
    $originalRequest = urlencode($_SERVER[REQUEST_URI]);// . $_SERVER[QUERY_STRING];
}

// retrieve the login page to redirect to
// FIXME: defaulting to A access
$page = $default->siteMap->getPage($action, "A");
//$page = $default->siteMap->getPage($action, getUserClass($userID));

// getPage returns false for no permisssion
if (!$page) {
    // TODO: build no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.";    
} else {
    //echo "about to redirect to $page<br>";
    redirect($page);
    // FIXME: append original request if necessary
    /*
    if (isset($originalRequest)) {
        redirect($page . "?fRedirect=$originalRequest");
    } else {
        redirect($page);        
    }
    */
}
?>

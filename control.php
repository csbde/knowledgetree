<?php

// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/session/SiteMap.inc");

/**
 * $Id$ 
 *  
 * Controller page -- controls the web application by responding to a set of
 * defined actions.  The controller performs session handling, page-level
 * authentication and forwards the request to the appropriate handling
 * page.  
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package controller
 */

// -------------------------------
// page start
// -------------------------------

// check the session, but don't redirect if the check fails
if (checkSessionAndRedirect(false)) {
    // session check succeeds, so default action should be the dashboard if no action was specified
    if (!isset($action)) {
        $action = "dashboard";
    }
} else {
    // session check fails, so default action should be the login form if no action was specified
    if (!isset($action)) {
        $action = "loginForm";
    }
}

// (if there is no userID on the session and the action that we're looking up
//  from the sitemap requires group access ie. !Anonymous then redirect to no
//  permission page)

// check whether the users group has access to the requested page
$page = $default->siteMap->getPage($action, $_SESSION["userID"]);

$default->log->debug("retrieved page=$page from SiteMap");
if (!$page) {
    // this user doesn't have permission to access the page
    // or there is no page mapping for the requested action
    
    // FIXME: redirect to no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.<br>";
    echo generateLink("logout") . "logout</a>";    

    exit;
} else {
    // set authorised flag and redirect
    $_SESSION["pageAccess"][$page] = true;
    $default->log->debug("control.php: just set SESSION[\"pageAccess\"][$page]=" . $_SESSION["pageAccess"][$page]); 
    redirect($page);
}
?>

<?php

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
 * @author <a href="mailto:michael@jamwarehouse.com">Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */

// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/SiteMap.inc");

// -------------------------------
// page start
// -------------------------------

if (checkSession()) {
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

// reset authorisation flag before checking access
$_SESSION["authorised"] = false;

$default->log->info("control.php: checking ($action, " . $_SESSION["userID"] . ")");
// check whether the users group has access to the requested page
$page = $default->siteMap->getPage($action, $_SESSION["userID"]);

$default->log->debug("retrieved page=$page from SiteMap");
if (!$page) {
    $default->log->info("control.php: permission denied for ($action, " . $_SESSION["userID"] . ")");
    // this group doesn't have permission to access the page
    // or there is no page mapping for the requested action
    
    // FIXME: redirect to no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.<br>";
    echo generateLink("logout") . "logout</a>";    

    exit;
} else {
    // set authorised flag and redirect
    $_SESSION["authorised"] = true;
    $default->log->debug("control.php: ($action, " . $_SESSION["userID"] . ")set authorised flag:" . $_SESSION["authorised"]);
    
    redirect($page);
}
?>

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
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
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

// retrieve the page from the sitemap (checks whether this user has access to the requested page)
$page = $default->siteMap->getPage($action, $_SESSION["userID"]);

$default->log->debug("retrieved page=$page from SiteMap");
if (!$page) {
    // this user doesn't have permission to access the page
    // or there is no page mapping for the requested action
    // redirect to no permission page
    redirect("$default->owl_ui_url/noAccess.php");
} else {
    $default->log->debug("control.php redirect=$redirect");
    $page = $default->owl_root_url . $page;
    // set authorised flag and redirect
    // strip querystring form $page before setting page authorisation flag
    if (strstr($page, "?")) {
        $accessPage = substr($page, 0, strpos($page, "?"));
        $default->log->debug("control.php: page without querystring=$accessPage; with=$page");
    } else {
        $accessPage = $page;
    }
    
    if (strlen($redirect) > 0) {
        $page = $page . (strstr($page, "?") ? "&redirect=$redirect" : "?redirect=$redirect");
    }
    
    $_SESSION["pageAccess"][$accessPage] = true;
    $default->log->debug("control.php: just set SESSION[\"pageAccess\"][$accessPage]=" . $_SESSION["pageAccess"][$accessPage]); 
    redirect($page);
}
?>

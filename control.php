<?php

// main library routines and defaults
require_once("./config/dmsDefaults.php");
//require_once("$default->fileSystemRoot/lib/session/SiteMap.inc");

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
//echo $_SERVER["QUERY_STRING"];
// check the session, but don't redirect if the check fails
if (checkSessionAndRedirect(false)) {
	//get around the problem with search
	if (strcmp($fForStandardSearch, "yes") == 0) {			
			$action = "standardSearch";
	} else if (!isset($action)) {
    // session check succeeds, so default action should be the dashboard if no action was specified    
		$action = "dashboard";
	}    
} else {	
    // session check fails, so default action should be the login form if no action was specified
    if (!isset($action)) {
        $action = "loginForm";
    }
}

// need to strip query string params from action before attempting to retrieve from sitemap

// check for the presence of additional params
//echo $_SERVER["QUERY_STRING"];
if (strstr($_SERVER["QUERY_STRING"], "&")) {
    // strip and save the querystring
    $queryString = substr($_SERVER["QUERY_STRING"], strpos($_SERVER["QUERY_STRING"], "&")+1, strlen($_SERVER["QUERY_STRING"]));
} else if (strstr($_SERVER["QUERY_STRING"], "?")) {
    // strip and save the querystring
    $queryString = substr($_SERVER["QUERY_STRING"], strpos($_SERVER["QUERY_STRING"], "?")+1, strlen($_SERVER["QUERY_STRING"]));
    // update
    $action = substr($_SERVER["QUERY_STRING"], 0, strpos($_SERVER["QUERY_STRING"], "?"));
}
    
// retrieve the page from the sitemap (checks whether this user has access to the requested page)
$page = $default->siteMap->getPage($action, $_SESSION["userID"]);

if (!$page) {
    // this user doesn't have permission to access the page
    // or there is no page mapping for the requested action
    // redirect to no permission page
    $default->log->error("control.php getPage failed for ($action, " . $_SESSION["userID"] . ")");
    redirect("$default->uiUrl/noAccess.php");
} else {
    $page = $default->rootUrl . $page;
    // set authorised flag and redirect
    // strip querystring from the page returned from the sitemap
    // before setting page authorisation flag (since checkSession checks page level
    // access by checking $_SESSION["pageAccess"][$_SERVER["PHP_SELF"] ie. without querystring(?)
    if (strstr($page, "?")) {
        $accessPage = substr($page, 0, strpos($page, "?"));
    } else {
        $accessPage = $page;
    }
    
    // if we have a redirect url, then append it(??)
    if (strlen($redirect) > 0) {
        $page = $page . (strstr($page, "?") ? "&redirect=$redirect" : "?redirect=$redirect");
    }
    
    $_SESSION["pageAccess"][$accessPage] = true;
    // if we have a querystring add it on
    if (strlen($queryString) > 0) {
        $page = $page . (strstr($page, "?") ? "&$queryString" : "?$queryString");
        $default->log->info("control.php: about to redirect to $page");
    }
    redirect($page);
}
?>

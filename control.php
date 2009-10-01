<?php
/*
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *  
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

// check if system has been installed
require_once("setup/wizard/installUtil.php");
// Check if system has been installed
$iu = new InstallUtil();
if(!$iu->isSystemInstalled()) {
	$iu->redirect("setup/wizard");
	exit(0);
}

// main library routines and defaults
require_once('config/dmsDefaults.php');

/**
 * Controller page -- controls the web application by responding to a set of
 * defined actions.  The controller performs session handling, page-level
 * authentication and forwards the request to the appropriate handling
 * page.
 */

// -------------------------------
// page start
// -------------------------------

$action = $_REQUEST['action'];

if ($action != 'login') {

    // check the session, but don't redirect if the check fails
    $ret = checkSessionAndRedirect(false);
    if ($ret === true) {
        //get around the problem with search
        if (strcmp($_REQUEST['fForStandardSearch'], 'yes') == 0) {
            $action = 'standardSearch';
        } else if (!isset($action)) {
        // session check succeeds, so default action should be the dashboard if no action was specified
            $action = 'dashboard';
        }
    } else {
        // session check fails, so default action should be the login form if no action was specified
        $oKTConfig = KTConfig::getSingleton();
        $dest = 'login';
        if ($oKTConfig->get('allowAnonymousLogin', false)) { $dest = 'dashboard'; }
            
        if (!isset($action)) {
            $action = $dest;
        } elseif ($action <> $dest) {
            // we have a controller link and auth has failed, so redirect to the login page
            // with the controller link as the redirect
            $url = generateControllerUrl('login');
            $redirect = urlencode($_SERVER[PHP_SELF] . '?' . $_SERVER['QUERY_STRING']);
            if ((strlen($redirect) > 1)) {
                $url = $url . '&redirect=' . $redirect;
            }
            if (PEAR::isError($ret)) {
                $url = $url . '&errorMessage=' .  urlencode($ret->getMessage());
                session_start();
                $_SESSION['errormessage']['login'] = $ret->getMessage();
            }
            redirect($url);
            exit(0);
        }
    }
}

// we appear to have some encoding/decoding issues, so we need to force-check for %30 type situations
$queryString = KTUtil::arrayGet($_REQUEST, 'qs', '');
if (is_array($queryString)) {
    $aStrings = array();
    foreach ($queryString as $k => $v) {
        $aStrings[] = $k . '=' . $v;
    }
    $queryString = join('&', $aStrings);
} elseif (count(preg_match('#\%#', $queryString) != 0)) {
    $queryString = urldecode($queryString);
}

if (empty($queryString)) {
    // need to strip query string params from action before attempting to retrieve from sitemap
    $queryString = '';
    // check for the presence of additional params
    if (strstr($_SERVER['QUERY_STRING'], '&')) {
        // strip and save the querystring
        $queryString = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], '&')+1, strlen($_SERVER['QUERY_STRING']));
    } else if (strstr($_SERVER['QUERY_STRING'], '?')) {
        // strip and save the querystring
        $queryString = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], '?')+1, strlen($_SERVER['QUERY_STRING']));
        // update
        $action = substr($_SERVER['QUERY_STRING'], 0, strpos($_SERVER['QUERY_STRING'], '?'));
    }
}

if ($action == 'dashboard') { 
    $oKTConfig = KTConfig::getSingleton();
    if(!$oKTConfig->get('useNewDashboard')) $action = 'olddashboard'; 
}

// retrieve the page from the sitemap (checks whether this user has access to the requested page)
$page = $default->siteMap->getPage($action, isset($_SESSION['userID']) ? $_SESSION['userID'] : '');

if (!$page) {
    // this user doesn't have permission to access the page
    // or there is no page mapping for the requested action
    // redirect to no permission page
    $default->log->error("control.php getPage failed for ($action, " . $_SESSION['userID'] . ")");
    redirect("$default->uiUrl/noAccess.php");
} else {
    $page = $default->rootUrl . $page;
    // set authorised flag and redirect
    // strip querystring from the page returned from the sitemap
    // before setting page authorisation flag (since checkSession checks page level
    // access by checking $_SESSION["pageAccess"][$_SERVER["PHP_SELF"] ie. without querystring(?)
    
    $paramStart=strpos($page, '?');
    if ($paramStart !== false) {
        $accessPage = substr($page, 0, $paramStart);
    } else {
        $accessPage = $page;
    }
    $_SESSION['pageAccess'][$accessPage] = true;
    // if we have a querystring add it on
    if (strlen($queryString) > 0) {
    	$page .= ($paramStart !== false)?'&':'?';
    	$page .= $queryString;
        $default->log->info("control.php: about to redirect to $page");
    }
    redirect($page);
}

?>

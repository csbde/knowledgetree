<?php

// main library routines and defaults
require_once("../config/dmsDefaults.php");

/**
 * $Id$
 *  
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 */

// -------------------------------
// page start
// -------------------------------
global $default;

if ($loginAction == "loginForm") {
    // TODO: build login form using PatternMainPage
    print "<html>
    <head>
    <link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">
    <link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">
    <title>The KnowledgeTree</title>
    </head>
    <body>
    <center>
    <img src=\"$default->rootUrl/locale/$default->defaultLanguage/graphics/$default->logo\">
    <br><br>
    <table>\n
    <form action=\"login.php\" method=\"post\">
    <tr><td>Please enter your details below to login</td></tr>
    <tr><td></td></tr>
    <tr><td><font color=\"red\">" . urldecode($errorMessage) . "</font><tr><td>
    \t<tr><td>$lang_username:</td></tr>
    \t<tr><td><input type=\"text\" name=\"fUserName\" size=\"35\"></td></tr>
    \t<tr><td>$lang_password:</td></tr>
    <tr><td><input type=\"password\" name=\"fPassword\" size=\"35\">
    </td></tr>
    <input type=\"hidden\" name=\"redirect\" value=\"$redirect\"/>
    <input type=\"hidden\" name=\"loginAction\" value=\"login\">\n
    <tr align=\"right\"><td><input type=\"image\" src=\"$default->graphicsUrl/icons/login.jpg\" border=\"0\"></td></tr>\n
    </table>
    </center>
    </body>
    </html>";

}
elseif ($loginAction == "login") {
    // set default url for login failure
    $url = $url . "login.php?loginAction=loginForm";
    // if requirements are met and we have a username and password to authenticate
    if( isset($fUserName) && isset($fPassword) ) {
        // verifies the login and password of the user
        $dbAuth = new $default->authenticationClass;
        $userDetails = $dbAuth->login($fUserName, $fPassword);

        switch ($userDetails["status"]) {
            // bad credentials
        case 0:
                $url = $url . "&errorMessage=" . urlencode($lang_loginfail);
            break;
            // successfully authenticated
        case 1:
            // start the session
            $session = new Session();
            $sessionID = $session->create($userDetails["userID"]);

            // initialise page-level authorisation array
            $_SESSION["pageAccess"] = NULL;

            // check for a location to forward to
            if (isset($redirect) && strlen(trim($redirect))>0) {
                $redirect = urldecode($redirect);
                // remove any params from redirect before looking up from sitemap
                if (strstr($redirect, "?")) {
                    $queryString = substr($redirect, strpos($redirect, "?")+1, strlen($redirect));
                    $redirect = substr($redirect, 0, strpos($redirect, "?"));
                }

                // need to strip rootUrl off $redirect
                if (strlen($default->rootUrl) > 0) {
                    $redirect = substr($redirect, strpos($redirect, $default->rootUrl)+strlen($default->rootUrl), strlen($redirect));
                }
                $action = $default->siteMap->getActionFromPage($redirect);
                if ($action) {
                    $url = generateControllerUrl($action);
                } else {
                    // default to the dashboard
                    $url = generateControllerUrl("dashboard");
                }

            // else redirect to the dashboard if there is none
            } else {
                $url = generateControllerUrl("dashboard");
            }
            break;
            // login disabled
        case 2:
            $url = $url . "&errorMessage=" . urlencode($lang_logindisabled);
            break;
            // too many sessions
        case 3 :
            $url = $url . "&errorMessage=" . urlencode($lang_toomanysessions);
            break;
            // not a unit user
        case 4 :
            $url = $url . "&errorMessage=" . urlencode("Not unit user- contact an Administrator");
            break;            
        default :
            $url = $url . "&errorMessage=" . urlencode($lang_err_general);
        }
    } else {
        // didn't receive any login parameters, so redirect login form
        // TODO: set "no login parameters received error message?
        // internal error message- should never happen
    }
    if (strlen($queryString) > 0) {
        $url .= "&$queryString";
    }
    $default->log->debug("login.php: about to redirect to $url");
    redirect($url);
}
?>



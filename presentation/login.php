<?php

// main library routines and defaults
require_once("../config/dmsDefaults.php");
require_once("../lib/util/sanitize.inc");
require_once("Html.inc");
/**
 * $Id$
 *  
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

global $default;

$redirect = $_REQUEST['redirect'];
$errorMessage = $_REQUEST['errorMessage'];

if ($_REQUEST['loginAction'] == "loginForm") {
    // TODO: build login form using PatternMainPage
    $cookietest = KTUtil::randomString();
    setcookie("CookieTestCookie", $cookietest, false);
    print "<html>
    <head>
    <link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">
    <link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">
    <title>The KnowledgeTree</title>

	<SCRIPT TYPE=\"text/javascript\">
	<!--
	function submitenter(myfield,e)	{
		var keycode;
		if (window.event) { 
			keycode = window.event.keyCode;
		} else if (e) {
			keycode = e.which;
		} else {
			return true;
		}
		
		if (keycode == 13) {
		   myfield.form.submit();
		   return false;
		} else {
		   return true;
		}
	}
	//-->
	</SCRIPT>
    
    </head>
    <body onload=\"javascript:document.loginForm.fUserName.focus()\">
    <center>
    <img src=\"$default->graphicsUrl/ktLogin.jpg\">
    <br><br>
    <table>\n
    <form name=\"loginForm\" action=\"" . $_SERVER["PHP_SELF"] . "\" method=\"post\">
    <tr><td>" . _("Please enter your details below to login") . "</td></tr>
    <tr><td></td></tr>
    <tr><td><font color=\"red\">" . sanitize($errorMessage) . "</font><tr><td>
    \t<tr><td>" . _("Username") . ":</td></tr>
    \t<tr><td><input type=\"text\" name=\"fUserName\" size=\"35\"></td></tr>
    \t<tr><td>" . _("Password") . ":</td></tr>
    <tr><td><input type=\"password\" name=\"fPassword\" size=\"35\" onKeyPress=\"return submitenter(this,event)\">
    </td></tr>
    <input type=\"hidden\" name=\"redirect\" value=\"$redirect\"/>
    <input type=\"hidden\" name=\"loginAction\" value=\"login\">\n
    <input type=\"hidden\" name=\"cookietestinput\" value=\"$cookietest\">\n
    <tr align=\"right\"><td><input type=\"image\" src=\"" . KTHtml::getLoginButton() . "\" border=\"0\"></td></tr>\n
    <tr><td><font size=\"1\">" . _("System Version") . ": " . $default->systemVersion . "</font></td></tr>
    </table>
    </center>
    </body>
    </html>";

} elseif ($_REQUEST['loginAction'] == "login") {
    // set default url for login failure
    // with redirect appended if set
    $url = $url . "login.php?loginAction=loginForm" . (isset($redirect) ? "&redirect=" . urlencode($redirect) : "");
    $cookieTest = KTUtil::arrayGet($_COOKIE, "CookieTestCookie", null);
    if (is_null($cookieTest) || $cookieTest != KTUtil::arrayGet($_REQUEST, "cookietestinput")) {
        $url .= "&errorMessage=" . urlencode(_("KnowledgeTree requires cookies to work"));
        redirect($url);
        exit(0);
    }
    
    // if requirements are met and we have a username and password to authenticate
    if (isset($_REQUEST['fUserName']) && isset($_REQUEST['fPassword']) ) {
        // verifies the login and password of the user
        $dbAuth = new $default->authenticationClass;
        $userDetails = $dbAuth->login($_REQUEST['fUserName'], $_REQUEST['fPassword']);

        switch ($userDetails["status"]) {
            // bad credentials
        case 0:
                $url = $url . "&errorMessage=" . urlencode(_("Login failure"));
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
            $url = $url . "&errorMessage=" . urlencode(_("Account has been DISABLED, contact the System Adminstrator"));
            break;
            // too many sessions
        case 3 :
            $url = $url . "&errorMessage=" . urlencode(_("Maximum sessions for user reached.<br>Contact the System Administrator"));
            break;
            // not a unit user
        case 4 :
            $url = $url . "&errorMessage=" . urlencode(_("This user does not belong to a group and is therefore not allowed to log in."));
            break;            
        default :
            $url = $url . "&errorMessage=" . urlencode(_("Login failure"));
        }
    } else {
        // didn't receive any login parameters, so redirect login form
        $default->log->error("login.php no login parameters received");
    }
    if (strlen($queryString) > 0) {    	
        $url .= "&$queryString";
    }
    redirect($url);
} else {
	// redirect to root
    $url = generateLink("", "");
	redirect($url);
}
?>

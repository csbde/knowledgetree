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
    //include("./lib/header.inc");
	print "<CENTER>";
	print "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'>";
    print "<BR><HR WIDTH=300>"; 
	print "<FORM ACTION=\"login.php\" METHOD=\"POST\">";
	
    if (isset($fileid)) {
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"parent\" value=\"$parent\">";
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"fileid\" value=\"$fileid\">";
    }
    
    print "<font color=\"red\">$errorMessage</font><br>";
        
	print "<TABLE><TR><TD>$lang_username:</TD><TD>
           <INPUT TYPE=\"TEXT\" NAME=\"fUserName\"><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD>
           <INPUT TYPE=\"PASSWORD\" NAME=\"fPassword\"><BR></TD></TR></TABLE>";
    print "<input type=\"hidden\" name=\"redirect\" value=\"$redirect\"/>";
	print "<INPUT TYPE=\"hidden\" name=\"loginAction\" value=\"login\">\n";    
	print "<INPUT TYPE=\"SUBMIT\" Value=\"$lang_login\">\n";
	print "<BR><BR><HR WIDTH=300>";
    //include("./lib/footer.inc");
    
} elseif ($loginAction == "login") {
    // set default url for login failure
    $url = $url . "login.php?loginAction=loginForm";
    // if requirements are met and we have a username and password to authenticate
    if( isset($fUserName) && isset($fPassword) ) {
        // verifies the login and password of the user
        $dbAuth = new $default->authentication_class;
        $userDetails = $dbAuth->login($fUserName, $fPassword);

        switch ($userDetails["status"]) {
            // bad credentials
            case 0:
                $url = $url . "&errorMessage=$lang_loginfail";
                break;
            // successfully authenticated
            case 1:
                // start the session
                $session = new Session();
                $sessionID = $session->create($userDetails["userID"]);
                
                // check for a location to forward to
                if (isset($redirect) && strlen(trim($redirect))>0) {
                    $url = urldecode($redirect);
                // else redirect to the dashboard
                } else {
                    $_SESSION["authorised"] = false;                        
                    $url = "/control.php?action=dashboard";
                }
                break;
            // login disabled                    
            case 2:
                $url = $url . "&errorMessage=$lang_logindisabled";
                break;
            // too many sessions
            case 3 :
                $url = $url . "&errorMessage=$lang_toomanysessions";
                break;
            default :
                $url = $url . "&errorMessage=$lang_err_general";
        }
    } else {
        // didn't receive any login parameters, so redirect login form
        // TODO: set "no login parameters received error message?
        // internal error message- should never happen
    }
    $default->log->debug("login.php: about to redirect to $url");
    redirect($url);
}
?>



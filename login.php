<?php

/**
 * $Id$
 *  
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dms
 */
 
// main library routines and defaults
require_once("./config/dmsDefaults.php");
require_once("./lib/owl.lib.php");
require_once("./lib/control.inc");
require_once("./config/html.php");
require_once("./lib/Session.inc");



if ($loginAction == "loginForm") {
    // TODO: build login form using PatternMainPage
    include("./lib/header.inc");
	print("<CENTER>");
	print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'><BR>$lang_engine<BR>$lang_version: $default->version<BR><HR WIDTH=300>"); 
	print "<FORM ACTION=\"login.php\" METHOD=\"POST\">";
	
    if (isset($fileid)) {
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"parent\" value=\"$parent\">";
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"fileid\" value=\"$fileid\">";
    }
    if (isset($loginFailureMessage)) {
        print "$loginFailureMessage<br>";
    }
        
	print "<TABLE><TR><TD>$lang_username:</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"fUserName\"><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"fPassword\"><BR></TD></TR></TABLE>";
    print "<input type=\"hidden\" name=\"redirect\" value=\"<?php echo $redirect ?>\"/>";
    print "<INPUT TYPE=\"hidden\" name=\"action\" value=\"login\">\n";
	print "<INPUT TYPE=\"hidden\" name=\"loginAction\" value=\"login\">\n";    
	print "<INPUT TYPE=\"SUBMIT\" Value=\"$lang_login\">\n";
	print "<BR><BR><HR WIDTH=300>";
    //include("./lib/footer.inc");
    
} elseif ($loginAction == "login") {
    // check the requirements
    if (checkrequirements() == 1) {
        // TODO: appropriate error message
        exit;
    } else {
        // if requirements are met and we have a username and password to authenticate
        if( isset($fUserName) && isset($fPassword) ) {
            // verifies the login and password of the user
            $dbAuth = new DBAuthenticator();
            $userDetails = $dbAuth->login($fUserName, $fUserName);
            switch ($userDetails["status"]) {
                // successfully authenticated
                case 1:
                    // start the session
                    $sessionID = Session::create($userDetails["userID"]);
                    // check for a location to forward to
                    //echo "started session, with id=$sessionID<br>";
                    /*
                    if (isset($redirect) && strlen(trim($redirect))>0) {
                        echo "it is set to $redirect<br>";
                        $url = $redirect;
                       //redirect($redirect);
                    } else {*/
                        $url = "control.php?action=DASHBOARD";
                    //}
                    //echo "url set to $url<br>";
                    break;
                // login disabled                    
                case 2:
                    $url = "control.php?action=loginForm&loginFailureMessage=$lang_logindisabled";
                    break;
                // too many sessions
                case 3 :
                    $url = "control.php?action=loginForm&loginFailureMessage=$lang_toomanysessions";
                    break;
                default :
                    $url = "control.php?action=loginForm&loginFailureMessage=$lang_err_general";
            }
        } else {
            // didn't receive any login parameters, so redirect login form
            $url = "control.php?action=loginForm";
        }
        //echo "about to redirect to $url<br>";
        redirect($url);
    }
}
?>



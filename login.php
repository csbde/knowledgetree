<?php

// main library routines and defaults
require_once("./config/owl.php");
require_once("./lib/owl.lib.php");
require_once("./config/html.php");
require_once("./lib/Authenticator.inc");
require_once("./lib/Session.php");

// this page displays the login form
// and performs the business logic login code

if ($loginAction == "loginForm") {
    // TODO: build login form using PatternMainPage
    include("./lib/header.inc");
	print("<CENTER>");
	print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'><BR>$lang_engine<BR>$lang_version: $default->version<BR><HR WIDTH=300>"); 
	print "<FORM ACTION=\"control.php\" METHOD=\"POST\">";
	
    if (isset($fileid)) {
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"parent\" value=\"$parent\">";
        print "<INPUT TYPE=\"HIDDEN\" NAME=\"fileid\" value=\"$fileid\">";
    }
        
	print "<TABLE><TR><TD>$lang_username:</TD><TD><INPUT TYPE=\"TEXT\" NAME=\"fUserName\"><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"fPassword\"><BR></TD></TR></TABLE>";
    print "<INPUT TYPE=\"hidden\" name=\"action\" value=\"login\">\n";
	print "<INPUT TYPE=\"hidden\" name=\"loginAction\" value=\"login\">\n";    
	print "<INPUT TYPE=\"SUBMIT\" Value=\"$lang_login\">\n";
	print "<BR><BR><HR WIDTH=300>";
    include("./lib/footer.inc");
    
} elseif ($loginAction == "login") {
    
    // check the requirements
    if (checkrequirements() == 1) {
        // TODO: appropriate error message
        exit;
    } else {
        // if requirements are met and we have a username and password to authenticate
        if( isset($fUserName) && isset($fPassword) ) {
            // verifies the login and password of the user
            $userDetails = Authenticator::login($fUserName, $fUserName)

            switch ($userDetails["status"]) {
                // successfully authenticated
                case 1:
                    $sessionID = Session::create($userDetails["userID"]);
                    // check query string and forward to requested page
                    $qString = $_SERVER["QUERY_STRING"];
                    // should be login.php?
                    // else forward to dashboard (config defined page/action)
                    break;
                // login disabled                    
                case 2:
                    redirect("control.php?action=loginForm&loginFailureMessage=");
                    break;
                // too many sessions
                case 3 :
                    redirect("control.php?action=loginForm&loginFailureMessage=");
                    break;
                default :
                    redirect("control.php?action=loginForm&loginFailureMessage=");
            }
        } else {
            // didn't receive any login parameters, so redirect login form
            $url = "control.php?action=loginForm";
            redirect($url);
        }
    }
}
?>



<?php

/**
 * index.php -- Main page
 *  
 * This is the main login page
 * Does some user verification and authentication as well as 
 * Determining the Role of the User logging in (i.e. Admin or user) 
 *
 * Creates a new session for the user for duration of usage
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version v 1.1.1.1 2002/12/04
 * @Copyright (c) 1999-2002 The Owl Project Team
 * @author michael
 * @package test
 */

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");

//check the requirements
if (checkrequirements() == 1) 
{
exit;
}
/*
* $failure is a counter to the number of times the user has tried to
* login.
*/
if(!isset($failure)) 
{
	$failure = 0;
}
if(!$login)
{
	 $login = 1;
}

// if requirements are met
if($loginname && $password) 
{
	//Verifies the Login and password of the user
	$verified["bit"] = 0;
	$verified = verify_login($loginname, $password);
	if ($verified["bit"] == 1) 
	{
		// if verified open a new session 
		$session = new Owl_Session;
		$uid = $session->Open_Session(0,$verified["uid"]);
	/*
		$sql = new Owl_DB;
		$sql->query("select * from $default->owl_folders_table where parent = '2' and name = '$loginname'");
			while($sql->next_record()) $id = $sql->f("id");
	*/
		$id = 1;

        /* BEGIN Admin Change */

        /*  If an admin signs on We want to se the admin menu
            Not the File Browser.                               */
      		  if ( $verified["group"] == 0)
      		  {
        		// if admin logs on..goto the admin main page
        		// else goto the normal file browser page
			  if(!isset($fileid))
			  {
		  		header("Location: admin/index.php?sess=". $uid->sessdata["sessid"]);
               		  }
                  	  else
                  	  {
                  		header("Location: browse.php?sess=". $uid->sessdata["sessid"]."&parent=$parent&fileid=$fileid");
                  	  }
        	  }
        	else
        	  {
		  	if(!isset($fileid))
		  	{
		  		header("Location: browse.php?sess=". $uid->sessdata["sessid"]);
		  	}
                  	else
                  	{
                   		header("Location: browse.php?sess=". $uid->sessdata["sessid"]."&parent=$parent&fileid=$fileid");
                  	}
        	 }
       /* END Admin Change */

	}
	 else 
		{//normal user..check failures
			if ($verified["bit"] == 2) 
			{
				header("Location: index.php?login=1&failure=2");
			}
                	else if ($verified["bit"] == 3 )
                	{
 				header("Location: index.php?login=1&failure=3");
 			}
			else 
			{
				header("Location: index.php?login=1&failure=1");
			}
		}
}

// 
if(($login == 1) || ($failure == 1))
 {
	include("./lib/header.inc");
	print("<CENTER>");
// BUG Number: 457588
// This is to display the version inforamation
// BEGIN
	print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'><BR>$lang_engine<BR>$lang_version: $default->version<BR><HR WIDTH=300>");
// END
	if($failure == 1)
	{
	 	print("<BR>$lang_loginfail<BR>");
	}
        if($failure == 2)
        {
         	print("<BR>$lang_logindisabled<BR>");
        }
        if($failure == 3) 
        {
        	print("<BR>$lang_toomanysessions<BR>");
        }
	print "<FORM ACTION=index.php METHOD=POST>";
	
        if (isset($fileid))
        {
        	print "<INPUT TYPE=HIDDEN NAME=parent value=$parent>";
        	print "<INPUT TYPE=HIDDEN NAME=fileid value=$fileid>";
        }
        
	print "<TABLE><TR><TD>$lang_username:</TD><TD><INPUT TYPE=TEXT NAME=loginname><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD><INPUT TYPE=PASSWORD NAME=password><BR></TD></TR></TABLE>";
	print "<INPUT TYPE=SUBMIT Value=$lang_login>\n";
	print "<BR><BR><HR WIDTH=300>";
	exit;
}
// when the user logouts the session is deleted from the session table
if($login == "logout") 
{
	include("./lib/header.inc");
	print("<CENTER>");
// BUG Number: 457588
// This is to display the version inforamation
// BEGIN
	print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'><BR>$lang_engine<BR>$lang_version: $default->version<BR><HR WIDTH=300>");
// END
	$sql = new Owl_DB;
	$sql->query("delete from $default->owl_sessions_table where sessid = '$sess'");
	print("<BR>$lang_successlogout<BR>");
	print "<FORM ACTION=index.php METHOD=POST>";
	print "<TABLE><TR><TD>$lang_username:</TD><TD><INPUT TYPE=TEXT NAME=loginname><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD><INPUT TYPE=PASSWORD NAME=password><BR></TD></TR></TABLE>";
	print "<INPUT TYPE=SUBMIT Value=$lang_login>\n";
	print "<BR><BR><HR WIDTH=300>";
	exit;
}
include("./lib/footer.inc");
?>

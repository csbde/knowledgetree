<?php

/*
 * index.php -- Main page
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
*/
require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");

if (checkrequirements() == 1) {
exit;
}
if(!isset($failure)) $failure = 0;
if(!$login) $login = 1;

if($loginname && $password) {
	$verified["bit"] = 0;
	$verified = verify_login($loginname, $password);
	if ($verified["bit"] == 1) {
		$session = new Owl_Session;
		$uid = $session->Open_Session(0,$verified["uid"]);
	/*
		$sql = new Owl_DB;
		$sql->query("select * from $default->owl_folders_table where parent = '2' and name = '$loginname'");
			while($sql->next_record()) $id = $sql->f("id");
	*/
		$id = 1;




        /* BEGIN Bozz Change */

        /*  If an admin signs on We want to se the admin menu
            Not the File Browser.                               */
        if ( $verified["group"] == 0)
        {
		  if(!isset($fileid))
                   header("Location: admin/index.php?sess=". $uid->sessdata["sessid"]);
                  else
                   header("Location: browse.php?sess=". $uid->sessdata["sessid"]."&parent=$parent&fileid=$fileid");
        }
        else
        {
		  if(!isset($fileid))
                   header("Location: browse.php?sess=". $uid->sessdata["sessid"]);
                  else
                   header("Location: browse.php?sess=". $uid->sessdata["sessid"]."&parent=$parent&fileid=$fileid");
        }
       /* END Bozz Change */

	} else {
		if ($verified["bit"] == 2) 
			header("Location: index.php?login=1&failure=2");
                else if ($verified["bit"] == 3 )
 			header("Location: index.php?login=1&failure=3");
		else 
			header("Location: index.php?login=1&failure=1");
	}
}


if(($login == 1) || ($failure == 1)) {
	include("./lib/header.inc");
	print("<CENTER>");
// BUG Number: 457588
// This is to display the version inforamation
// BEGIN
	print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo'><BR>$lang_engine<BR>$lang_version: $default->version<BR><HR WIDTH=300>");
// END
	if($failure == 1) print("<BR>$lang_loginfail<BR>");
        if($failure == 2) print("<BR>$lang_logindisabled<BR>");
        if($failure == 3) print("<BR>$lang_toomanysessions<BR>");
	print "<FORM ACTION=index.php METHOD=POST>";
        if (isset($fileid)) {
        	print "<INPUT TYPE=HIDDEN NAME=parent value=$parent>";
        	print "<INPUT TYPE=HIDDEN NAME=fileid value=$fileid>";
        }
	print "<TABLE><TR><TD>$lang_username:</TD><TD><INPUT TYPE=TEXT NAME=loginname><BR></TD></TR>";
	print "<TR><TD>$lang_password:</TD><TD><INPUT TYPE=PASSWORD NAME=password><BR></TD></TR></TABLE>";
	print "<INPUT TYPE=SUBMIT Value=$lang_login>\n";
	print "<BR><BR><HR WIDTH=300>";
	exit;
}

if($login == "logout") {
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

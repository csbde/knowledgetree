<?php

/**
 * prefs.php -- Main page
 *  
 * This is the preferences page
 *
 * This page displays the various preference of the specific user
 * It Allows for ordering of informatio nas well as the changing 
 * Of specific information e.g. passwords etc
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
include("./lib/header.inc");

// Begin 496814 Column Sorts are not persistant
// + ADDED &order=$order&$sortorder=$sortname to
// all browse.php?  header and HREF LINES
// Begin 496814 Column Sorts are not persistant

// This handles the sorting order of information
switch ($order) 
{
     case "name":
           $sortorder = 'sortname';
           break;
     case "major_revision":
           $sortorder = 'sortver';
           break;
     case "filename" :
           $sortorder = 'sortfilename';
           break;
     case "size" :
           $sortorder = 'sortsize';
           break;
     case "creatorid" :
           $sortorder = 'sortposted';
           break;
     case "smodified" :
           $sortorder = 'sortmod';
           break;
     case "checked_out":
           $sortorder = 'sortcheckedout';
           break;
     default:
          $sort="ASC";
          break;
}

// END 496814 Column Sorts are not persistant
// BEGIN BUG FIX: #433932 Fileupdate and Quotas

print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>

<TR><TD ALIGN=LEFT><?php print "$lang_user: "; print uid_to_name($userid);?> <FONT SIZE=-1>
<?
	php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
    </FONT></TD><TD ALIGN=RIGHT>
<?php 
	print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0>");?>
        </A></TD></TR></TABLE>
        
        <?php print $lang_preference; ?><br><hr width=50%>
<?php

if(!$action) 
{
	$action = "users";
}

//-------------------------------------------------------------
/**
 *   Function Printuser($id)
 *
 *   Displays the user information
 *
 *   @param $id
 *	The UserId of the user that is currently logged on 
*/
//-------------------------------------------------------------
// Not Usable - Interface has to Change
function printuser($id)
 {
 	// declare some global variables
	global $order, $sortname, $sort;
        global $sess,$change,$lang_saved,$lang_title,$lang_group,$lang_username,$lang_change,$lang_quota,$lang_groupmember;
	global $lang_deleteuser, $default, $expand, $parent, $lang_oldpassword, $lang_newpassword, $lang_confpassword;
	global $lang_email, $lang_notification, $lang_userlang,$lang_attach_file;
	
	if(isset($change))
	{ 
		print("$lang_saved<BR>");
	}
	
	// get the id  and name from the groups table
	$sql = new Owl_DB;
	$sql->query("select id,name from $default->owl_groups_table");
	$i=0;
	
	// store in a double array
	while($sql->next_record()) 
	{
		$groups[$i][0] = $sql->f("id");
		$groups[$i][1] = $sql->f("name");
		$i++;
	}
	
	//get all information from the users table based on the group id
	$sql->query("select * from $default->owl_users_table where id = '$id'");
	// loop through the recordset
	while($sql->next_record()) 
	{
		print("<FORM ACTION='./dbmodify.php' METHOD=POST>");
		print("<INPUT TYPE=HIDDEN NAME=id VALUE=".$sql->f("id").">");
                print("<INPUT TYPE=HIDDEN NAME=order VALUE='$order'>");
                print("<INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>");
		print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
		print("<INPUT TYPE=HIDDEN name=action VALUE=user>");
		print("<INPUT TYPE=HIDDEN name=expand VALUE=$expand>");
		print("<INPUT TYPE=HIDDEN name=parent VALUE=$parent>");
		print("<TABLE><TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_title</TD><TD><INPUT TYPE=text NAME=name VALUE='".$sql->f("name")."'></TD></TR>");
		
        //*******************************
        // Display the Language dropdown
        //*******************************
                
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_userlang</TD><TD align=left><SELECT NAME=newlanguage>");
                print("<OPTION VALUE=".$sql->f("language").">".$sql->f("language"));
                
                $dir = dir($default->owl_LangDir);
                $dir->rewind();
                                                         
                while($file=$dir->read())
                {
                     if ($file != "." and $file != "..")
                     {
                        print("<OPTION VALUE=$file>$file");
                     }
                }
                 $dir->close();
                
               // get values if password is to be changed
                print("</SELECT></TD></TR>"); 
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_oldpassword</TD><TD><INPUT TYPE=PASSWORD NAME=oldpassword VALUE=></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_newpassword</TD><TD><INPUT TYPE=PASSWORD NAME=newpassword VALUE=></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_confpassword</TD><TD><INPUT TYPE=PASSWORD NAME=confpassword VALUE=></TD></TR>");
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_email</TD><TD align=left><INPUT TYPE=TEXT NAME=email VALUE='".$sql->f("email")."'></TD></TR>");
 
 		// check if notification has been checked on
                if ( $sql->f("notify") == 1)
                {
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_notification</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=notify VALUE=1 checked></TD></TR>");
                }
                else
                {
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_notification</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=notify VALUE=1></TD></TR>");
                }
               
               // check if attachfile has been checked
                if ( $sql->f("attachfile") == 1)
                {
                   	 print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_attach_file</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=attachfile VALUE=1 checked></TD></TR>");
                }
                else
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_attach_file</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=attachfile VALUE=1></TD></TR>");
		print("</TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_change>");
		print("</FORM>"); 
		}
}

//-------------------------------------------------------------
// if an action has occurred, check if user is set and then print out user preferences
if($action) 
{
	if(isset($owluser)) 
	{
		printuser($owluser);
	}
} 
else 
{
	exit("$lang_err_general");
}

$expand = 0;
include("./lib/footer.inc");

?>

<?php

/**
 * modify.php
 *
 * Displays forms for file (upload, update, modify, email) and folder (create, modify) 
 * maintenance and management.
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 * @todo line 27- refactor
 * @todo line 55-71- refactor into header.inc and new navigation.inc
 * @todo quote attribute values in all forms
 * @todo refactor permission handling
 */

//print("<H1>MODIFY Sess: $sess<BR> Loginname: $loginname<BR> Login:$login</H1>");

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");
include("./lib/header.inc");

// Begin 496814 Column Sorts are not persistant
// + ADDED &order=$order&$sortorder=$sortname to
// all browse.php?  header and HREF LINES
switch ($order) {
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
        break;
}
// END 496814 Column Sorts are not persistant

print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
<TR><TD ALIGN=LEFT>
<?php print("$lang_user: ");
      if(prefaccess($userid)) {
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&parent=$parent&expand=$expand&order=$order&sortname=$sortname'>");
      }
      //print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
      print uid_to_name($userid);
      print ("</A>");
?>
<FONT SIZE=-1>

<?php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");?>
    </FONT></TD><TD ALIGN=RIGHT>
<?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0>");?>
        </A></TD></TR></TABLE>
<?php

print("<CENTER>");

if ($action == "file_update") {
	if(check_auth($id, "file_modify", $userid) == 1) {
		print("<BR>");
		$expand = 1;
		print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
		print("<TR><TD align=left>$lang_updating ".gen_navbar($parent)."/".flid_to_name($id)."</TD></TR>");
		print("</TABLE><HR WIDTH=$default->table_expand_width><BR>");
		print("<FORM enctype='multipart/form-data' ACTION='dbmodify.php' METHOD=POST>
			<INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
			<INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
			<INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'>
			<INPUT TYPE=HIDDEN NAME=parent VALUE=$parent>
			<INPUT TYPE=HIDDEN NAME=MAX_FILE_SIZE VALUE='$default->max_filesize'>
			<INPUT TYPE=HIDDEN NAME=action VALUE=file_update>
			<INPUT TYPE=HIDDEN NAME=id VALUE='$id'>");
		// BUG FIX: #449395 expanded/collapse view bugs
		print("<INPUT TYPE=HIDDEN NAME=expand VALUE='$expand'>");
		// END BUG FIX: #449395 expanded/collapse view bugs
		print("<TABLE BORDER=$default->table_border><TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_sendthisfile</TD><TD align=left><input name='userfile' type='file'></TD></TR>");
        
		// begin Daphne change - version control
		if ($default->owl_version_control == 1) {
		    print("<TR align=left><TD ALIGN=RIGHT bgcolor=$default->table_header_bg valign=top>$lang_vertype</td>
			   <td><SELECT NAME=versionchange>
			       <OPTION VALUE=major_revision>$lang_vermajor
			       <OPTION selected VALUE=minor_revision>$lang_verminor</select></td></tr>");
		    print("<tr><TD align=right bgcolor=$default->table_header_bg valign=top>$lang_verdescription
			   </td>
			   <td align=left><textarea name=newdesc rows=5 cols=30 wrap=hard></textarea></tr>");
		}
		// End Daphne Change
		print("</TABLE><INPUT TYPE=SUBMIT VALUE='$lang_sendfile'></FORM>");
		include("./lib/footer.inc");
	} else {
		print($lang_noupload);
	}
}

if ($action == "file_upload") {

	if(check_auth($parent, "folder_modify", $userid) == 1) {
		$expand = 1;

        /* BEGIN Bozz Change
           Retrieve Group information if the user is in the
           Administrator group   */

        if ( owlusergroup($userid) == 0 ) {
           $sql = new Owl_DB;
           $sql->query("select id,name from $default->owl_groups_table");
           $i=0;
           while($sql->next_record()) {
               $groups[$i][0] = $sql->f("id");
               $groups[$i][1] = $sql->f("name");
               $i++;
           }
        } else {        
            $sql = new Owl_DB;
            $sql->query("select userid,groupid from $default->owl_users_grpmem_table where userid = $userid ");
            if ($sql->num_rows($sql) == 0) {
                $sql->query("SELECT u.groupid as groupid, g.name as name  from $default->owl_users_table as u join $default->owl_groups_table as g where u.id = $userid and u.groupid = g.id");
            }
            $i=0;
            while($sql->next_record()) {
               $groups[$i][0] = $sql->f("groupid");
               $groups[$i][1] = group_to_name($sql->f("groupid"));
               $i++;
            }
        }
        /* END Bozz Change */

		print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
		print("<TR><TD align=left>$lang_addingfile".gen_navbar($parent)."</TD></TR>");
		print("</TABLE><HR WIDTH=$default->table_expand_width><BR>");
		print("<FORM enctype= 'multipart/form-data' ACTION='dbmodify.php' METHOD=POST>
			   <INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'><INPUT TYPE=HIDDEN NAME=parent VALUE=$parent>
               <INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
               <INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
			   <INPUT TYPE=HIDDEN NAME=MAX_FILE_SIZE VALUE='$default->max_filesize'>
			   <INPUT TYPE=HIDDEN NAME=action VALUE=file_upload>
			   <INPUT TYPE=HIDDEN NAME=expand VALUE=$expand>
			   <INPUT TYPE=HIDDEN NAME=type VALUE=$type>
			   <TABLE BORDER=$default->table_border><TR>");

		if ($type == "url") {
            print("<TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_sendthisurl:</TD><TD align=left><input name='userfile' type='text'size='80'></TD></TR>");
        } else {
			print("<TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_sendthisfile:</TD><TD align=left><input name='userfile' type='file'></TD></TR>");
        }

        print("<TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_title:</TD><TD align=left><INPUT TYPE=TEXT NAME=title></TD></TR>
			   <TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_keywords:</TD><TD align=left><INPUT TYPE=TEXT NAME=metadata></TD></TR>");

        print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownergroup:</TD><TD align=left><SELECT NAME=groupid>");
        if(isset($groupid)) {
            print("<OPTION VALUE=".$sql->f("groupid").">".group_to_name($sql->f("groupid")));
        }
        foreach($groups as $g) {
            print("<OPTION VALUE=$g[0]>$g[1]");
        }
        printfileperm("4", "security", $lang_permissions, "admin");

        print("<TR><TD ALIGN=RIGHT VALIGN=TOP bgcolor=$default->table_header_bg>");
        // Daphne Change - add wrap=hard to textarea for logs
        print("$lang_description:</TD><TD align=left><TEXTAREA NAME=description ROWS=10 COLS=50 WRAP=hard></TEXTAREA></TD></TR>
               </TABLE><INPUT TYPE=SUBMIT VALUE='$lang_sendfile'></FORM>");
		include("./lib/footer.inc");
	} else {
		print($lang_noupload);
	}
}

if ($action == "file_modify") {
	if(check_auth($id, "file_modify", $userid) == 1) {
		$expand = 1;

        /* BEGIN Bozz Change
           Retrieve Group information if the user is in the
           Administrator group   */
        if ( owlusergroup($userid) == 0 ) {
            $sql = new Owl_DB;
            $sql->query("select id,name from $default->owl_groups_table");
            $i=0;
            while($sql->next_record()) {
                $groups[$i][0] = $sql->f("id");
                $groups[$i][1] = $sql->f("name");
                $i++;
            }
            $sql->query("select id,name from $default->owl_users_table");
            $i=0;
            while($sql->next_record()) {
                $users[$i][0] = $sql->f("id");
                $users[$i][1] = $sql->f("name");
                $i++;
            }
         } else {
             if (uid_to_name($userid) == fid_to_creator($id)) {
                $sql = new Owl_DB;
                $sql->query("select userid,groupid from $default->owl_users_grpmem_table where userid = $userid ");
                if ($sql->num_rows($sql) == 0) {
                    $sql->query("SELECT u.groupid as groupid, g.name as name  from $default->owl_users_table as u join $default->owl_groups_table as g where u.id = $userid and u.groupid = g.id");
                }
                $i=0;
                while($sql->next_record()) {
                    $groups[$i][0] = $sql->f("groupid");
                    $groups[$i][1] = group_to_name($sql->f("groupid"));
                    $i++;
                }
                $mygroup = owlusergroup($userid);
                $sql->query("select id,name from $default->owl_users_table where groupid='$mygroup'");
                $i=0;
                while($sql->next_record()) {
                   $users[$i][0] = $sql->f("id");
                   $users[$i][1] = $sql->f("name");
                   $i++;
                }
            }
        }
        /* END Bozz Change */

		print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
		print("<TR><TD align=left>$lang_modifying".gen_navbar($parent)."/".flid_to_name($id)."</TD></TR>");
		print("</TABLE><HR WIDTH=$default->table_expand_width><BR>");
		$sql = new Owl_DB;
        $sql->query("select * from $default->owl_files_table where id = '$id'");
        
		while($sql->next_record()) {
            print("<TABLE WIDTH=66% BORDER=$default->table_border><FORM ACTION='dbmodify.php'><TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>
                   $lang_title:</TD><TD align=left><INPUT TYPE=TEXT NAME=title VALUE=\"".$sql->f("name")."\"></TD></TR>
			       <TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_file:</TD><TD align=left>".$sql->f("filename")."&nbsp;(".gen_filesize($sql->f("size")).")</TD></TR>");
            // Bozz Change Begin

			$security = $sql->f("security");
            $current_groupid = owlfilegroup($id);
            $current_owner = owlfilecreator($id);
            if ( owlusergroup($userid) == 0  || uid_to_name($userid) == fid_to_creator($id)) {
                print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownership:</TD><TD align=left><SELECT NAME=file_owner>");
                foreach($users as $g) {
                    print("<OPTION VALUE=$g[0] ");
                    if($g[0] == owlfilecreator($id)) {
                        print("SELECTED");
                    }
                    print(">$g[1]");
                }
                print("</SELECT></TD></TR>");

                print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownergroup:</TD><TD align=left><SELECT NAME=groupid>");
                //print("<OPTION VALUE=$groupid>".group_to_name($sql->f("groupid")));
                foreach($groups as $g) {
                    print("<OPTION VALUE=$g[0] ");
                    if($g[0] == $current_groupid) {
                        print("SELECTED");
                    }
                    print(">$g[1]");
                }
                print("</SELECT></TD></TR>");
                printfileperm($security, "security", "$lang_permissions:","admin");
            } else {
                print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownership:</TD><TD align=left>".fid_to_creator($id)."&nbsp;(".group_to_name(owlfilegroup($id)).")</TD></TR>");
                print("<INPUT TYPE=HIDDEN NAME=file_owner VALUE='$current_owner'>");
                print("<INPUT TYPE=HIDDEN NAME=security VALUE='$security'>");
                print("<INPUT TYPE=HIDDEN NAME=groupid VALUE='$current_groupid'>");
            }
            // Bozz change End
            
			//print("</SELECT></TD></TR>
			print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_keywords:</TD><TD align=left><INPUT TYPE=TEXT NAME=metadata VALUE='".$sql->f("metadata")."'></TD></TR>
			       <TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg VALIGN=TOP>
			       $lang_description:</TD><TD align=left><TEXTAREA NAME=description ROWS=10 COLS=50>".$sql->f("description")."</TEXTAREA>
			       <INPUT TYPE=HIDDEN NAME=action VALUE=file_modify>
                   <INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
                   <INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
			       <INPUT TYPE=HIDDEN NAME=id VALUE=$id>
			       <INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'>
			       <INPUT TYPE=HIDDEN NAME=parent VALUE=$parent></TD></TR></TABLE>
			       <INPUT TYPE=HIDDEN NAME=expand VALUE=$expand>
			       <BR><INPUT TYPE=SUBMIT VALUE=$lang_change><INPUT TYPE=RESET VALUE=$lang_reset></FORM>");
			include("./lib/footer.inc");
		}
	} else {
        print("<BR><BR>".$lang_nofilemod);
	}
}

if ($action == "folder_create") {
	if(check_auth($parent, "folder_modify", $userid) == 1) {
		$expand=1;
    
        /* BEGIN Bozz Change
           Retrieve Group information if the user is in the
           Administrator group   */
        $sql = new Owl_DB;
        if ( owlusergroup($userid) == 0 ) {
            $sql->query("SELECT id,name from $default->owl_groups_table");
        } else {
            $sql->query("SELECT * from $default->owl_users_grpmem_table join $default->owl_groups_table where id = groupid and userid = $userid");
            if ($sql->num_rows($sql) == 0) {
                $sql->query("SELECT u.groupid as id, g.name as name  from $default->owl_users_table as u join $default->owl_groups_table as g where u.id = $userid and u.groupid = g.id");
            }
        } 
        $i=0;
        while($sql->next_record()) {
            $groups[$i][0] = $sql->f("id");
            $groups[$i][1] = $sql->f("name");
            $i++;
        }
        /* END Bozz Change */
                
		print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border><TR><TD align=left>$lang_addingfolder ".gen_navbar($parent)."</TD></TR></TABLE><HR WIDTH=$default->table_expand_width><BR>
		       <TABLE WIDTH=50% BORDER=$default->table_border><TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_name:</TD><TD align=left><FORM ACTION='dbmodify.php'>
		       <INPUT TYPE=HIDDEN NAME=parent VALUE=$parent><INPUT TYPE=HIDDEN NAME=expand VALUE=$expand>
		       <INPUT TYPE=HIDDEN NAME=action VALUE=folder_create><INPUT TYPE=TEXT NAME=name></TD></TR>
		       <INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'>
               <INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
               <INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
		       <INPUT TYPE=HIDDEN NAME=expand VALUE='$expand'>");

        /* BEGIN Bozz Change
          Display Retrieved Group information if the user is in the
          Administrator group   */
        print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownergroup:</TD><TD align=left><SELECT NAME=groupid>");
        foreach($groups as $g) {
            print("<OPTION VALUE=$g[0]>$g[1]");
        }
        if ( owlusergroup($userid) == 0 ) {
            printgroupperm(54, "policy", $lang_policy, "admin");
        } else {
            printgroupperm(54, "policy", $lang_policy, "user");
        }
        /* END Bozz Change */
        
		print("</TABLE><INPUT TYPE=SUBMIT VALUE=$lang_create></FORM>");
		include("./lib/footer.inc");
	} else {
		print($lang_nosubfolder);
	}
}

if ($action == "folder_modify") {
	if(check_auth($id, "folder_property", $userid) == 1) {
		$expand=1;

        /* BEGIN Bozz Change
           Retrieve Group information if the user is in the
           Administrator group   */        
        if ( owlusergroup($userid) == 0 ) {
            $sql = new Owl_DB;
            $sql->query("select id,name from $default->owl_groups_table");
            $i=0;
            while($sql->next_record()) {
                $groups[$i][0] = $sql->f("id");
                $groups[$i][1] = $sql->f("name");
                $i++;
            }
        }
        /* END Bozz Change */

		print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
		print("<TR><TD align=left>$lang_modifying ".gen_navbar($id)."</TD></TR>");
		print("</TABLE><HR WIDTH=$default->table_expand_width><BR><TABLE WIDTH=50% BORDER=$default->table_border>");
		$sql = new Owl_DB; 
        $sql->query("select * from $default->owl_folders_table where id = '$id'");
        
        while($sql->next_record()) {
            $security = $sql->f("security");
			print("<FORM ACTION='dbmodify.php'><INPUT TYPE=HIDDEN NAME=action VALUE=folder_modify>");
			print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_name:</TD><TD align=left><INPUT TYPE=TEXT NAME=name VALUE='".$sql->f("name")."'></TD></TR>");
            //print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_policy:</TD><TD align=left>$security</TD></TR>");
            print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownership:</TD><TD align=left>".uid_to_name(owlfoldercreator($id))."&nbsp;(".group_to_name(owlfoldergroup($id)).")</TD></TR>
			       <INPUT TYPE=HIDDEN NAME=id VALUE=$id>
                   <INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
                   <INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
	               <INPUT TYPE=HIDDEN NAME=parent VALUE=$parent>
        	       <INPUT TYPE=HIDDEN NAME=expand VALUE=$expand>");

            /* BEGIN Bozz Change
               Display Retrieved Group information if the user is in the
               Administrator group   */
            if ( owlusergroup($userid) == 0 ) {
                print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownergroup:</TD><TD align=left><SELECT NAME=groupid>");
                print("<OPTION VALUE=".$sql->f("groupid").">".group_to_name($sql->f("groupid")));
                foreach($groups as $g) {
                    print("<OPTION VALUE=$g[0]>$g[1]");
                }
                printgroupperm($security, "policy", $lang_policy, "admin");
            } else {
                printgroupperm($security, "policy", $lang_policy, "user");
            }
            /* END Bozz Change */

	        print("</TABLE><INPUT TYPE=SUBMIT VALUE=$lang_change><INPUT TYPE=RESET VALUE=$lang_reset>
			       <INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'></FORM></TABLE>");
			include("./lib/footer.inc");
        } // end while	
    } else {
        print($lang_nofoldermod);
    }
}

if ($action == "file_email") {
    if(check_auth($id, "file_modify", $userid) == 1) {
        print("<BR>");
        $expand = 1;

		$sql = new Owl_DB;
      	$sql->query("select * from $default->owl_users_table where id = '$userid'");
		$sql->next_record();
        $default_reply_to = $sql->f("email");

        print("<FORM ACTION='./dbmodify.php' METHOD=POST>");
        print("<INPUT TYPE=HIDDEN NAME=id VALUE=".$sql->f("id").">");

        print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
        print("<TR><TD align=left>$lang_emailing ".gen_navbar($parent)."/".flid_to_name($id)."</TD></TR>");
        print("</TABLE><HR WIDTH=$default->table_expand_width><BR>");
        print("<FORM enctype='multipart/form-data' ACTION='dbmodify.php' METHOD=POST>
                <INPUT TYPE=HIDDEN NAME=order VALUE='$order'>
                <INPUT TYPE=HIDDEN NAME=sortname VALUE='$sortname'>
                <INPUT TYPE=HIDDEN NAME=sess VALUE='$sess'>
                <INPUT TYPE=HIDDEN NAME=parent VALUE=$parent>
                <INPUT TYPE=HIDDEN NAME=MAX_FILE_SIZE VALUE='$default->max_filesize'>
                <INPUT TYPE=HIDDEN NAME=action VALUE=file_email>
                <INPUT TYPE=HIDDEN NAME=type VALUE='$type'>
                <INPUT TYPE=HIDDEN NAME=id VALUE='$id'>");
                 
        print("<INPUT TYPE=HIDDEN NAME=expand VALUE='$expand'>");
        print("<TABLE BORDER=$default->table_border><TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_email_to</TD><TD align=left><INPUT TYPE=TEXT NAME=mailto></TD></TR>
               <TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_email_cc</TD><TD align=left><INPUT TYPE=TEXT NAME=ccto></TD></TR>
               <TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_email_reply_to</TD><TD align=left><INPUT TYPE=TEXT NAME=replyto VALUE='$default_reply_to'></TD></TR>
               <TR><TD ALIGN=RIGHT bgcolor=$default->table_header_bg>$lang_email_subject</TD><TD align=left><INPUT TYPE=TEXT NAME=subject size=80></TD></TR>");
        print("<tr><TD align=right bgcolor=$default->table_header_bg valign=top>$lang_email_body</td>
               <td align=left><textarea name=mailbody rows=20 cols=80 wrap=hard></textarea></tr>");
        print("</TABLE><INPUT TYPE=SUBMIT VALUE='$lang_sendfile'></FORM>");
        include("./lib/footer.inc");
    } else {
        print($lang_noemail);
    }
}

?>

<?php

/*
 * index.php
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

require("../config/owl.php");
require("../lib/owl.lib.php");
require("../config/html.php");


if($action == "backup") dobackup();

include("../lib/header.inc");
print("<CENTER>");

if($usergroupid != "0") die("$lang_err_unauthorized");

if(!isset($action)) $action = "users";

function printusers() {
	global $sess, $default, $lang_users;

	$sql = new Owl_DB;
	$sql_active_sess = new Owl_DB;

	$sql->query("select username,name,id,maxsessions from $default->owl_users_table order by name");
	

        /* print("<TABLE><TR><TD BGCOLOR=$default->table_header_bg>$lang_users</TD></TR>");


        print("<tr><td><form method=post action=index.php?sess=$sess&action=users>");
        print("<input type=hidden name=sess value=$sess>");
        print("<input type=hidden name=action value=users>");
        print("<tr><td><select name=owluser>");

        while($sql->next_record()) {
                $uid = $sql->f("id");
                $username = $sql->f("username");
                $name = $sql->f("name");
                $maxsess = $sql->f("maxsessions") + 1;
                $numrows = 0;

                $sql_active_sess->query("select * from $default->owl_sessions_table where uid = $uid");
                $sql_active_sess->next_record();
                $numrows = $sql_active_sess->num_rows($sql_active_sess);

        if ($name == "")
                print("<option value=".$uid.">".$username."</option>");
        else
		if ($uid == $owluser)
                print("<option value=".$uid." SELECTED>(".$numrows."/".$maxsess.")  ".$name."</option>");
		else
                print("<option value=".$uid." >(".$numrows."/".$maxsess.")  ".$name."</option>");
        }
        print("</select><input type=submit value=Go></td></tr></table>"); */


 	print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg>$lang_users</TD><TD BGCOLOR=$default->table_header_bg>&nbsp</TD></TR>");


	while($sql->next_record()) {
                $uid = $sql->f("id");
                $username = $sql->f("username");
                $name = $sql->f("name");
 		$maxsess = $sql->f("maxsessions") + 1;
                $numrows = 0;
                
                $sql_active_sess->query("select * from $default->owl_sessions_table where uid = $uid");
                $sql_active_sess->next_record();
                $numrows = $sql_active_sess->num_rows($sql_active_sess);

                if ($name == "")
			print("<TR><TD align=left><A HREF='index.php?sess=$sess&action=users&owluser=".$uid."'>".$username."</A></TD>");
                else
			print("<TR><TD align=left><A HREF='index.php?sess=$sess&action=users&owluser=".$uid."'>".$name."</A></TD>");
                print("<TD align='right'>(".$numrows."/".$maxsess.")</TD></TR>");
	}
	print("</TABLE>");  
}

function printgroups() {
	global $sess, $lang_groups, $default;
	$sql = new Owl_DB;
	$sql->query("select name,id from $default->owl_groups_table order by name");
	print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg>$lang_groups</TD></TR>");
	while($sql->next_record()) {
		print("<TR><TD align=left><A HREF='index.php?sess=$sess&action=groups&group=".$sql->f("id")."'>".$sql->f("name")."</A></TD></TR>");
	}
	print("</TABLE>");
}

function printuser($id) {
	global $sess,$change,$lang_saved,$lang_title,$lang_group,$lang_username,$lang_password,$lang_change,$lang_quota,$lang_groupmember,$lang_noprefaccess,$lang_disableuser, $lang_userlang, $lang_maxsessions, $lang_attach_file;
	global $lang_flush_sessions_alt, $lang_flushed, $lang_deleteuser, $lang_email, $lang_notification, $default, $flush;

	if($change == 1) print("$lang_saved<BR>");

	if ($flush == 1) {
	  flushsessions($id, $sess); 
	  print($lang_flushed);
	}

	$sql = new Owl_DB;
	$sql->query("select id,name from $default->owl_groups_table order by name");
	$i=0;
	while($sql->next_record()) {
		$groups[$i][0] = $sql->f("id");
		$groups[$i][1] = $sql->f("name");
		$i++;
	}
	$sql->query("select * from $default->owl_users_table where id = '$id'");
	while($sql->next_record()) {
		print("<FORM ACTION='admin_dbmodify.php' METHOD=POST>");
		print("<INPUT TYPE=HIDDEN NAME=id VALUE=".$sql->f("id").">");
		print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
		print("<INPUT TYPE=HIDDEN name=action VALUE=user>");
		print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_title</TD><TD align=left><INPUT TYPE=text NAME=name VALUE='".$sql->f("name")."'></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_group</TD><TD align=left><SELECT NAME=groupid>");
		print("<OPTION VALUE=".$sql->f("groupid").">".group_to_name($sql->f("groupid")));
		foreach($groups as $g) {
			print("<OPTION VALUE=$g[0]>$g[1]");
		}
		print("</SELECT></TD></TR>");
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
			print("<OPTION VALUE=$file>$file");
                }
                $dir->close();
		print("</SELECT></TD></TR>"); 
                // Bozz Change  begin
                //This is to allow a user to be part of more than one group
        
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_groupmember</TD><TD align=left>");
                $i=0;
                $sqlmemgroup = new Owl_DB;
                foreach($groups as $g) {
                        $is_set_gid = $g[0];
                        $sqlmemgroup->query("select userid from $default->owl_users_grpmem_table where userid = '$id' and groupid = '$is_set_gid'");
                        $sqlmemgroup->next_record();
                        if ($sqlmemgroup->num_rows($sqlmemgroup) > 0) {
                             print("<input type='checkbox' name='group$i' value=$g[0] checked>$g[1]<BR>");
                        }
                        else {
                             print("<input type='checkbox' name='group$i' value=$g[0]>$g[1]<BR>");
                        }
                        $i++;
                }
                // This hidden field is to store the nubmer of displayed groups for future use
                // when the records are saved to the db


                print("<INPUT TYPE=HIDDEN NAME=no_groups_displayed VALUE=$i>");
                // Bozz Change End

                print("<TR><TD BGCOLOR=$default->table_header_bg ALIGN=RIGHT>$lang_username</TD><TD align=left><INPUT TYPE=TEXT NAME=loginname VALUE='".$sql->f("username")."'></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_quota</TD><TD align=left>".$sql->f("quota_current")." / <INPUT TYPE=TEXT NAME=quota VALUE=".$sql->f("quota_max")."></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_maxsessions</TD><TD align=left>".($sql->f("maxsessions") + 1)." / <INPUT TYPE=TEXT NAME=maxsessions VALUE=".($sql->f("maxsessions") + 1).">
<a href=\"index.php?sess=$sess&action=user&owluser=$id&change=0&flush=1\"><IMG SRC='$default->owl_root_url/graphics/admin_flush.gif' BORDER=0 ALT='$lang_flush_sessions_alt' TITLE='$lang_flush_sessions_alt'></a></TD></TR>");
		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_password</TD><TD align=left><INPUT TYPE=PASSWORD NAME=password VALUE='".$sql->f("password")."'></TD></TR>");
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_email</TD><TD align=left><INPUT TYPE=TEXT NAME=email VALUE='".$sql->f("email")."'></TD></TR>");
                if ( $sql->f("notify") == 1)
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_notification</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=notify VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_notification</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=notify VALUE=1></TD></TR>");
                if ( $sql->f("attachfile") == 1)
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_attach_file</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=attachfile VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_attach_file</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=attachfile VALUE=1></TD></TR>");
		if ($id != 1) {
                if ( $sql->f("disabled") == 1)
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_disableuser</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=disabled VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_disableuser</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=disabled VALUE=1></TD></TR>");
                if ( $sql->f("noprefaccess") == 1)
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_noprefaccess</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=noprefaccess VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_noprefaccess</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=noprefaccess VALUE=1></TD></TR>");
		}
		print("</TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_change>");
		if ($sql->f("id") != 1) {
			print("<INPUT TYPE=SUBMIT NAME=action VALUE='$lang_deleteuser'>");
		}

		print("</FORM>");
	}
}

function flushsessions($id, $sess) {
  global $default;
  $sql= new Owl_DB;
  $sql->query("delete from $default->owl_sessions_table where uid='$id' AND sessid!='$sess'");
}


function printgroup($id) {
	global $sess,$change,$lang_title,$lang_change,$lang_deletegroup,$lang_saved,$default;
	if(isset($change)) print("$lang_saved<BR>");
	$sql = new Owl_DB;
	$sql->query("select id,name from $default->owl_groups_table where id = '$id'");
	while($sql->next_record()) {
		print("<FORM ACTION='admin_dbmodify.php' METHOD=POST>");
		print("<INPUT TYPE=HIDDEN NAME=id VALUE=".$sql->f("id").">");
		print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
		print("<INPUT TYPE=HIDDEN name=action VALUE=group>");
		print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg>$lang_title</TD><TD><INPUT TYPE=text NAME=name VALUE='".$sql->f("name")."'></TD></TR></TABLE>");
		print("<BR><INPUT TYPE=SUBMIT VALUE=$lang_change>");
		if($sql->f("id") != 0) print("<INPUT TYPE=SUBMIT NAME=action VALUE='$lang_deletegroup'>");
		print("</FORM>");
	}
}

function printnewgroup() {
	global $default, $sess,$lang_title,$lang_add;
	print("<FORM ACTION='admin_dbmodify.php' METHOD=post>");
	print("<INPUT TYPE=HIDDEN NAME=action VALUE=add>");
	print("<INPUT TYPE=HIDDEN NAME=type VALUE=group>");
	print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
	print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg>$lang_title</TD><TD><INPUT TYPE=TEXT NAME=name></TD></TR></TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_add></FORM>");
}

function printnewuser() {
	global $sess,$lang_title,$lang_username,$lang_group,$lang_password,$lang_add,$default, $lang_quota,$lang_groupmember;
        global $lang_email, $lang_notification, $lang_noprefaccess, $lang_disableuser, $lang_userlang, $lang_maxsessions, $lang_attach_file; 
	$sql = new Owl_DB;
	$sql->query("select id,name from $default->owl_groups_table order by name");
	$i=0;
	while($sql->next_record()) {
		$groups[$i][0] = $sql->f("id");
		$groups[$i][1] = $sql->f("name");
		$i++;
	}
	print("<FORM ACTION='admin_dbmodify.php' METHOD=post>");
	print("<INPUT TYPE=HIDDEN NAME=action VALUE=add>");
	print("<INPUT TYPE=HIDDEN NAME=type VALUE=user>");
	print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
	print("<TABLE BORDER=$default->table_border><TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_title</TD><TD align=left><INPUT TYPE=TEXT NAME=name></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_username</TD><TD align=left><INPUT TYPE=TEXT NAME=loginname></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_group</TD><TD align=left><SELECT NAME=groupid>");
	foreach($groups as $g) {
		print("<OPTION VALUE=$g[0]>$g[1]");
	}
        print("</SELECT></TD></TR>");
        //*******************************
        // Display the Language dropdown
        //*******************************
                print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_userlang</TD><TD align=left><SELECT NAME=newlanguage>");
                                                 $dir = dir($default->owl_LangDir);
                                                 $dir->rewind();
                        
                                                 while($file=$dir->read())
                                                        {
                                                                if ($file != "." and $file != "..")
                                                                        { 
                        							//janu's change BEGIN
                        							print("<OPTION VALUE=$file");
                        							if ($file == $default->owl_lang)
                                							print (" SELECTED");
                        							print(">$file");
                        							//janu's change END 
                                                                        }
                                                        }
                                         $dir->close();
                print("</SELECT></TD></TR>");

        // Bozz Change  begin
        //This is to allow a user to be part of more than one group
	
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_groupmember</TD><TD align=left>");
        $i=0;
	foreach($groups as $g) {
		print("<input type='checkbox' name='group$i' value=$g[0]>$g[1]<BR>");
                $i++;
	}
        // This hidden field is to store the nubmer of displayed groups for future use
        // when the records are saved to the db

	print("<INPUT TYPE=HIDDEN NAME=no_groups_displayed VALUE=$i>");
        // Bozz Change End 
	print("</TD></TR><TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_quota</TD><TD align=left><INPUT TYPE=TEXT NAME=quota VALUE=0></TD></TR>");
	print("</TD></TR><TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_maxsessions</TD><TD align=left><INPUT TYPE=TEXT NAME=maxsessions VALUE=1></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_password</TD><TD align=left><INPUT TYPE=PASSWORD NAME=password></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_email</TD><TD align=left><INPUT TYPE=TEXT NAME=email></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_attach_file</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=attachfile VALUE=1></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_disableuser</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=disabled VALUE=1></TD></TR>");
	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_noprefaccess</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=noprefaccess VALUE=1></TD></TR>");
	print("</TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_add></FORM>");
}

function printhtml() {
        global $default, $sess, $lang_add, $lang_change, $change, $lang_saved;
        global $lang_ht_tbl_border_sz, $lang_ht_tbl_hd_bg, $lang_ht_tbl_cell_bg_cl, $lang_ht_tbl_cell_bg_al, $lang_ht_tbl_bg_cl, $lang_ht_expand_width, $lang_ht_collapse_width, $lang_ht_bd_bg_cl, $lang_ht_bd_txt_cl, $lang_ht_bd_lnk_cl, $lang_ht_bd_vlnk_cl, $lang_ht_bd_width; 
	if(isset($change)) print("$lang_saved<BR>");
        print("<FORM ACTION='admin_dbmodify.php' METHOD=post>");
        print("<INPUT TYPE=HIDDEN NAME=action VALUE=edhtml>");
        print("<INPUT TYPE=HIDDEN NAME=type VALUE=html>");
        print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
        print("<TABLE BORDER=$default->table_border>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_tbl_border_sz</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=border VALUE='$default->table_border'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_tbl_hd_bg</TD>
	           <TD align=left><INPUT TYPE=TEXT NAME=header_bg VALUE=$default->table_header_bg></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_tbl_cell_bg_cl</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=cell_bg VALUE='$default->table_cell_bg'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_tbl_cell_bg_al</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=cell_bg_alt VALUE='$default->table_cell_bg_alt'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_expand_width</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=expand_width VALUE='$default->table_expand_width'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_collapse_width</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=collapse_width VALUE='$default->table_collapse_width'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>Main Header Background Color</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=main_header_bgcolor VALUE='$default->main_header_bgcolor'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_bd_bg_cl</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=body_bgcolor VALUE='$default->body_bgcolor'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_bd_txt_cl</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=body_textcolor VALUE='$default->body_textcolor'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_bd_lnk_cl</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=body_link VALUE='$default->body_link'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_ht_bd_vlnk_cl</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=body_vlink VALUE='$default->body_vlink'></TD></TR>");
        print("</TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_change></FORM>");
}

function printprefs() {
        global $default, $sess, $lang_add, $lang_change, $change, $lang_saved;
	global $lang_owl_title_email, $lang_owl_email_from, $lang_owl_email_fromname, $lang_owl_email_replyto , $lang_owl_email_server, $lang_owl_title_HD, $lang_owl_lookAtHD, $lang_owl_def_file_security, $lang_owl_def_file_group_owner, $lang_owl_def_file_owner, $lang_owl_def_file_title, $lang_owl_def_file_meta , $lang_owl_def_fold_sec, $lang_owl_def_fold_group_owner, $lang_owl_def_fold_owner, $lang_owl_title_other, $lang_owl_max_filesize, $lang_owl_owl_timeout, $lang_owl_owl_expand, $lang_owl_version_control, $lang_owl_restrict_view ;
        global $lang_owl_title_tools, $lang_owl_dbdump_path,$lang_owl_gzip_path, $lang_owl_tar_path; 

	if(isset($change)) print("$lang_saved<BR>");
        print("<FORM ACTION='admin_dbmodify.php' METHOD=post>");
        print("<INPUT TYPE=HIDDEN NAME=action VALUE=edprefs>");
        print("<INPUT TYPE=HIDDEN NAME=type VALUE=html>");
        print("<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess>");
        print("<TABLE BORDER=$default->table_border>");
        print("<TR><TD BGCOLOR=$default->main_header_bgcolor align=CENTER colspan=2>$lang_owl_title_email</TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_email_from</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=email_from VALUE='$default->owl_email_from' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_email_fromname</TD>
	           <TD align=left><INPUT TYPE=TEXT NAME=email_fromname VALUE='$default->owl_email_fromname' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_email_replyto</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=email_replyto VALUE='$default->owl_email_replyto' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_email_server</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=email_server VALUE='$default->owl_email_server' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->main_header_bgcolor align=CENTER colspan=2>$lang_owl_title_HD</TD></TR>");

	if ( $default->owl_LookAtHD == "false" ){
               	print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_lookAtHD</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=lookAtHD VALUE='false' checked></TD></TR>");
        	print("<INPUT TYPE=HIDDEN NAME=def_file_security VALUE='$default->owl_def_file_security'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_file_group_owner VALUE='$default->owl_def_file_group_owner'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_file_owner VALUE='$default->owl_def_file_owner'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_file_title VALUE='$default->owl_def_file_title'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_file_meta  VALUE='$default->owl_def_file_meta'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_fold_security VALUE='$default->owl_def_fold_security'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_fold_group_owner VALUE='$default->owl_def_fold_group_owner'>");
        	print("<INPUT TYPE=HIDDEN NAME=def_fold_owner VALUE='$default->owl_def_fold_owner'>");
	}
      	else {
      		print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_lookAtHD</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=lookAtHD VALUE='false'></TD></TR>");
             	printfileperm($default->owl_def_file_security, "def_file_security", $lang_owl_def_file_security, "user");



     	   $sql = new Owl_DB;
           $sql->query("select id,name from $default->owl_groups_table");
           $i=0;
           while($sql->next_record()) {
                   $groups[$i][0] = $sql->f("id");
                   $groups[$i][1] = $sql->f("name");
                   $i++;
           }
          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_owl_def_file_group_owner</TD><TD align=left><SELECT NAME=def_file_group_owner>");
	   foreach($groups as $g) {
                             print("<OPTION VALUE=$g[0] ");
                             if($g[0] == $default->owl_def_file_group_owner)
				print("SELECTED");
			     print(">$g[1]");
                          }
                          print("</SELECT></TD></TR>");
     	   $sql = new Owl_DB;
           $sql->query("select id,name from $default->owl_users_table");
           $i=0;
           while($sql->next_record()) {
                   $users[$i][0] = $sql->f("id");
                   $users[$i][1] = $sql->f("name");
                   $i++;
           }
          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_owl_def_file_owner</TD><TD align=left><SELECT NAME=def_file_owner>");
	   foreach($users as $g) {
                             print("<OPTION VALUE=$g[0] ");
                             if($g[0] == $default->owl_def_file_owner)
				print("SELECTED");
			     print(">$g[1]");
                          }
                          print("</SELECT></TD></TR>");


        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_def_file_title</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=def_file_title VALUE='$default->owl_def_file_title' size=40></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_def_file_meta</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=def_file_meta VALUE='$default->owl_def_file_meta' size=40></TD></TR>");

             	printgroupperm($default->owl_def_fold_security, "def_fold_security", $lang_owl_def_fold_sec, "user");

          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_owl_def_fold_group_owner</TD><TD align=left><SELECT NAME=def_fold_group_owner>");
           foreach($groups as $g) {
                             print("<OPTION VALUE=$g[0] ");
                             if($g[0] == $default->owl_def_fold_group_owner)
                                print("SELECTED");
                             print(">$g[1]");
                          }
                          print("</SELECT></TD></TR>");

          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_owl_def_fold_owner</TD><TD align=left><SELECT NAME=def_fold_owner>");
	   foreach($users as $g) {
                             print("<OPTION VALUE=$g[0] ");
                             if($g[0] == $default->owl_def_fold_owner)
				print("SELECTED");
			     print(">$g[1]");
                          }
                          print("</SELECT></TD></TR>");

        }
        print("<TR><TD BGCOLOR=$default->main_header_bgcolor align=CENTER colspan=2>$lang_owl_title_other</TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_max_filesize</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=max_filesize VALUE='$default->max_filesize'></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_owl_timeout</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=owl_timeout VALUE='$default->owl_timeout'></TD></TR>");

	if ( $default->expand == 1 )
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_owl_expand</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=owl_expand VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_owl_expand:</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=owl_expand VALUE=1></TD></TR>");

	if ( $default->owl_version_control == 1 )
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_version_control</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=version_control VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_version_control</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=version_control VALUE=1></TD></TR>");

	if ( $default->restrict_view == 1 )
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_restrict_view</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=restrict_view VALUE=1 checked></TD></TR>");
                else
                    print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_restrict_view</TD><TD align=left><INPUT TYPE=CHECKBOX NAME=restrict_view VALUE=1></TD></TR>");

        print("<TR><TD BGCOLOR=$default->main_header_bgcolor align=CENTER colspan=2>$lang_owl_title_tools</TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_dbdump_path</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=dbdump_path VALUE='$default->dbdump_path' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_gzip_path</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=gzip_path VALUE='$default->gzip_path' size=30></TD></TR>");
        print("<TR><TD BGCOLOR=$default->table_header_bg align=right>$lang_owl_tar_path</TD>
                   <TD align=left><INPUT TYPE=TEXT NAME=tar_path VALUE='$default->tar_path' size=30></TD></TR>");
        print("</TABLE><BR><INPUT TYPE=SUBMIT VALUE=$lang_change></FORM>");
}

function dobackup() {
  global $default;

  $command = $default->dbdump_path . " --opt --host=" . $default->owl_db_host . " --user=" . $default->owl_db_user . " --password=" . $default->owl_db_pass . " " . $default->owl_db_name . " | " . $default->gzip_path . " -fc";
  $date = date("Ymd.Hms");

  header("Content-Disposition: attachment; filename=\"" . $default->owl_db_name . "-$date.sql.gz\"");
  header("Content-Location: " . $default->owl_db_name . "-$date.sql.gz");
  header("Content-Type: application/octet-stream");
  //header("Content-Length: $fsize");
  //header("Pragma: no-cache");
  header("Expires: 0");
  passthru($command);
  exit();

}



if($action) {
	print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
	print("<TR><TD WIDTH=200 VALIGN=TOP>");
        print("<TR>");
        print("<TD ALIGN=LEFT WIDTH=33%>");
        print uid_to_name($userid);
        print(" : <A HREF='../index.php?login=logout&sess=$sess'>$lang_logout</A></TD>");
        print("<TD ALIGN=CENTER WIDTH=33%> $lang_owl_admin</TD>");
        print("<TD ALIGN=RIGHT WIDTH=33%> <A HREF='../browse.php?sess=$sess'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A> </TD>");
        print("</TR>");
        print("</TABLE>");
	print("<HR WIDTH=$default->table_expand_width><BR>");
	print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border><TR><TD WIDTH=200 VALIGN=TOP>");
	print("<TABLE  BORDER=$default->table_border><TR><TD align=left>");
	print("<A HREF='index.php?sess=$sess&action=newuser'><IMG SRC='$default->owl_root_url/graphics/admin_users.gif' BORDER=0 ALT='$lang_newuser_alt' TITLE='$lang_newuser_alt'></A><BR>");
	print("<A HREF='index.php?sess=$sess&action=newgroup'><IMG SRC='$default->owl_root_url/graphics/admin_groups.gif' BORDER=0 ALT='$lang_newgroup_alt' TITLE='$lang_newgroup_alt'></A><BR>");
	print("<A HREF='index.php?sess=$sess&action=edhtml'><IMG SRC='$default->owl_root_url/graphics/admin_html_prefs.gif' BORDER=0 ALT='$lang_edthtml_alt' TITLE='$lang_edthtml_alt'></A><BR>");
	print("<A HREF='index.php?sess=$sess&action=edprefs'><IMG SRC='$default->owl_root_url/graphics/admin_site_prefs.gif' BORDER=0 ALT='$lang_edprefs_alt' TITLE='$lang_edprefs_alt'></A><BR>");
        if (file_exists($default->dbdump_path) && file_exists($default->gzip_path)) {
		print("<A HREF='index.php?sess=$sess&action=backup'><IMG SRC='$default->owl_root_url/graphics/admin_backup.gif' BORDER=0 ALT='$lang_backup_alt' TITLE='$lang_backup_alt'></A><BR><BR>");
        }
        else {
		print("<IMG SRC='$default->owl_root_url/graphics/admin_backup_disabled.gif' BORDER=0 ALT='$lang_backup_dis_alt' TITLE='$lang_backup_dis_alt'></A><BR><BR>");
	}
//	print("<A HREF='upgrade-users.php?sess=$sess'>$lang_upg_MD5</A><BR><BR>");
	printusers();
	print("<BR><BR>");
	printgroups();
	print("</TD></TR></TABLE>");
	print("</TD><TD VALIGN=TOP>");
	if(isset($owluser)) printuser($owluser);
	if(isset($group)) printgroup($group);
	if($action == "newgroup") printnewgroup();
	if($action == "newuser") printnewuser();
	if($action == "edhtml") printhtml();
	if($action == "edprefs") printprefs();
	print("</TD></TR></TABLE>");
} else {
	exit("$lang_err_general");
}

print("<BR><HR WIDTH=$default->table_expand_width><BR><A HREF='../browse.php?sess=$sess'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>");
?>
<!-- BEGIN BUG FIX: #448241 HTML-Syntax-Error in admin/index.php  -->
</BODY> 
</HTML> 
<!-- BEGIN BUG FIX: #448241 HTML-Syntax-Error in admin/index.php  -->

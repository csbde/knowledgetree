<?php

/*
 * admin_dbmodify.php
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
require("../config/owl.php");
require("../lib/owl.lib.php");
require("../config/html.php");

if(owlusergroup($userid) != 0) exit("$lang_err_unauth_area");


if($action == "user") {
        $maxsessions = $maxsessions - 1; // always is stored - 1
	$sql = new Owl_DB;
	$sql->query("SELECT * FROM $default->owl_users_table WHERE id = '$id'");
	$sql->next_record();
	$newpass = $sql->f("password");
	if ($newpass == $password) {
		$sql->query("UPDATE $default->owl_users_table SET groupid='$groupid',username='$loginname',name='$name',password='$password',quota_max='$quota', email='$email',notify='$notify',email='$email',attachfile='$attachfile',disabled='$disabled',noprefaccess='$noprefaccess',language='$newlanguage',maxsessions='$maxsessions' where id = '$id'");
	} 
        else 
        {
		$sql->query("UPDATE $default->owl_users_table SET groupid='$groupid',username='$loginname',name='$name',password='" . md5($password) ."',quota_max='$quota', email='$email', notify='$notify',attachfile='$attachfile',disabled='$disabled',noprefaccess='$noprefaccess',language='$newlanguage',maxsessions='$maxsessions' where id = '$id'");
	}
        // Bozz Change BEGIN 
        
        // Clean Up the member group table first

	$sql->query("DELETE FROM $default->owl_users_grpmem_table WHERE userid = $id");

        // Insert the new Choices the member group table with selected groups
        for ( $i = 0 ; $i <= $no_groups_displayed; $i++ ) {
             $checkboxfields = 'group' . $i;
             if($$checkboxfields != '') {
                  $checkboxvalue = $$checkboxfields;
                  $sql->query("INSERT INTO $default->owl_users_grpmem_table (userid,groupid) VALUES ('$id', '$checkboxvalue')");
             } 
        }
        /* Bozz Change END */
	header("Location: index.php?sess=$sess&action=users&owluser=$id&change=1");
}

if($action == "group") {
	global $default;
	$sql = new Owl_DB;
	$sql->query("UPDATE $default->owl_groups_table SET name='$name' where id = '$id'");
	header("Location: index.php?sess=$sess&action=groups&group=$id&change=1");
}

// BEGIN BUG FIX: #448232 mistake in admin_dbmodify.php
if($action == $lang_deleteuser) {
// END BUG FIX: #448232 mistake in admin_dbmodify.php
	$sql = new Owl_DB;
	$sql->query("DELETE FROM $default->owl_users_table WHERE id = '$id'");
        // Bozz Change Begin
        // Also Clean up the groupmember table when a user is deleted
	$sql->query("DELETE FROM $default->owl_users_grpmem_table WHERE userid = $id");
        // Bozz Change End
	header("Location: index.php?sess=$sess&action=users");
}

if($action == "edhtml") {
  $sql = new Owl_DB;
  $sql->query("UPDATE $default->owl_html_table SET table_border='$border', table_header_bg='$header_bg', table_cell_bg='$cell_bg',table_cell_bg_alt='$cell_bg_alt',body_bgcolor='$body_bgcolor',body_textcolor='$body_textcolor',body_link='$body_link',body_vlink='$body_vlink',table_expand_width='$expand_width',table_collapse_width='$collapse_width', main_header_bgcolor='$main_header_bgcolor' ");    

  header("Location: index.php?sess=$sess&action=edhtml&change=1");

}

if($action == "edprefs") {
  $sql = new Owl_DB;

  if ($lookAtHD != "false" )
  	$lookAtHD = "true";
  if ($owl_expand != "1")
	$owl_expand = "0";
  if ($version_control != "1")
	$version_control = "0";

  $sql->query("UPDATE $default->owl_prefs_table SET  email_from='$email_from', email_fromname='$email_fromname', email_replyto='$email_replyto', email_server='$email_server', lookAtHD='$lookAtHD', def_file_security='$def_file_security', def_file_group_owner='$def_file_group_owner', def_file_owner='$def_file_owner', def_file_title='$def_file_title', def_file_meta='$def_file_meta', def_fold_security='$def_fold_security', def_fold_group_owner='$def_fold_group_owner', def_fold_owner='$def_fold_owner', max_filesize='$max_filesize', timeout='$owl_timeout', expand='$owl_expand', version_control='$version_control', restrict_view='$restrict_view', dbdump_path='$dbdump_path', gzip_path='$gzip_path', tar_path='$tar_path'");    

  header("Location: index.php?sess=$sess&action=edprefs&change=1");
}

// BEGIN BUG FIX: #448232 mistake in admin_dbmodify.php
if($action == $lang_deletegroup ) {
// END BUG FIX: #448232 mistake in admin_dbmodify.php
	global $default;
	$sql = new Owl_DB;
	$sql->query("DELETE FROM $default->owl_groups_table WHERE id = '$id'");
	header("Location: index.php?sess=$sess&action=groups");
}

if($action == "add") {
	if($type == "user") {
                $maxsessions = $maxsessions - 1; // always is stored - 1
		$sql = new Owl_DB;
		$sql->query("SELECT * FROM $default->owl_users_table WHERE username = '$loginname'");
		if($sql->num_rows($sql) > 0) die ("$lang_err_user_exists");
		$sql->query("INSERT INTO $default->owl_users_table (groupid,username,name,password,quota_max,quota_current,email,notify,attachfile,disabled,noprefaccess,language,maxsessions) VALUES ('$groupid', '$loginname', '$name', '" . md5($password) . "', '$quota', '0', '$email', '$notify','$attachfile', '$disabled', '$noprefaccess', '$newlanguage', '$maxsessions')");
                // Bozz Change BEGIN 
                // Populated the member group table with selected groups
		$sql->query("SELECT id FROM $default->owl_users_table WHERE username = '$loginname'");
                $sql->next_record();
                $newuid = $sql->f("id");
                for ( $i = 0 ; $i <= $no_groups_displayed; $i++ ) {
                                $checkboxfields = 'group' . $i;
                                if($$checkboxfields != '') {
                                     $checkboxvalue = $$checkboxfields;
                                     $sql->query("INSERT INTO $default->owl_users_grpmem_table (userid,groupid) VALUES ('$newuid', '$checkboxvalue')");
                                } 
                }
                /* Bozz Change END */
		if($home == "1") {
			$sql->query("select * from $default->owl_users_table where username = '$loginname'");
			while($sql->next_record()) $id = $sql->f("id");
			$sql->query("insert into $default->owl_folders_table values (0, '$loginname', '2', '54', '$groupid', '$id')");
			mkdir($default->owl_fs_root."/".fid_to_name("1")."/Home/$loginname", 0777);
		}
		header("Location: index.php?sess=$sess");
	} elseif($type == "group") {
		$sql = new Owl_DB;
		$sql->query("INSERT INTO $default->owl_groups_table (name) VALUES ('$name')");
		header("Location: index.php?sess=$sess");
	}
}

?>

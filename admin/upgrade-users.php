<?php

/**
 * upgrade-users.php
 *
 * This is used to upgrade a user's password
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
 */
 
require("../config/owl.php");
require("../lib/owl.lib.php");
require("../config/html.php");

// this page is used to upgrade a user's password
print("<CENTER>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("<BR>DO NOT RUN THIS AGAIN</CENTER><BR><BR>");
print("Running through $default->owl_users_table<BR>");
$sql = new Owl_DB;
$sql->query("select * from $default->owl_users_table");
$sqlupd = new Owl_DB;
while($sql->next_record()) 
{
	$userid = $sql->f("id");
	$password = $sql->f("password");
        if (strlen($password) <> 32)
         {
		$sqlupd->query("update $default->owl_users_table set password='" . md5($password) . "' where id = '$userid'");
		print "Updated user id  $userid: ".$sql->f("username")."<BR>";
        } 
        else
         {
		print "ALREADY UPGRADED -> $userid: ".$sql->f("username")."<BR>";
        }
}
print("DONE<BR><BR><CENTER>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("********************** WARNING WARNING WARNING ****************************<BR>");
print("<BR>DO NOT RUN THIS AGAIN</CENTER><BR>");

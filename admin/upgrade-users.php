<?php

/*

  File: upgrade-users.php
  Author: Chris
  Date: 2001/01/24

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

require("../config/owl.php");
require("../lib/owl.lib.php");
require("../config/html.php");

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
while($sql->next_record()) {
	$userid = $sql->f("id");
	$password = $sql->f("password");
        if (strlen($password) <> 32) {
		$sqlupd->query("update $default->owl_users_table set password='" . md5($password) . "' where id = '$userid'");
		print "Updated user id  $userid: ".$sql->f("username")."<BR>";
        } else {
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

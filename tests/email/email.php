<?php
/**
* Unit tests for static function in Email class in /lib/email/Email.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*
*/

require_once("../../config/dmsDefaults.php");

if (checkSession) {
	require_once("$default->owl_fs_root/lib/email/Email.inc");
	//require_once("$default->owl_fs_root/phpmailer/class.smtp.php");
	require_once("$default->owl_fs_root/phpmailer/class.phpmailer.php");	
	Email::sendHyperLink("ktdev@jamwarehouse.com", "KTDEV", "rob@jamwarehouse.com", "testing email", "<b>We're testing the email</b><br>", "http://www.google.com");
}

?>

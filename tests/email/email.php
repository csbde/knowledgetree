<?php

require_once("../../config/dmsDefaults.php");

/**
* Unit tests for static function in Email class in /lib/email/Email.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package tests.email
*/


if (checkSession) {
	require_once("$default->fileSystemRoot/lib/email/Email.inc");
	//require_once("$default->fileSystemRoot/phpmailer/class.smtp.php");
	require_once("$default->fileSystemRoot/phpmailer/class.phpmailer.php");	
	Email::sendHyperLink("ktdev@jamwarehouse.com", "KTDEV", "rob@jamwarehouse.com", "testing email", "<b>We're testing the email</b><br>", "http://www.google.com");
}

?>

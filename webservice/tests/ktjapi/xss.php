<?php
	include_once('../../../config/dmsDefaults.php');
	error_reporting(E_ERROR);
	session_start();
	session_id($_GET['session_id']);
	echo file_get_contents($_GET['url']);
?>
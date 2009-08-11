<?php
	require_once('../../ktapi/ktapi.inc.php');

	if($_GET['user']){
		$kt=new KTAPI();
		$user=$kt->get_user_object_by_username($_GET['user']);
		echo '<pre>'.print_r($user,true).'</pre>';
		echo '<hr /><pre>'.print_r(get_class_methods(get_class($user)),true).'</pre>';
		//echo '<hr /><pre>'.$user->getPassword().'</pre>';
	}
?>
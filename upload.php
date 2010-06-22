<?php
	print_r($_FILES);
	
	$num_files = count($_FILES['user_file']['name']);
	
	//echo($num_files);
	
	require_once('ktapi/ktapi.inc.php');
	require_once('ktwebservice/KTUploadManager.inc.php');
	
	//$session_id = $_POST['session_id'];
	
	echo($session_id);

	$ktapi = new KTAPI();
	$ktapi->add_document();
	//$session = $ktapi->get_active_session($session_id, null, 'ws');
	
	print_r($_POST);

?>
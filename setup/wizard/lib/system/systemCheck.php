<?php
	include("../../path.php");

	if(is_writable(SYS_VAR_DIR.DS."log")) {
		echo 'var/log is writable<br>';
	}
	if(is_writable(SYS_VAR_DIR.DS."bin")) {
		echo 'var/bin is writable<br>';
	}
	if(is_writable(SYS_VAR_DIR.DS."tmp")) {
		echo 'var/tmp is writable<br>';
	}
	if(is_writable(SYS_VAR_DIR.DS."Documents")) {
		echo 'var/Documents is writable<br>';
	}
	
?>
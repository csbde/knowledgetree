<?php

class InstallUtil {
	
	public function __construct() {
		
	}
	
	public function isSystemInstalled() {
		if (file_exists(dirname(__FILE__)."/install")) {
			return false;
		}

		return true;
	}
}
?>
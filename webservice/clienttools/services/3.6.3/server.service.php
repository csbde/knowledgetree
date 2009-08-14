<?php
require_once(str_replace('/','\\',str_replace('//','/',dirname(__FILE__).'/')."../3.6.2/server.service.php"));

class clientTools_service_server_363 extends clientTools_service_server_362{
	public function phpinfo(){
		parent::phpinfo();
		$this->response['GLOBALS']=$GLOBALS;
	}
}
?>
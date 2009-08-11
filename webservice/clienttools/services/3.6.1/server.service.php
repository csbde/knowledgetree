<?php
require_once(str_replace('/','\\',str_replace('//','/',dirname(__FILE__).'/')."../../clientTools_service.php"));

class clientTools_service_server_361 extends clientTools_service{
		public function status(){

		}

		public function getToken(){

		}

		public function phpInfo(){
			ob_start();
			phpinfo();
			$this->response['phpinfo']=ob_get_clean();
		}

		public function getServiceObject(){
			$this->response['serviceobject']=new clientTools_service_server_361(&$this->kt,&$this->session,&$this->session_id);
		}
	}
?>
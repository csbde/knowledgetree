<?php
class server extends client_service {
		public function status(){
			$this->setResponse(array('online'=>true));
		}
		
		public function ping(){
			$this->addResponse('pong',time());
		}

		public function getToken(){

		}

		public function phpInfo(){
			ob_start();
			phpinfo();
			$this->addResponse('phpinfo',ob_get_clean());
		}

	}
?>
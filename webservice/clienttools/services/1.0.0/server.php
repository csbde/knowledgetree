<?php
class server extends client_service {
		public function status(){
			$this->logTrace((__METHOD__.'('.__FILE__.' '.__LINE__.')'),'Enter Function');
			$this->setResponse(array('online'=>true));
		}
		
		public function ping(){
			$this->logTrace((__METHOD__.'('.__FILE__.' '.__LINE__.')'),'Enter Function');
			$this->addResponse('pong',time());
		}

		public function getToken(){
			$this->logTrace((__METHOD__.'('.__FILE__.' '.__LINE__.')'),'Enter Function');

		}

		public function phpInfo(){
			$this->logTrace((__METHOD__.'('.__FILE__.' '.__LINE__.')'),'Enter Function');
			ob_start();
			phpinfo();
			$this->addResponse('phpinfo',ob_get_clean());
		}

	}
?>
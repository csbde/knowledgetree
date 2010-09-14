<?php
define('DS',DIRECTORY_SEPARATOR);
define('F_UP','..'.DS);
define('FRAG_ROOT',realpath(dirname(__FILE__).DS.F_UP.F_UP.F_UP.F_UP.'templates'.DS.'fragments'.DS));


class template extends client_service{
	
	public function getFragment($params){
		$fragment=FRAG_ROOT.DS.($params['name']).".html";
		$content='';
		if(is_file($fragment)){
			if(is_readable($fragment)){
				$content=file_get_contents($fragment);
			}else{
				$this->addError('Fragment could not be read.');
			}
		}else{
			$this->addError('Fragment could not be found.');
		}
		$this->addResponse('Template Path',$fragment);
		$this->addResponse('fragment',$content);
	}

	public function parseFragment($params){
		$data=$params['data'];
		$this->getFragment($params);
		$content='';
		if(!$this->hasErrors){
			if(is_array($data)){
				$content=$this->parseString($this->getResponse('fragment'),$data);
			}else{
				$this->addError('Data object unreadable as array.');
			}
		}
		$this->addResponse('parsed',$content);
	}


	public function execFragment($params){
		$data=isset($params['data']) ? $params['data'] : array();
		$data=is_array($data) ? $data : array();
		$content='';
		$fragment=FRAG_ROOT.DS.($params['name']).".php";
		
		if(is_file($fragment)){
			if(is_readable($fragment)){
				$content=$this->_execFragment($fragment,$data);
			}else{
				$this->addError('Fragment could not be read.');
			}
		}else{
			$this->addError('Fragment could not be found.');
		}
		
		
		$this->addResponse('Template Path',$fragment);
		$this->addResponse('fragment',$content);
	}
	
	private function _execFragment($file=NULL,$data=NULL){
		if(is_array($data)){
			extract($data,EXTR_OVERWRITE);
		}
		ob_start();
		try{
			include($file);
		}catch(Exception $e){
			$this->addError($e->getMessage());
		}
		$result=ob_get_clean();
		return $result;
	}
}
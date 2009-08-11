<?php

class clientTools_service{
	//Service error codes
	const ERR_SERVICE_RESOURCE_NOT_FOUND=200;

	protected $response;
	protected $errors;
	protected $debug;
	protected $dispatcher;
	public $kt=null;
	public $session=null;
	public $session_id=null;

	public function __construct(&$kt=null,&$session=null,$session_id=null,&$dispatcher=null){
		$this->kt=&$kt;
		$this->session=&$session;
		$this->session_id=$session_id;
		$this->dispatcher=&$dispatcher;
	}

	protected function setResponse($name=NULL,$obj=NULL){
		if(!is_array($this->response))$this->response=array();
		$this->response[$name]=$obj;
	}

	protected function setError($code=0,$message=''){
		$this->errors[]=array('code'=>$code,'message'=>$message);
	}

	protected function setDebug($title='',$obj=NULL){
		$this->debug[$title]=$obj;
	}

	public function getResponse(){
		if(!is_array($this->response))$this->response=array();
		return $this->response;
	}

	public function getDebug(){
		if(!is_array($this->debug))$this->debug=array();
		return $this->debug;
	}

	public function getErrors(){
		if(!is_array($this->errors))$this->errors=array();
		return $this->errors;
	}
}
?>
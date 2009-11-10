<?php

class jsonContentException extends Exception{
	const INPUT_ERROR=100001;
}

class jsonResponseObject{
	protected $title='';
	protected $errors=array();
	protected $status=array('session_id'=>'','random_token'=>'');
	protected $data=array();
	protected $log=array();
	protected $request=array();
	protected $debug=array();
	public $additional=array();
	public $isDataSource=false;
	
	public $includeDebug=true;
	
	public $response=array(
		'requestName'		=>'',
		'errors'			=>array(
			'hadErrors'			=>0 ,
			'errors'			=>array()
		),
		'status'			=>array(
			'session_id'		=>'',
			'random_token'		=>''
		),
		'data'				=>array(),
		'request'			=>array(),
		'debug'				=>array(),
		'log'				=>array()
	);	
	
	
	public function addError($message=NULL,$code=NULL){
		$this->errors[]=array('code'=>$code,'message'=>$message);
	}
	
	public function setStatus($varName=NULL,$value=NULL){
		$this->status[$varName]=$value;
	}
	
	public function setData($varName=NULL,$value=NULL){
		$this->data[$varName]=$value;
	}
	
	public function overwriteData($value=NULL){
		$this->data=$value;
	}
	
	public function setDebug($varName=NULL,$value=NULL){
		if(is_array($this->debug[$varName]) && is_array($value))$value=array_merge($this->debug[$varName],$value);
		$this->debug[$varName]=$value;
	}
	
	public function addDebug($varName=NULL,$value=NULL){$this->setDebug($varName,$value);}
	
	public function setRequest($request=NULL){
		$this->request=$request;
	}
	
	public function setTitle($title=NULL){
		$title=(string)$title;
		$this->title=$title;
	}
	
	public function log($str){
		$this->log[]='['.date('h:i:s').'] '.$str;
	}
	
	public function getJson(){
		$response=array_merge(array(
			'requestName'		=>$this->title,
			'errors'	=>array(
				'hadErrors'		=>(count($this->errors)>0?1:0),
				'errors'		=>$this->errors
			),
			'status'	=>$this->status,
			'data'		=>$this->data,
			'request'	=>$this->request,
			'debug'		=>$this->debug,
			'log'		=>$this->log
		),$this->additional);
		if(!$this->includeDebug) unset($response['debug']);
		
		if($this->isDataSource){
			$response=json_encode($response['data']);
		}else{
			$response=json_encode($response);
		}
		return $response;
	}
}



class jsonWrapper{
	public $raw='';
	public $jsonArray=array();
	
	public function __construct($content=NULL){
		$this->raw=$content;
		$content=@json_decode($content,true);
		if(!is_array($content))throw new jsonContentException('Invalid JSON input',jsonContentException::INPUT_ERROR);
		$this->jsonArray=$content;
	}
	
	public function getVersion(){
		$ver=$this->jsonArray['auth']['version'];
		$ver="{$ver['major']}.{$ver['minor']}";
		return $ver;
	}
}

?>
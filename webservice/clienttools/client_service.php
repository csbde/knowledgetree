<?php

class client_service{
	public $Response;
	public $KT;
	public $Request;
	public $AuthInfo;
	public $handler;
	
	public function __construct(&$handler,&$ResponseObject,&$KT_Instance,&$Request,&$AuthInfo){
		// set the response object
//		if(get_class($ResponseObject)=='jsonResponseObject'){
//			$this->Response=&$ResponseObject;
//		}else{
//			$this->Response=new jsonResponseObject();
//		}

		$this->handler=$handler;
		$this->Response=&$ResponseObject;
		$this->KT=&$KT_Instance;
		$this->AuthInfo=&$AuthInfo;
		$this->Request=&$Request;
		
		$this->Response->location='client service';
	}
	
	protected function addResponse($name,$value){
		$this->Response->setData($name,$value);
	}	
	
	protected function addDebug($name,$value){
		$this->Response->setDebug($name,$value);
	}

	protected function setResponse($value){
		$this->Response->overwriteData($value);
	}

	protected function addError($message,$code){
		$this->Response->addError($message,$code);
	}
	
	protected function xlate($var=NULL){
		return $var;
	}
	
	protected function logTrace($location=NULL,$message=NULL){
		Clienttools_Syslog::logTrace($this->AuthInfo['user'],'SERVICE - '.$location,$message);
	}
	
	protected function logError($location=NULL,$detail=NULL,$err=NULL){
		Clienttools_Syslog::logError($this->AuthInfo['user'],'SERVICE - '.$location,$detail,$err);
	}
	
	protected function logInfo($location=NULL,$message=NULL,$debugData=NULL){
		Clienttools_Syslog::logInfo($this->AuthInfo['user'],'SERVICE - '.$location,$message,$debugData);
	}
	
	protected function checkPearError($obj,$errMsg,$debug=NULL,$response=NULL){
		if (PEAR::isError($obj)){
			if($response===NULL)$response=array('status_code' => 1);
			$this->addError($errMsg);
			if((isset($debug) || $debug==NULL) && $debug!=='')$this->addDebug('',$debug!==NULL?$debug:$obj);
    		$this->setResponse($response);
    		return false;
    	}
    	return true;	
	}
	
}

?>
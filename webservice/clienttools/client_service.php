<?php

class client_service{
	public $Response;
	public $KT;
	public $Request;
	public $AuthInfo;
	public $handler;
	
	public function __construct(&$handler,&$ResponseObject,&$KT_Instance,&$Request,&$AuthInfo){
		// set the response object
		if(get_class($ResponseObject)=='jsonResponseObject'){
			$this->Response=&$ResponseObject;
		}else{
			$this->Response=new jsonResponseObject();
		}

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
	
	protected function getResponse($name=NULL){
		return $this->Response->getData($name);
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
	
	protected function hasErrors(){
		return $this->Response->hasErrors();
	}
	
	protected function log($message=NULL){
		$this->Response->log($message);
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
	
	/**
	 * Forces parameter to boolean.
	 * $isTrue array contains a list of values that are recognized as 'true' values in boolean
	 */
	protected function bool($var=NULL){
		$ret=false;
		$isTrue=Array('true','0','yes');
		if(is_bool($var))$ret=$var;
		$var=strtolower(trim(($var.'')));
		$ret=(in_array($var,$isTrue));
		return $ret;
	}
	
	protected function filter_array($arr=array(),$filter=NULL,$strict=true){
		$new=array();
		if(!is_array($filter)){
			$filter=(string)$filter;
			$filter=trim($filter);
			$filter=explode(",",$filter);
			if(count($filter)>0)if($filter[0]=='')$filter=array();
		}
		if(is_array($arr)){
			if(count($filter)>0){
				$keys=array_keys($arr);
				if($strict){
					$req=$filter;
				}else{
					$req=array_intersect($filter,$keys);
				}
				foreach($req as $key){
					$new[$key]=isset($arr[$key]) ? $arr[$key] : NULL;
				}
			}else{
				return $arr;
			}
		}else{
			return $arr;
		}
		return $new;
	}
	
	protected function ext_explode($delimiter=NULL,$str=NULL){
//			$this->log("Delimiter: ",$delimiter);
		if($str && $delimiter){
			$proc=explode($delimiter,$str);
			$new=array();
			foreach($proc as $key=>$val){
				if($val)$new[$key]=$val;
			}
			return $new;
		}else return array();
	}

	public static function parseString($string='',$xform=array()){
		if(!is_array($xform))$xform=array();
		
		$from=array_keys($xform);
		$to=array_values($xform);
		
		$delim=create_function('&$item,$key,$prefix','$item="[".$item."]";');
		array_walk($from,$delim);
		
		return str_replace($from,$to,$string);
	}	
		
}

?>
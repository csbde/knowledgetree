<?php
class webAjaxHandler{
	protected $rawRequestObject=NULL;
	protected $digestToken=NULL;
	protected $remoteIp=NULL;
	
	public $ret=NULL;
	public $req=NULL;
	public $version=NULL;
	public $auth=NULL;
	public $request=NULL;
	public $kt=NULL;
	public $authenticator=NULL;
	public $noAuthRequireList=array();
	public $standardServices=array('system');
	public $parameters=array();
	protected $errors=array();

	public function __construct(&$response=NULL,&$kt){
		
		//========================= Preparations
		// set the response object
		if(get_class($response)=='jsonResponseObject'){
			$this->ret=&$response;
		}else{
			$this->ret=new jsonResponseObject();
		}
		$this->ret->location='webajaxhandler';

		$this->remoteIp = (getenv(HTTP_X_FORWARDED_FOR)) ?  getenv(HTTP_X_FORWARDED_FOR)  :  getenv(REMOTE_ADDR);

		$this->rawRequestObject=isset($_GET['request'])?$_GET['request']:(isset($_POST['request'])?$_POST['request']:'');
		
		$this->ret->addDebug("php version",PHP_VERSION);
		
		
		
		//========================= 1. Parse Json
		$this->req=new jsonWrapper($this->rawRequestObject);
		$this->auth=$this->structArray('session,token',$this->req->jsonArray['auth']);
		$this->request=$this->structArray('service,function,parameters',$this->req->jsonArray['request']);
		

		$this->ret->addDebug('Raw Request',$this->rawRequestObject);

		//Add additional parameters
		$add_params=array_merge($_GET,$_POST);
		unset($add_params['request'],$add_params['datasource']);
		$this->request['parameters']=array_merge($this->request['parameters'],$add_params);
		$this->parameters=$this->request['parameters'];
		
		if(!$this->auth['debug'])$this->ret->includeDebug=false;
		
		$this->ret->setRequest($this->req->jsonArray);
		$this->ret->setTitle($this->request['service'].'::'.$this->request['function']);
		$this->ret->setDebug('Server Versions',$this->getServerVersions());
		$this->ret->setDebug('Using Version',$this->getLatestServiceVersion());
		
		if(get_class($kt)=='KTAPI'){
			$this->kt=&$kt;
		}else{
			$this->ret->addError('KnowledgeTree Object not Received in '.__CLASS__.' constructor. Quitting.');
			return $this->render();
		}

		$this->dispatch();
		return $this->render();
	}
	

	/**
	 * Alias for responseobject->log
	 * @param $str
	 * @return void
	 */
	protected function log($str=''){
		$this->ret->log($str);
	}
	
	
	/**
	 * Alias for responseobject->error
	 * @param $errMsg
	 * @return void
	 */
	protected function error($errMsg=NULL){
		$this->ret->addError($errMsg);
	}
	
	
	/**
	 * Provide a structured array. The resultant array will contain all the keys (empty values) listed in the $structString.
	 * Where these values exist in the passed array $arr, they will be used, otherwise they will be empty.
	 * 
	 * @param $structString
	 * @param $arr
	 * @return array
	 */
	private function structArray($structString=NULL,$arr=NULL){
		$struct=array_flip(split(',',(string)$structString));
		return array_merge($struct,is_array($arr)?$arr:array());
	}

	
	/**
	 * Dispatch to the specified service
	 * @return void
	 */
	public function dispatch(){
		$request=$this->request;

		$this->loadService($request['service']);
		if(class_exists($request['service'])){
			$service=new $request['service']($this,$this->ret,$this->kt,$this->request,$this->auth);
		}else{
			$this->ret->setDebug('Service could not be loaded',$request['service']);
		}
		
		$this->ret->setdebug('dispatch_request','The service class loaded');

		if(method_exists($service,$request['function'])){
			$this->ret->setDebug('dispatch_execution','The service method was found. Executing');
			$service->$request['function']($request['parameters']);
		}else{
			$this->ret->addError("Service {$request['service']} does not contain the method: {$request['function']}");
			return false;
		}
	}
	
	
	/**
	 * Load the service or throw an exception
	 * 
	 * @param $serviceName
	 * @return unknown_type
	 */
	public function loadService($serviceName=NULL){
		$version=$this->getLatestServiceVersion();
		if(!class_exists($serviceName)){
			if(file_exists('services/'.$version.'/'.$serviceName.'.php')){
				require_once('services/'.$version.'/'.$serviceName.'.php');
				return true;
			}else{
				throw new Exception('Service could not be found: '.$serviceName);
				return false;
			}
		}
	}

	
	/**
	 * Get a list of all the server versions that are available
	 * 
	 * @return array
	 */
	public function getServerVersions(){
		$folder='services/';
		$contents=scandir($folder);
		$dir=array();
		foreach($contents as $item){
			if(is_dir($folder.$item) && $item!='.' && $item!=='..'){
				$dir[]=$item;
			}
		}
		rsort($dir);
		return $dir;		
	}
	
	
	/**
	 * Get the latest service version. Ajax from the Website always make use of the latest version available.
	 * @return unknown_type
	 */
	public function getLatestServiceVersion(){
		$ret= $this->getServerVersions();
		return $ret[0];
	}
	
	
	/**
	 * Render the output
	 * @return unknown_type
	 */
	public function render(){
		echo $this->ret->getJson();
		return true;
	}


}
?>
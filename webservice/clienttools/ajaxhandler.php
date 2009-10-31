<?php
class ajaxHandler{
	public $ret=NULL;
	public $req=NULL;
	public $version=NULL;
	public $auth=NULL;
	public $request=NULL;
	public $kt=NULL;
	public $authenticator=NULL;
	public $noAuthRequireList=array();
	public $standardServices=array('system');

	public function __construct(&$ret=NULL,&$kt,$noAuthRequests=''){
		// set a local copy of the json request wrapper
		$noAuthRequests=is_array($noAuthRequests)?$noAuthRequests:split(',',(string)$noAuthRequests);
		$this->registerNoAuthRequest($noAuthRequests);
		$this->req=new jsonWrapper(isset($_GET['request'])?$_GET['request']:(isset($_POST['request'])?$_POST['request']:''));
		$this->auth=$this->structArray('user,pass,passhash,appType,session,token,version',$this->req->jsonArray['auth']);
		$this->request=$this->structArray('service,function,parameters',$this->req->jsonArray['request']);

		$add_params=array_merge($_GET,$_POST);
		unset($add_params['request'],$add_params['datasource']);
		$this->request['parameters']=array_merge($this->request['parameters'],$add_params);


		// set the response object
		if(get_class($ret)=='jsonResponseObject'){
			$this->ret=&$ret;
		}else{
			$this->ret=new jsonResponseObject();
		}
		$this->ret->setRequest($this->req->jsonArray);
		$this->ret->setTitle($this->request['service'].'::'.$this->request['function']);
		$this->ret->setDebug('Server Versions',$this->getServerVersions());

		if(get_class($kt)=='KTAPI'){
			$this->kt=&$kt;
		}else{
			$this->ret->addError('KnowledgeTree Object not Received in '.__CLASS__.' constructor. Quitting.');
			return $this->render();
		}

		// Prepare
		if(!$this->isStandardService()){
			$this->loadService('auth');
			$this->authenticator=new auth($this,$this->ret,$this->kt,$this->request,$this->auth);
			
	
			//Make sure a token exists before continuing
			if(!$this->verifyToken())return $this->render();
	
	
			if(!$this->verifySession()){
				$this->doLogin();
				$isAuthRequired=$this->isNoAuthRequiredRequest();
				$isAuthenticated=$this->isAuthenticated();
				if(!$isAuthRequired && !$isAuthenticated)return $this->render();
			}
		}
		
		$this->dispatch();

		return $this->render();
	}
	
	private function structArray($structString=NULL,$arr=NULL){
		$struct=array_flip(split(',',(string)$structString));
		return array_merge($struct,is_array($arr)?$arr:array());
	}

	public function dispatch(){
		$request=$this->request;
		if($request['auth']){
			$service=$this->authenticator;
		}else{
			$this->loadService($request['service']);
			if(class_exists($request['service'])){
				$service=new $request['service']($this,$this->ret,$this->kt,$this->request,$this->auth);
			}else{
				$this->ret->setDebug('Service could not be loaded',$request['service']);
			}
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
	
	public function isStandardService(){
		return in_array($this->request['service'],$this->standardServices);
	}
	

	public function loadService($serviceName=NULL){
		if(in_array($serviceName,$this->standardServices)){
			$fileName=dirname(__FILE__).'/standardservices/'.$serviceName.'.php';
			$this->ret->setDebug('standardService Found',$fileName);
			if(!class_exists($serviceName)){
				if(file_exists($fileName)){
					require_once($fileName);
					return true;
				}else{
					throw new Exception('Standard Service could not be found: '.$serviceName);
					return false;
				}
			}
		}else{
			$version=$this->getVersion();
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
	}

	protected function verifyToken(){
		$token=isset($this->auth['token'])?$this->auth['token']:NULL;
		if(!$token){
			$token=md5(rand()*rand());
			$this->ret->setStatus('random_token',$token);
			return false;
		}
		return true;
	}

	public function getVersion(){
		if(!$this->version)$this->version=$this->req->getVersion();
		return $this->version;
	}
	
	public function getServerVersions(){
		$folder='services/';
		$contents=scandir($folder);
		$dir=array();
		foreach($contents as $item){
			if(is_dir($folder.$item) && $item!='.' && $item!=='..'){
				$dir[]=$item;
			}
		}
		return $dir;		
	}

	protected function verifySession(){
		return $this->authenticator->pickup_session();
	}

	protected function isAuthenticated(){
		return $this->authenticator->pickup_session();
	}

	protected function doLogin(){
		if($this->authenticator->login()){
			return true;
		}else{
			$this->ret->addError('Unsuccesful Login');
			return false;
		}
	}

	public function render(){
		echo $this->ret->getJson();
		return true;
	}
	
	public function registerNoAuthRequest($requestString=''){
		if($requestString){
			if(is_array($requestString)){
				foreach ($requestString as $rString){
					$rString=strtolower((string)$rString);
					$this->noAuthRequireList[$rString]=$rString;
				}
			}else{
				$requestString=strtolower((string)$requestString);
				$this->noAuthRequireList[$requestString]=(string)$requestString;
			}
		}
	}
	
	public function isNoAuthRequiredRequest(){
		$req=$this->request;
		$reqString=strtolower("{$req['service']}.{$req['function']}");
		return in_array($reqString,$this->noAuthRequireList);
	}

}
?>
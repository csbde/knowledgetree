<?php
class ajaxHandler{
	public $ret=NULL;
	public $req=NULL;
	public $version=NULL;
	public $auth=NULL;
	public $request=NULL;
	public $kt=NULL;
	public $authenticator=NULL;

	public function __construct(&$ret=NULL,&$kt){
		// set a local copy of the json request wrapper
		$this->req=new jsonWrapper(isset($_GET['request'])?$_GET['request']:(isset($_POST['request'])?$_POST['request']:''));
		$this->auth=$this->req->jsonArray['auth'];
		$this->request=$this->req->jsonArray['request'];


		// set the response object
		if(get_class($ret)=='jsonResponseObject'){
			$this->ret=&$ret;
		}else{
			$this->ret=new jsonResponseObject();
		}
		$this->ret->setRequest($this->req->jsonArray);

		if(get_class($kt)=='KTAPI'){
			$this->kt=&$kt;
		}else{
			$this->ret->addError('KnowledgeTree Object not Received in '.__CLASS__.' constructor. Quitting.');
			return $this->render();
		}

		// Prepar		
		$this->loadService('auth');
		$this->authenticator=new auth($this->ret,$this->kt,$this->request,$this->auth);
		

		//Make sure a token exists before continuing
		if(!$this->verifyToken())return $this->render();


		if(!$this->verifySession()){
			$this->doLogin();
			if(!$this->isAuthenticated())return $this->render();
		}
		
		$this->dispatch();

		return $this->render();
	}

	public function dispatch(){
		$request=$this->request;
		$this->loadService($request['service']);
		$service=new $request['service']($this->ret,$this->kt,$this->request,$this->auth);
		$this->ret->setTitle($request['service'].'::'.$request['function']);
		if(method_exists($service,$request['function'])){
			//$this->ret->setDebug('got here');
			$service->$request['function']($request['parameters']);
		}else{
			$this->ret->addError("Service {$request['service']} does not contain the method: {$request['function']}");
			return false;
		}
	}
	

	public function loadService($serviceName=NULL){
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

	protected function verifyToken(){
		$token=isset($this->auth['token'])?$this->auth['token']:NULL;
		if(!$token){
			$token=md5(rand()*rand());
			$this->ret->setStatus('random_token',$token);
			return false;
		}
		return true;
	}

	protected function getVersion(){
		if(!$this->version)$this->version=$this->req->getVersion();
		return $this->version;
	}

	protected function verifySession(){
		return $this->authenticator->pickup_session();
	}

	protected function isAuthenticated(){
		return true;
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

}
?>
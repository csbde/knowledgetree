<?php
class ajaxHandler{
	public $ret=NULL;
	public $req=NULL;
	public $version=NULL;
	public $auth=NULL;
	public $request=NULL;
	
	public function __construct(&$ret=NULL){
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
		
		
		//Make sure a token exists before continuing
		if(!$this->verifyToken())return $this->render();
		
		
		if(!$this->verifySession()){
			$this->doLogin();
			if(!$this->isAuthenticated())return $this->render();
		}
		
		
		return $this->render();
	}
	
	
	public function loadService($serviceName=NULL){
		$version=$this->getVersion();
		if(!class_exists($serviceName)){
			if(file_exists('services/'.$version.'/'.$serviceName.'.php')){
				require_once('services/'.$version.'/'.$serviceName.'.php');
			}else{
				throw new Exception('Service could not be found: '.$serviceName);
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
		return false;		
	}
	
	protected function isAuthenticated(){
		
	}
	
	protected function doLogin(){
		$this->loadService('auth');
		$auth=new auth($this->ret);
		if(!$auth->login($this->auth)){
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
<?php
class ajaxHandler{
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
	
	/**
	 * 1.Parse JSON
	 * 2.Check Request Validity (hash/ip/expiration token)
	 * 3.Preliminary Session Check
	 * 		if no session or invalid session
	 * 			3.1 Use credentials to create a new session.
	 * 			3.3 Update Authentication segment with new sessionid
	 * 4.Authentication Check
	 * 5.Service Dispatch
	 */

	public function __construct(&$response=NULL,&$kt,$noAuthRequests=''){
		
		//========================= Preparations
		// set the response object
		if(get_class($response)=='jsonResponseObject'){
			$this->ret=&$response;
		}else{
			$this->ret=new jsonResponseObject();
		}
		$this->log("[__construct]ENTERING PREPARATIONS");		

		$this->remoteIp = (getenv(HTTP_X_FORWARDED_FOR)) ?  getenv(HTTP_X_FORWARDED_FOR)  :  getenv(REMOTE_ADDR);
		$this->log("[__construct]Remote IP determined as: {$this->remoteIp}");		

		$noAuthRequests=is_array($noAuthRequests)?$noAuthRequests:split(',',(string)$noAuthRequests);
		$this->registerNoAuthRequest($noAuthRequests);

		$this->rawRequestObject=isset($_GET['request'])?$_GET['request']:(isset($_POST['request'])?$_POST['request']:'');
		$this->digestToken=isset($_GET['msgAuth'])?$_GET['msgAuth']:(isset($_POST['msgAuth'])?$_POST['msgAuth']:'');
		$this->log("[__construct]DigestToken Found: {$this->digestToken}");		
		
		$this->ret->addDebug('Raw Request',$this->rawRequestObject);
		$this->ret->addDebug('DigestToken Received',$this->digestToken);
		$this->ret->addDebug('Remote IP',$this->remoteIp);
		
		
		if($this->auth['session'])session_id($this->auth['session']);
		$this->session=session_id();
		$this->log("[__construct]Session Restarted as: {$this->session}");		
		//		session_id('BLANK_SESSION');
		
		
		
		//========================= 1. Parse Json
		$this->log("[__construct]ENTERING Parse Json");		
		$this->req=new jsonWrapper($this->rawRequestObject);
		$this->auth=$this->structArray('user,pass,passhash,appType,session,token,version',$this->req->jsonArray['auth']);
		$this->request=$this->structArray('service,function,parameters',$this->req->jsonArray['request']);

		//Add additional parameters
		$add_params=array_merge($_GET,$_POST);
		unset($add_params['request'],$add_params['datasource']);
		$this->request['parameters']=array_merge($this->request['parameters'],$add_params);
		$this->parameters=$this->request['parameters'];
		
		if(!$this->auth['debug'])$this->ret->includeDebug=false;
		
		$this->ret->setRequest($this->req->jsonArray);
		$this->ret->setTitle($this->request['service'].'::'.$this->request['function']);
		$this->ret->setDebug('Server Versions',$this->getServerVersions());
		
		
		
		
		
		//========================= 2. Test System Requirements
		$this->log("[__construct]ENTERING Test System Requirements");		
		if(get_class($kt)=='KTAPI'){
			$this->kt=&$kt;
		}else{
			$this->ret->addError('KnowledgeTree Object not Received in '.__CLASS__.' constructor. Quitting.');
			return $this->render();
		}
		
		
		//TODO: Get rid of this service
//		$this->loadService('auth');
//		$this->authenticator=new auth($this,$this->ret,$this->kt,$this->request,$this->auth);
		
		
		
		//========================= 3. Check Request Validity
		$this->log("[__construct]ENTERING Check Request Validity");		
		if(!$this->checkRequestValidity())return $this->render();
		if(!$this->checkTokenValidity())return $this->render();
		
		
		
		
		//========================= 4. Preliminary Session Check
		$this->log("[__construct]ENTERING Preliminary Session Check");		
		if(!$this->checkSessionValidity()){
			$this->creatNewSession();				//(login) This may fail, be the user is still allowed to dispatch to the 
		}
		
		
		
		
		
		//========================= 5. Authentication Check
		$this->log("[__construct]ENTERING Authentication Check");		
		if(!$this->isStandardService() && !$this->isNoAuthRequiredRequest()){
			//Authentication is Required
			$this->log("[__construct]Determined Authentication is required");		
			if(!$this->checkCredentials()){
				throw new Exception('User Credentials Necessary for Requested Service');
				return $this->render();
			}
		}
		
		
		
		
		
		
		//========================= 6. Service Dispatch
		$this->log("[__construct]ENTERING Service Dispatch");		
		$this->dispatch();
		return $this->render();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	protected function checkRequestValidity(){
		$this->log("[checkRequestvalidity]Entering...");		
		$securityHash=md5(md5($this->rawRequestObject).'_'.$this->auth['token'].'_'.$this->getUserPass());
		$digestToken=$this->digestToken;
		$this->log("[checkRequestvalidity]comparing {$securityHash} with {$digestToken} as received");		

		$passed=$securityHash==$digestToken;

		$data=array(
			'Received Token'	=>$digestToken,
			'Expected Token'	=>$securityHash,
			'Passed'			=>$passed,
			''
		);		
		$this->ret->addDebug('Message Digest Security',$data);
		
		if(!$passed){
			$this->log("[checkRequestvalidity]Failed Validity Test");
			if(!$this->isStandardService() && !$this->isNoAuthRequiredRequest()){
				if(!$this->checkCredentials()){
					throw new Exception('User Credentials are Incorrect');
					return $this->render();
				}				
			}		
			throw new Exception('Message Integrity Was Compromised.');
		}
		return $passed;
	}
	
	
	protected function checkSessionValidity(){
		$valid=$this->start_session();
		$this->auth['session']=session_id();
		$this->ret->setStatus('session_id',session_id());
		$this->ret->addDebug('Auth',array('Session Check'=>$valid));
		return $valid;
	}
	
	protected function checkTokenValidity(){
		if($this->parameters['permanentURL'])return true;
		$token=$this->auth['token'];
		$tokenList=$_SESSION['JAPI_TOKEN_STORE']?$_SESSION['JAPI_TOKEN_STORE']:array();
		$valid=!in_array($token,$tokenList);
		if($valid){
			$tokenList[$token]=$token;
			$_SESSION['JAPI_TOKEN_STORE']=$tokenList;
		}else{
			$this->error('Invalid Token - Already Used');
			$this->log('Invalid Token - Already Used');
		}
		
		return $valid;
	}
	
	
	protected function creatNewSession(){
		$this->ret->addDebug('Auth',array('Attempting to Create a New Session'));
		if($this->checkCredentials()){
			$ssession=KTAPI_UserSession::_check_session($this->getUserObject(),$this->remoteIp,$this->auth['appType']);
			$session=$ssession[0];
			$this->ret->addDebug('####################################Session Created : '.$session);
			$this->auth['session']=session_id();
			$this->ret->setStatus('session_id',session_id());
			return true;
		}else{
			return false;
		}
	}
	
	protected function start_session(){
		$app_type=$this->auth['appType'];
		$session_id=$this->auth['session'];
		$ip=$this->remoteIp;
		
		$session=$this->kt->get_session();
		
		if(get_class($session)=='KTAPI_UserSession'){
			return true;			
		}else{
			$session = $this->kt->get_active_session($session_id, $ip, $app_type);
			
			if (PEAR::isError($session)){
	            return false;
	        }
			$this->auth['session']=session_id();
			$this->ret->setStatus('session_id',session_id());
			return true;		
		}
				
		
	}
	
	
	protected function getUserPass(){
		$l_pass=md5('@NO_AUTH_NEEDED@');
		$u=$this->getUserObject();
		if($u){
			$l_pass=$this->getUserObject()->getPassword();
		}
		return $l_pass;
	}
	
	protected function getUserObject(){
		$kt=$this->kt;
		$user=$this->auth['user'];
        $o_user=$kt->get_user_object_by_username($user);
        
        if(PEAR::isError($o_user)){
        	if(!isset($this->errors['usernotfound']))$this->ret->addError('User '.$user.' not found');
        	$this->errors['usernotfound']=true;
        	return false;
        }else{
        	$this->log("[getUserObject] Found User: ".$o_user->getName());
        }
		return $o_user;
	}
	
	protected function checkCredentials(){
		$user=$this->auth['user'];
		$passHash=$this->auth['passhash'];
		
		$kt=$this->kt;
		
		/*
		 * User Check
		 */
        $o_user=$kt->get_user_object_by_username($user);
        
        if(PEAR::isError($o_user)){
        	if(!isset($this->errors['usernotfound']))$this->ret->addError('User '.$user.' not found');
        	$this->errors['usernotfound']=true;
        	return false;
        }
        
        /*
         * BAOBAB Licence Check
         */
       if ($user != 'admin') {
        	try{
        		if(class_exists('BaobabKeyUtil')){
		            if (!BaobabKeyUtil::checkIfLicensed(true)) {
		                $this->ret->setResponse(array('authenticated'=> false, 'message'=> 'license_expired'));
		                $this->ret->addError('Licence Expired');
		                return false;
		            }
        		}else{
        			$this->ret->addError('Licence Utility could not be loaded. Appears to be a Community version.');
        			$this->ret->setResponse(array('authenticated'=> false, 'message'=> 'Licence Utility could not be loaded. Appears to be a Community version.'));
        			return false;
				}
        	}catch(Exception $e){
        		$this->ret->addError('could not execute BaobabKeyUtil::checkIfLicensed');
        		$this->ret->setResponse(array('authenticated'=> false, 'message'=> 'BaobabKeyUtil::checkIfLicensed error'));
        		return;
        	}
        }
        
        
        /*
         * Password Check
         */
        try{
        	$l_pass=$o_user->getPassword();
        	$l_passHash=md5($l_pass.$this->auth['token']);
        	
        	$passed=$passHash==$l_passHash;
        	
        	$this->ret->setDebug('Auth',array(
        		'User Real Password'=>$l_pass,
        		'User Real Password Hash'=>$l_passHash,
        		'Received Password Hash'=>$passHash,
        		'passed'=>$passed
        	));
        	
        	return $passed;
        	
        }catch(Exception $e){
        	throw new Exception('Unknown credentialCheck error encountered');
        	return false;
        }
		
        return ture;
	}
	
	
	
	protected function log($str=''){
		$this->ret->log($str);
	}
	
	
	protected function error($errMsg=NULL){
		$this->ret->addError($errMsg);
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
		$isStandardService=in_array($this->request['service'],$this->standardServices);
		
		$debug=array(
			'requested service'	=>$this->request['service'],
			'standard services'	=>$this->standardServices,
			'isStandardService'	=>$isStandardService
		);
		
		$this->ret->addDebug('ajaxhandler::isStandardService',$debug);
		
		return $isStandardService;
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

	//TODO: Remove this function - deprecated
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

	//TODO: Remove this function - deprecated
	protected function verifySession(){
		return $this->authenticator->pickup_session();
	}

	//TODO: Remove this function - deprecated
	protected function isAuthenticated(){
		return $this->authenticator->pickup_session();
	}

	//TODO: Remove this function - deprecated
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
		$requiresAuth=in_array($reqString,$this->noAuthRequireList);

		$debug=array(
			'requested service  method'	=>$reqString,
			'no auth required list'	=>$this->noAuthRequireList,
			'requires auth'	=>$requiresAuth
		);
		
		$this->ret->addDebug('ajaxhandler::isNoAuthRequiredRequest',$debug);
		
		return $requiresAuth;
	}

}
?>
<?php
include_once('../../ktapi/ktapi.inc.php');
error_reporting(E_ERROR);

/**
 * Expected structure of the json-decoded request object
 * [auth]
 * 		[user]
 * 		[passhash]
 * 		[session]
 * 		[appType]
 * [request]
 * 		[service]
 * 		[function]
 * 		[parameters]
 *
 *
 */

class ktjapi{
	//System error codes
	const ERR_SYSTEM_OK=0;
	const ERR_SYSTEM_ERROR=1;

	//Dispatcher error codes
	const ERR_DISPATCHER_SERVICE_NOT_FOUND=100;
	const ERR_DISPATCHER_METHOD_NOT_FOUND=101;

	protected $debug=true;
	protected $kt;
	protected $raw;
	protected $request=array(
	'auth'		=>array(
		'user'			=>'',
		'passhash'		=>'',
		'session'		=>'',
		'appType'		=>'',
		'token'			=>''
	),
	'request'	=>array(
		'service'		=>'',
		'function'		=>'',
		'parameters'	=>''
	)
	);
	protected $session_id;
	protected $session;
	protected $token;
	protected $response=array(
		'errors'			=>array(
			'hadErrors'			=>self::ERR_SYSTEM_OK ,
			'errors'			=>array()
		),
		'status'			=>array(
			'session_id'		=>'',
			'random_token'		=>''
		),
		'data'				=>array(),
		'request'			=>array(),
		'debug'				=>array()
	);

	public function __construct($versions){
		$this->versions=$versions;
		$this->kt=new KTAPI();
		$this->parseRequest();
		$this->verifyToken();
		$this->verifySession();
		if($this->auth()){
			$this->dispatch();
			$this->setAuthResponse();
		}
		$this->response['request']=$this->request;
		if(!$this->debug)unset($this->response['debug']);
		echo json_encode($this->response);
	}

	protected function parseRequest(){
		$this->raw=@file_get_contents('php://input');
		$this->raw=$_GET['request']?$_GET['request']:$_POST['request'];

		$req=json_decode($this->raw,true);
		if(is_array($req))$this->request=$req;
	}

	protected function verifyToken(){
		$this->token=$this->request['auth']['token']?$this->request['auth']['token']:md5(rand()*rand());
		$this->response['status']['random_token']=$this->token;
	}

	protected function verifySession(){
		$this->session=$this->getSession($this->request['auth']['session']);
	}


	protected function auth(){
		if(!$this->isLoggedIn()){
			if(!$this->login()){
				$this->response['error']['code']=100;
				$this->response['error']['message']='Invalid credentials. You are not authorised on this repository.';
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}

	protected function setAuthResponse(){
		$this->response['status']['session_id']=$this->session_id;
		$this->response['status']['random_token']=$this->token;
	}

	protected function getSession($sessId=null){
		$session=$this->kt->get_active_session($sessId?$sessId:session_id());
		return PEAR::isError($session)?null:$session;
	}

	public  function isLoggedIn(){
		return isset($this->session);
	}

	protected function setDebug($title='',$obj=NULL){
		$this->response['debug'][$title]=$obj;
	}

	public  function logout(){
		if($this->isLoggedIn()){
			$this->session->logout();
			$this->checkSession();
		}
	}

	public  function login(){
		if(!$this->isLoggedIn()){
			$user=$this->kt->get_user_object_by_username($this->request['auth']['user']);
			if(!PEAR::isError($user)){

				$pass=$user->getPassword();
				$passHash=md5($pass.$this->token);
				//$this->request['auth']['passhash']=md5(md5($this->request['auth']['pass']).$this->token);
				$this->setDebug('Expected passHash',$passHash);
				$this->setDebug('Serverside Token',$this->token);
				$this->setDebug('Expected Password',$pass);

				if($passHash==$this->request['auth']['passhash']){
					$uSession=KTAPI_UserSession::_check_session($user, null, $this->request['auth']['appType']);
					$this->response['debug']['pass_confirmed_sess_detail']=$uSession;
					if(!PEAR::isError($uSession)){
						$this->session= &new KTAPI_UserSession($this->kt, $user, $uSession[0], $uSession[1], NULL);
						$this->session = $this->kt->get_active_session($session_id, null, $application);
						$this->session_id=$uSession[0];
						$this->response['debug']['session']=$this->session;
						$this->response['status']['session_id']=$uSession[0];
						$this->response['debug']['isLoggedIn']=$this->isLoggedIn()?'True':'False';
						$this->kt=new KTAPI();
					}else{
						//handle the session error
					}
				}
			}
			$this->response['debug']['isLoggedIn2']=$this->isLoggedIn()?'True':'False';
		}
		return $this->isLoggedIn();
	}

	protected function addError($code=NULL,$message=NULL){
		if($code!==null){
			$this->response['errors']['errors'][]=array('code'=>$code,'message'=>$message);
		}
		if(count($this->response['errors']['errors']))$this->response['errors']['hadErrors']=self::ERR_SYSTEM_ERROR;
	}

	protected function dispatch(){
		$class=strtolower($this->request['request']['service']);
		$function=strtolower($this->request['request']['function']);
		$params=$this->request['request']['parameters'];
		$version=join('',$this->request['auth']['version']);
		if($this->verifyServiceClass($class)){
			$serviceClass='clientTools_service_'.$class.'_'.$version;
			$service=new $serviceClass($this->kt,$this->session,$this->session_id,$this);
			if(method_exists($service,$function)){
				$service->$function($params);
				$this->response['data']=$service->getResponse();
				$this->response['debug']=array_merge($this->response['debug'],$service->getDebug());
				$errors=$service->getErrors();
				foreach ($errors as $error){
					$this->addError($error['code'],$error['message']);
				}
			}else{
				$this->addError(self::ERR_DISPATCHER_METHOD_NOT_FOUND,"Method '{$class}.{$function}' not Found.");
			}
		}else{
				$this->addError(self::ERR_DISPATCHER_SERVICE_NOT_FOUND,"Service '{$class}' not Found.");
		}
	}

	protected function verifyServiceClass($class){
		$version=join('.',$this->request['auth']['version']);
		$fname=str_replace('/','\\',str_replace('//','/',dirname(__FILE__).'/')."services/{$version}/{$class}.service.php");
		if(file_exists($fname)){
			require_once($fname);
		}else{
			$this->response['debug'][]="Service File {$fname} Not Found";
			return false;
		}
		return true;
	}

}
require_once("../../config/dmsDefaults.php");
require_once('clientTools_service.php');


$k=new ktjapi();
//echo '<pre>'.print_r($k,true).'</pre>';
//echo '<pre>'.print_r($_GET,true).'</pre>';
?>
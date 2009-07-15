<?php
include_once('../../ktapi/ktapi.inc.php');

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
	protected $kt;
	protected $raw;
	protected $request=array(
	'auth'		=>array(
		'user'			=>'',
		'passhash'		=>'',
		'session'		=>'',
		'appType'		=>''
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
		'error'				=>array(
			'code'				=>0,
			'message'			=>''
		),
		'status'			=>array(
			'session_id'		=>'',
			'random_token'		=>''
		),
		'data'				=>array()
	);

	public function __construct(){
		$this->kt=new KTAPI();
		$this->parseRequest();
		if($this->auth()){
			$this->dispatch();
			$this->setAuthResponse();
		}
		echo json_encode($this->response);
	}

	protected function parseRequest(){
		$this->raw=@file_get_contents('php://input');
		$this->req=@json_decode($this->raw,true);
		if(!is_array($this->req))$this->req=array();

		$this->checkSession();
		$this->token=$_SESSION['token']?$_SESSION['token']:md5(rand()*rand());
		$this->response['status']['random_token']=$this->token;
	}

	protected function checkSession(){
		$this->session_id=isset($this->request['auth']['session'])?$this->request['auth']['session']:session_id();
		$this->session=$this->getSession($this->session_id);
	}

	protected function auth(){
		if(!$this->isLoggedIn()){
			$this->login();
			if(!$this->isLoggedIn()){
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
		$this->checkSession();
		if(rand(0,100)>50)$this->token=md5(rand()*rand());
		$_SESSION['ktjapi_token']=$this->token;
		$this->response['status']['session_id']=$this->session_id;
		$this->response['status']['random_token']=$this->token;
	}

	protected function getSession($sessId=null){
		$session=$this->kt->get_active_session($sessId?$sessId:session_id());
		return PEAR::isError($session)?null:$session;
	}

	public  function isLoggedIn(){
		return !is_null($session);
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
				if($passHash==$this->request['auth']['passhash']){
					$uSession=KTAPI_UserSession::_check_session($user, null, $this->request['auth']['appType']);
					if(!PEAR::isError($uSession)){
						$session= &new KTAPI_UserSession($this->kt, $user, $uSession[0], $uSession[1], NULL);
					}else{
						//handle the session error
					}
				}
			}
			$this->checkSession();
		}
	}

	protected function dispatch(){

	}
}

$k=new ktjapi();
?>
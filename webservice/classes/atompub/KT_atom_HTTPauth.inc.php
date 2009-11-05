<?php
class KT_atom_HTTPauth{
	public static function getCredentials(){

	    // Workaround for mod_auth when running php cgi
	    if(!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['HTTP_AUTHORIZATION'])){
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
	    }

		$credentials=array('user'=>'','pass'=>'','method'=>'');
		if(isset($_SERVER['PHP_AUTH_USER'])){
			$credentials['user']=$_SERVER['PHP_AUTH_USER'];
			$credentials['pass']=isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:'';
			$credentials['method']=isset($_SERVER['AUTH_TYPE'])?$_SERVER['AUTH_TYPE']:'';
		}else{
			if(isset($_SERVER['kt_auth']) || isset($_SERVER['REDIRECT_kt_auth'])){
				$ktauth=isset($_SERVER['kt_auth'])?$_SERVER['kt_auth']:$_SERVER['REDIRECT_kt_auth'];
				list($authMethod,$authCred)=split(' ',$ktauth);
				$authMethod=strtolower(trim($authMethod));
				$authCred=base64_decode(trim($authCred));
				list($authUser,$authPass)=split(':',$authCred);
				$credentials['method']=$authMethod;
				$credentials['user']=$authUser;
				$credentials['pass']=$authPass;
			}
		}
		return $credentials;
	}

	public static function requireBasicAuth($realm='default',$message=''){
		$realm=$realm?$realm:'default';
		header('WWW-Authenticate: Basic Realm="'.$realm.'"');
		header('HTTP/1.0 401 Unauthorized');
		echo $message;
		exit;
	}

	public static function isLoggedIn(){
		$kt=new KTAPI();
		$session=$kt->get_active_session(session_id());
		return !PEAR::isError($session);
	}

	public static function logout(){
		$kt=new KTAPI();
		$session=$kt->get_active_session(session_id());
		if(!PEAR::isError($session)){
			$session->logout();
		}
	}

	public static function login($realm,$msg){
		$kt=new KTAPI();
		$session=$kt->get_active_session(session_id());
		if(PEAR::isError($session)){
			$cred=self::getCredentials();
			$kt->login($cred['user'],$cred['pass']);
			if(self::isLoggedIn())return;
		}
		self::requireBasicAuth($realm,$msg);
	}
}
?>
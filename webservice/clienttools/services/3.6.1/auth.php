<?php

class auth{
	protected $ret;
	
	public function __construct(&$ret=NULL){
		// set the response object
		if(get_class($ret)=='jsonResponseObject'){
			$this->ret=&$ret;
		}else{
			$this->ret=new jsonResponseObject();
		}
	}
	
	public function login($params){
		
		$username=$params['user'];
		$passhash=$params['passhash'];
		$token=$params['token'];
		$app_type=$params['appType'];
		$session_id=$params['session'];
		$ip=$_SERVER['REMOTE_ADDR'];
		$language=isset($params['language'])?$params['language']:'en';

		setcookie("kt_language", $language, 2147483647, '/');

		
        $kt = new KTAPI();
        
        
//        if ($username != 'admin') {
//            require_once(KT_DIR .  '/plugins/wintools/baobabkeyutil.inc.php');
//            
//            if (!BaobabKeyUtil::checkIfLicensed(true)) {
//                return array('authenticated'=> false, 'message'=> 'license_expired');
//            }
//        }
	
        $user=$kt->get_user_object_by_username($username);
        if(!PEAR::isError($user)){
	        $password=$user->getPassword();
			$localPassHash=md5($password.$token);
			if($localPassHash==$passhash){
				$session=array();
				$this->ret->setDebug('trying to start session with',array('username'=>$username,'password'=>$password));
		        $session = $kt->start_session($username, $password, NULL, NULL);
		        if(!PEAR::isError($session)){
		        	$this->ret->setStatus('session_id',$session);
		        }else{
		        	$this->ret->setDebug('failed login',$session);
		        	throw new Exception('Unknown Login Error');
		        	return false;
		        }
			}else{
				throw new Exception('Incorrect Credentials');
				return false;
			}
        }else{
        	throw new Exception('Unrecognized User');
        	return false;
        }
        return true;
	}
}

?>
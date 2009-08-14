<?php

class auth extends client_service {

	public function login(){
		$params=$this->AuthInfo;
		
		$username=$params['user'];
		$passhash=$params['passhash'];
		$token=$params['token'];
		$app_type=$params['appType'];
		$session_id=$params['session'];
		$ip=$_SERVER['REMOTE_ADDR'];
		$language=isset($params['language'])?$params['language']:'en';
		
		$this->Response->setDebug('parameters',$params);

		setcookie("kt_language", $language, 2147483647, '/');

        $kt =& $this->KT;
        
        if ($username != 'admin') {
            require_once(KT_DIR .  '/plugins/wintools/baobabkeyutil.inc.php');
            
            if (!BaobabKeyUtil::checkIfLicensed(true)) {
                return array('authenticated'=> false, 'message'=> 'license_expired');
            }
        }
	
        $user=$kt->get_user_object_by_username($username);
        if(!PEAR::isError($user)){
	        $password=$user->getPassword();
			$localPassHash=md5($password.$token);
			if($localPassHash==$passhash){
				$session=new stdClass();
				$this->Response->setDebug('trying to start session with',array('username'=>$username,'password'=>$password));
		        $session = $kt->start_session($username, $params['pass'],NULL,$app_type);
		        if(!PEAR::isError($session)){
		        	$this->Response->setStatus('session_id',$session->get_session());
		        }else{
		        	$this->Response->setDebug('failed login',print_r($session,true));
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
	
	public function pickup_session(){
		$params=$this->AuthInfo;
		$app_type=$params['appType'];
		$session_id=$params['session'];
		$ip=$_SERVER['REMOTE_ADDR'];

		$session = $this->KT->get_active_session($session_id, $ip, $app_type);
		
		if (PEAR::isError($session)){
            return false;
        }
       	$this->Response->setStatus('session_id',$session->get_session());
        return true;
	}
}

?>
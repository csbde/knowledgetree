<?

class KTAPI_Session 
{
	var $ktapi;
	var $user = null;
	var $session = '';
	var $sessionid = -1;
	var $active;
	
	function KTAPI_Session(&$ktapi, &$user)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi,'KTAPI'));
		assert(!is_null($user));
		assert(is_a($user,'User'));		
		
		$this->ktapi=&$ktapi;
		$this->user=&$user;
		$this->active = false;
	}
	
	/**
	 * return the session string
	 *
	 * @return string
	 */
	function get_session()
	{
		die('get_session() should be overloaded!');
	}
	
	/**
	 * Return the session id 
	 * 
	 * @return int
	 */
	function get_sessionid()
	{
		die('get_sessionid() should be overloaded!');
	}
	
	/**
	 * Return the user
	 *
	 * @return User
	 */
	function &get_user()
	{
		 return $this->user;
	}
	
	function logout()
	{
		$this->active=false;
		// don't need to do anything really
	}
	
	function is_active()
	{
		return $this->active;
	}
	
}


class KTAPI_UserSession extends KTAPI_Session 
{
	var $ip = null;
	
	function KTAPI_UserSession(&$ktapi, &$user, $session, $sessionid, $ip)
	{
		parent::KTAPI_Session($ktapi, $user);
				
		$this->ktapi 	= &$ktapi;
		$this->user 	= &$user;
		$this->session 	= $session;
		$this->sessionid = $sessionid;
		$this->ip 		= $ip;

		// TODO: get documenttransaction to not look at the session variable!
		$_SESSION["userID"] = $user->getId();
		$_SESSION["sessionID"] = $this->sessionid;	
		$this->active = true;		
	}
	
	/**
	 * This returns the session string
	 *
	 * @return string
	 */
	function get_session()
	{
		return $this->session;
	}
	
	/**
	 * This returns the sessionid in the database.
	 *
	 * @return int
	 */
	function get_sessionid()
	{
		return $this->sessionid;
	}
			
	/**
	 * This resolves the user's ip
	 *
	 * @access private
	 * @return string
	 */
	function resolveIP()
	{
		if (getenv("REMOTE_ADDR")) 
		{
        	$ip = getenv("REMOTE_ADDR");
        } 
        elseif (getenv("HTTP_X_FORWARDED_FOR")) 
        {
        	$forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } 
        elseif (getenv("HTTP_CLIENT_IP")) 
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        
        if ($ip == '')
        {
        	$ip = '127.0.0.1';
        }
        
        return $ip;
	}
	
	/**
	 *
	 * @access protected
	 * @static 
	 * @param User $user
	 */
	function _check_session(&$user)
	{       
        $user_id = $user->getId();
        
        $sql = "SELECT count(*) >= u.max_sessions as over_limit FROM active_sessions ass INNER JOIN users u ON ass.user_id=u.id WHERE ass.user_id = $user_id";
		$row = DBUtil::getOneResult($sql);
        if (PEAR::isError($row))
        {
        	return $row;
        }
        if (is_null($row))
        {
        	return new PEAR_Error('No record found for user?');
        }  
        if ($row['over_limit'] == 1)
        {
			return new PEAR_Error('Session limit exceeded. Logout of any active sessions.');
        }
        
        $session = session_id();
        
        $sessionid = DBUtil::autoInsert('active_sessions',
        	array(
        		'user_id' => $user_id,
        		'session_id' => session_id(),
        		'lastused' => date('Y-m-d H:i:s'),
        		'ip' => $ip
        	));
        if (PEAR::isError($sessionid) )
        {
        	return $sessionid;
        }
        
        return array($session,$sessionid);
	}
	
	
	/**
	 * This returns a session object based on authentication credentials.
	 *
	 * @access public
	 * @static 
	 * @param string $username
	 * @param string $password
	 * @return KTAPI_Session
	 */
	function &start_session(&$ktapi, $username, $password, $ip=null)
	{		
		$this->active=false;
		if ( empty($username) ) 
		{
			return new PEAR_Error(_kt('The username is empty.'));
		}

		$user =& User::getByUsername($username);
        if (PEAR::isError($user) || ($user === false)) 
        {
           return new KTAPI_Error(_kt("The user '$username' cound not be found."),$user);      
        }

        if ( empty($password) )
        {
        	return new PEAR_Error(_kt('The password is empty.'));
        }

        $authenticated = KTAuthenticationUtil::checkPassword($user, $password);

        if (PEAR::isError($authenticated) || $authenticated === false)
        {
        	return new KTAPI_Error(_kt("The password is invalid."),$authenticated);
        }
        
        if (is_null($ip))
        {
        	$ip = '127.0.0.1';
        	//$ip = KTAPI_Session::resolveIP();
        }
        
        list($session,$sessionid) = KTAPI_UserSession::_check_session($user);
        if (PEAR::isError($sessionid))
        {
        	return $sessionid;
        }
                
		$session = &new KTAPI_UserSession($ktapi, $user, $session, $sessionid, $ip);		
		 
		return $session;
	}	
	
	/**
	 * This returns an active session.
	 *
	 * @param KTAPI $ktapi
	 * @param string $session
	 * @param string $ip
	 * @return KTAPI_Session
	 */
	function &get_active_session(&$ktapi, $session, $ip)
	{        		
		$sql = "SELECT id, user_id FROM active_sessions WHERE session_id='$session'";
		if (!empty($ip))
		{
			$sql .= " AND ip='$ip'";
		}		
		
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			return new KTAPI_Error(KTAPI_ERROR_SESSION_INVALID, $row);
		}
		
		$sessionid = $row['id'];
		$userid = $row['user_id'];
		
		$user = &User::get($userid);
		if (is_null($user) || PEAR::isError($user))
		{
			return new KTAPI_Error(KTAPI_ERROR_USER_INVALID, $user);
		}
		

		
        $now=date('Y-m-d H:i:s');
        $sql = "UPDATE active_sessions SET last_used='$now' WHERE id=$sessionid";
        DBUtil::runQuery($sql);
        
		$session = &new KTAPI_Session($ktapi, $user, $session, $sessionid, $ip);		
		return $session;
	}
	
	/**
	 * This closes the current session.
	 *
	 */
	function logout()
	{
		$sql = "DELETE FROM active_sessions WHERE id=$this->sessionid";
		$result = DBUtil::runQuery($sql);
		if (PEAR::isError($result))
		{
			return $result;
		}
			
		$this->user 		= null;
		$this->session 		= '';
		$this->sessionid 	= -1;
		$this->active=false;
	}
	
}

class KTAPI_AnonymousSession extends KTAPI_UserSession 
{
	function &start_session(&$ktapi, $ip=null)
	{		
		$user =& User::get(-2);
		if (is_null($user) ||  PEAR::isError($user) || ($user === false) || !$user->isAnonymous())
		{
			return new KTAPI_Error(_kt("The anonymous user could not be found."), $user);
		}

		$authenticated = true;

		$config = &KTConfig::getSingleton();
		$allow_anonymous = $config->get('session/allowAnonymousLogin', false);

		if (!$allow_anonymous)
		{
			return new PEAR_Error(_kt('Anonymous user not allowed'));
		}

		if (is_null($ip))
		{
			$ip = '127.0.0.1';
			//$ip = KTAPI_Session::resolveIP();
		}

        list($session,$sessionid) = KTAPI_UserSession::_check_session($user);
        if (PEAR::isError($sessionid))
        {
        	return $sessionid;
        }
                
		$session = &new KTAPI_AnonymousSession($ktapi, $user, $session, $sessionid, $ip);		
		
		return $session;
	}
}



class KTAPI_SystemSession extends KTAPI_Session 
{
	function KTAPI_SystemSession(&$ktapi, &$user)
	{
		parent::KTAPI_Session($ktapi, $user);
		$this->active=true;
	}
}

?>
<?php
/**
 * API for the handling the KnowledgeTree session
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 */

/**
 * API for the handling the KnowledgeTree session
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_Session
{
    /**
	 * This is a reference to the ktapi object.
	 *
	 * @access protected
	 * @var KTAPI
	 */
    var $ktapi;

    /**
	 * This is a reference to the user object.
	 *
	 * @access protected
	 * @var User
	 */
    var $user = null;

    /**
	 * This is a reference to the internal session object.
	 *
	 * @access protected
	 * @var Session
	 */
    var $session = '';

    /**
	 * The sessionid from the database
	 *
	 * @access protected
	 * @var int
	 */
    var $sessionid = -1;

    /**
	 * Indicates if the session is active and the user is logged in
	 *
	 * @access protected
	 * @var bool
	 */
    var $active;

    /**
	 * The users id of the user logged in before a new user session was initiated.
	 *
	 * @access protected
	 * @var int
	 */
    var $origUserId;

    /**
	 * Creates a new KTAPI_Session, sets up the internal variables.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
	 * @param User $user Instance of the USER object
	 * @return KTAPI_Session
	 */
	function KTAPI_Session(&$ktapi, &$user)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi,'KTAPI'));
		assert(!is_null($user));
		assert(is_a($user,'User'));

		$this->ktapi=&$ktapi;
		$this->user=&$user;
		$this->origUserId = isset($_SESSION['userID'])?$_SESSION['userID']:null;
		$_SESSION['userID']=$user->getId();
		$this->active = false;
	}

	/**
	 * Returns the internal session object
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return Session
	 */
	function get_session()
	{
		return $this->session;
	}

	/**
	 * This returns the sessionid in the database.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return int
	 */
	function get_sessionid()
	{
		return $this->sessionid;
	}

	/**
	 * Returns the user object
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return User
	 */
	function &get_user()
	{
		 return $this->user;
	}

	/**
	 * Logs the user out of the session. Sets the session userid back to the original userid
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 */
	function logout()
	{
		$_SESSION['userID'] = $this->origUserId;
		$this->active=false;
		// don't need to do anything really
	}

	/**
	 * Checks whether the session is active
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return bool TRUE if active | FALSE if not
	 */
	function is_active()
	{
		return $this->active;
	}

}

/**
 * API for the handling a users session in KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_UserSession extends KTAPI_Session
{

    /**
	 * The users ip address
	 *
	 * @access protected
	 * @var int
	 */
	var $ip = null;

	/**
	 * Create a KTAPI_Session for the current user
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi The KTAPI object
	 * @param USER $user The User object
	 * @param SESSION $session The current session object
	 * @param int $sessionid The id for the current session
	 * @param int $ip The users IP address
	 * @return KTAPI_UserSession
	 */
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
	 * This resolves the user's ip address
	 *
	 * @author KnowledgeTree Team
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
	 * Checks whether a session exists for the given user and creates a new one or updates the existing one.
	 *
	 * @author KnowledgeTree Team
	 * @access protected
	 * @static
	 * @param User $user The User object
	 * @param int $ip The users IP address
	 * @param string $app The originating application type - ws => webservices | webapp => web application | webdav
	 * @return array|PEAR_Error Returns the session string and session id (DB) | a PEAR_Error on failure
	 */
	function _check_session(&$user, $ip, $app)
	{
        $user_id = $user->getId();

        Session::removeStaleSessions($user_id);

        $config = &KTConfig::getSingleton();
		$validateSession = $config->get('webservice/validateSessionCount', false);

		if ($validateSession)
		{
		    $sql = "SELECT count(*) >= u.max_sessions as over_limit FROM active_sessions ass INNER JOIN users u ON ass.user_id=u.id WHERE ass.user_id = $user_id AND ass.apptype = 'webapp'";
		    $row = DBUtil::getOneResult($sql);

		    if (PEAR::isError($row))
		    {
		        return $row;
		    }
		    if (is_null($row))
		    {
		        return new PEAR_Error('No record found for user?');
		    }
		    if ($row['over_limit']+0 == 1)
		    {
		        return new PEAR_Error('Session limit exceeded. Logout of any active sessions.');
		    }
		}

        $session = session_id();
        $newSessionRequired = false;
		if ($app != 'webapp')
		{
            $sql = "select id from active_sessions where user_id=$user_id AND apptype='$app' and ip='$ip'";

            $row = DBUtil::getOneResult($sql);
            if (empty($row))
            {
                $newSessionRequired = true;
            }
            else
            {
                $sessionid = $row['id'];
                $sql = "update active_sessions set session_id='$session' where id=$sessionid";

                DBUtil::runQuery($sql);
            }
		}
		else
		{
		    $newSessionRequired = true;
		}

		if ($newSessionRequired)
		{
		    $sessionid = DBUtil::autoInsert('active_sessions',
    		    array(
	   	          'user_id' => $user_id,
		          'session_id' => session_id(),
		          'lastused' => date('Y-m-d H:i:s'),
		          'ip' => $ip,
		          'apptype'=>$app
		          ));
		    if (PEAR::isError($sessionid) )
		    {
		        return $sessionid;
		    }
		}

        return array($session,$sessionid);
	}


	/**
	 * This returns a session object based on authentication credentials.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @static
	 * @param KTAPI $ktapi Instance of the KTAPI object
	 * @param string $username The users username
	 * @param string $password The users password
	 * @param string $ip Optional. The users IP address - if null, the method will attempt to resolve it
	 * @param string $app Optional. The originating application type - Default is ws => webservices | webapp => The web application
	 * @return KTAPI_Session|PEAR_Error Returns the KATPI_UserSession | a PEAR_Error on failure
	 */
	function &start_session(&$ktapi, $username, $password, $ip=null, $app='ws')
	{
		$this->active=false;
		if ( empty($username) )
		{
			return new PEAR_Error(_kt('The username is empty.'));
		}

		$user =& User::getByUsername($username);
        if (PEAR::isError($user) || ($user === false))
        {
           return new KTAPI_Error(sprintf(_kt("The user '%s' cound not be found.") , $username),$user);
        }

        if ( empty($password) )
        {
        	return new PEAR_Error(_kt('The password is empty.'));
        }

        $authenticated = KTAuthenticationUtil::checkPassword($user, $password);

        if (PEAR::isError($authenticated) || $authenticated === false)
        {
        	$ret=new KTAPI_Error(_kt("The password is invalid."),$authenticated);
        	return $ret;
        }

        if (is_null($ip))
        {
        	//$ip = '127.0.0.1';
        	$ip = KTAPI_UserSession::resolveIP();
        }

        $result = KTAPI_UserSession::_check_session($user, $ip, $app);

        if (PEAR::isError($result))
        {
        	return $result;
        }

        list($session,$sessionid) = $result;

		$session = &new KTAPI_UserSession($ktapi, $user, $session, $sessionid, $ip);

		return $session;
	}

	/**
	 * Returns an active session based on the session string and the ip address if supplied.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
	 * @param string $session The session string
	 * @param string $ip The users ip address
	 * @param string $app Optional. The originating application type - Default is ws => webservices | webapp => The web application
	 * @return KTAPI_Session|PEAR_Error Returns the session object | a PEAR_Error on failure
	 */
	function &get_active_session(&$ktapi, $session, $ip, $app='ws')
	{
		$sql = "SELECT id, user_id FROM active_sessions WHERE session_id='$session' and apptype='$app'";
		if (!empty($ip))
		{
			$sql .= " AND ip='$ip'";
		}

		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			$ret= new KTAPI_Error(KTAPI_ERROR_SESSION_INVALID, $row);
			return $ret;
		}

		$sessionid = $row['id'];
		$userid = $row['user_id'];

		$user = &User::get($userid);
		if (is_null($user) || PEAR::isError($user))
		{
			return new KTAPI_Error(KTAPI_ERROR_USER_INVALID, $user);
		}

        $now=date('Y-m-d H:i:s');
        $sql = "UPDATE active_sessions SET lastused='$now' WHERE id=$sessionid";
        DBUtil::runQuery($sql);

        if ($user->isAnonymous())
			$session = &new KTAPI_AnonymousSession($ktapi, $user, $session, $sessionid, $ip);
        else
			$session = &new KTAPI_UserSession($ktapi, $user, $session, $sessionid, $ip);
		return $session;
	}

	/**
	 * Ends the current session.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return void|PEAR_Error Returns nothing on success | a PEAR_Error on failure
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

/**
 * API for the handling the session for an anonymous user in KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_AnonymousSession extends KTAPI_UserSession
{
    /**
     * Creates a session for an anonymous user
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param KTAPI $ktapi Instance of the KTAPI object
	 * @param string $ip The users ip address
	 * @param string $app Optional. The originating application type - Default is ws => webservices | webapp => The web application
     * @return KTAPI_Session|PEAR_Error Returns a session object | a PEAR_Error on failure
     */
	function &start_session(&$ktapi, $ip=null, $app = 'ws')
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

        list($session,$sessionid) = KTAPI_UserSession::_check_session($user, $ip, $app);
        if (PEAR::isError($sessionid))
        {
        	return $sessionid;
        }

		$session = &new KTAPI_AnonymousSession($ktapi, $user, $session, $sessionid, $ip);

		return $session;
	}
}

/**
 * API for the handling the system in KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_SystemSession extends KTAPI_Session
{
    /**
     * Creates a system session
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param KTAPI $ktapi Instance of the KTAPI object
     * @param USER $user Instance of the user object
     * @return KTAPI_SystemSession
     */
	function KTAPI_SystemSession(&$ktapi, &$user)
	{
		parent::KTAPI_Session($ktapi, $user);
		$this->active=true;
	}
}

?>

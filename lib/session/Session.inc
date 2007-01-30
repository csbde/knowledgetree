<?php
/**
 * $Id$
 *
 * Session management class.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 * @version $Revision$
 */ 

class Session {
    var $_bNotEmpty = true;

    /**
     * Creates a session.
     *
     * @param int the id of the user to create a session for
     * @return string the generated sessionID
     */
    function create(&$oUser) {
        $iUserId = $oUser->getId();
        global $default;
        
        session_start();

        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            if (!$oUser->isAnonymous()) {
                require_once(KT_DIR .  '/plugins/wintools/baobabkeyutil.inc.php');
                $res = BaobabKeyUtil::isBaobabUser($oUser);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }
        
        if ($oUser->getDisabled()) {
            return PEAR::raiseError(_kt("Your account has been disabled.  Please contact the system administrator for assistance."));
        }
        
        // bind user id to session
        $_SESSION["userID"] = $iUserId;
        $_SESSION["KTErrorMessage"] = array();
        
        // use the PHP generated session id
        $sessionID = session_id();

        // retrieve client ip
        $ip = $this->getClientIP();

        $default->log->debug("Session::create() new session for $iUserId, from $ip, sessionID=$sessionID");
        
        // insert session information into db
        $aParams = array(
            'session_id' => $sessionID,
            'user_id' => $iUserId,
            'lastused' => date("Y-m-d H:i:s", time()),
            'ip' => $ip,
        );

        $result = DBUtil::autoInsert($default->sessions_table, $aParams);
        if (PEAR::isError($result)) {
            die("Error creating session: " . $result->toString());
        }
        $_SESSION['sessionID'] = $result;

        $aParams = array(
            'userid' => $iUserId,
            'datetime' => date("Y-m-d H:i:s", time()),
            'actionnamespace' => 'ktcore.user_history.login',
            'comments' => sprintf('Logged in from %s', $ip),
            'sessionid' => $_SESSION['sessionID'],
        );
        require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
        $res = KTUserHistory::createFromArray($aParams);

        $oUser->setLastLogin(getCurrentDateTime());
        $oUser->update();

        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForUser($oUser);
        $oProvider->login($oUser);
        
        return $sessionID;
    }
    
    /**
     * Destroys the current session.
     */
    function destroy() {
        global $default;

        session_start();
        $sSessionID = session_id();
        $iUserId = $_SESSION["userID"];
        
        // remove the session information from the database

		$sTable = KTUtil::getTableName('sessions');
		$res = DBUtil::whereDelete($sTable, array('session_id' => $sSessionID));

        $aParams = array(
            'userid' => $iUserId,
            'datetime' => date("Y-m-d H:i:s", time()),
            'actionnamespace' => 'ktcore.user_history.logout',
            'sessionid' => $_SESSION['sessonID'],
        );
        require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
        $res = KTUserHistory::createFromArray($aParams);

        // remove the php4 session
		unset($_SESSION['userID']);
		unset($_SESSION['sessionStatus']);
        session_unset();
        session_destroy();	
    }
    
    /**
     * Removes any stale sessions for the specified userID
     *
     * @param int the userID to remove stale sessions for
     */
    function removeStaleSessions() {
        global $default;
        $time = time() - $default->sessionTimeout;

        $sTable = KTUtil::getTableName('sessions');
        $aQuery = array(
            sprintf('SELECT id, lastused, user_id FROM %s WHERE lastused <= ?', $sTable),
            array(formatDateTime($time)),
        );

        $aSessions = DBUtil::getResultArray($aQuery);

        foreach ($aSessions as $aSessionData) {
            $iId = $aSessionData['id'];
            $dLastUsed = $aSessionData['lastused'];
            $iUserId = $aSessionData['user_id'];
            $iTime = strtotime($dLastUsed);
            $iTime = $iTime + $default->sessionTimeout;
            $aParams = array(
                'userid' => $iUserId,
                'datetime' => formatDateTime($iTime),
                'actionnamespace' => 'ktcore.user_history.timeout',
                'comments' => 'Session timed out',
                'sessionid' => $_SESSION['sessionID'],
            );
            require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
            $res = KTUserHistory::createFromArray($aParams);

            DBUtil::whereDelete($sTable, array('id' => $iId));
        }
    }

    /**
     * Used to verify the current user's session.
     */
    function verify() {
        global $default;

        // this is a workaround for an SSL download bug with IE.
        session_cache_limiter('none');
        session_start();
        header("Cache-Control: must-revalidate");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() - 3600) . " GMT");
        $sessionID = session_id();
        $version = KTUtil::getSystemSetting('databaseVersion');

        if ($default->systemVersion != $version) {
            $default->log->info("Session::verify : Database not upgraded");
            return PEAR::raiseError(sprintf(_kt('Incompatible database version (%s, expected version %s) - contact the administrator'), $version, $default->systemVersion));
        }

        if (empty($sessionID)) {
            $default->log->info("Session::verify session not in db");
            return PEAR::raiseError(_kt('You need to login to access this page'));
        }

        // this should be an existing session, so check the db
        $aRows = DBUtil::getResultArray(array("SELECT * FROM $default->sessions_table WHERE session_id = ?", $sessionID));
				
        $numrows = count($aRows);

        // FIXME: if there aren't more rows that the max sessions for this user
        if ($numrows < 1) {
            // the session doesn't exist in the db
            $default->log->info("Session::verify sessionID=$sessionID, not in db");
            return PEAR::raiseError(_kt('You need to login to access this page'));
            return false;
        }

        $default->log->debug("Session::verify found session in db");
        $aRow = $aRows[0];

        $iUserID = $aRow["user_id"];
		
        $oKTConfig = KTConfig::getSingleton();	
        $allowAnon = $oKTConfig->get('session/allowAnonymousLogin', false);
		
        $ANON = -2;
        if ((!$allowAnon) && ($iUserID == $ANON)) { 
            Session::destroy();   // delete the anonymous session - config.ini has changed under this session.
            return PEAR::raiseError(_kt("Anonymous logins are no longer allowed by the system administrator.  Please login."));
        }

        $ipTracking = $oKTConfig->get('session/ipTracking', false);
        // check that ip matches
        $ip = $this->getClientIP();
        if ($ipTracking && ($ip != trim($aRow["ip"]))) {
            return PEAR::raiseError(_kt("You are coming from a different IP address than the session requires"));
        }
        
        if (empty($_SESSION['userID'])) {
            Session::removeStaleSessions();

            return PEAR::raiseError(_kt('Session timed out'));
        }

        // now check if the timeout has been exceeded
        $lastused = $aRow["lastused"];
        $diff = time() - strtotime($lastused);
        if($diff <= $default->sessionTimeout) {
            // update last used timestamp
            $aFV = array(
                'lastused' => getCurrentDateTime(),
            );
            $aWFV = array(
                'user_id' => $iUserID,
                'session_id' => $sessionID,
            );
            $res = DBUtil::whereUpdate($default->sessions_table, $aFV, $aWFV);
            // add the array to the session
            $_SESSION["sessionStatus"] = $sessionStatus;

            Session::removeStaleSessions();

            return true;
        } else {

            Session::removeStaleSessions();

            return PEAR::raiseError(_kt('Session timed out'));
        }

        Session::removeStaleSessions();
        
        return false;
    }
    
    /**
     * Retrieves and returns the IP address of the current user
     */
    function getClientIP() {
        // get client ip
        if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        return $ip;
    }
}
?>

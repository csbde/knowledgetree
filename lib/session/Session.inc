<?php
/**
 * $Id$
 *
 * Session management class.
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
 * Contributor( s): ______________________________________
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


        // Don't need to lock a user out the web interface if KT Tools exists and has no license.
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            if (!$oUser->isAnonymous()) {
                $path = KTPluginUtil::getPluginPath('ktdms.wintools');
                require_once($path .  'baobabkeyutil.inc.php');
                $res = BaobabKeyUtil::isValidUser($oUser);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
        }

        if ($oUser->getDisabled() == 1) {
            return PEAR::raiseError(_kt("Your account has been disabled.  Please contact the system administrator for assistance."));
        }

        // bind user id to session
        $_SESSION["userID"] = $iUserId;
        $_SESSION["KTErrorMessage"] = array();

        // use the PHP generated session id
        $sessionID = session_id();

        // retrieve client ip
        $ip = $this->getClientIP();

        if (KTLOG_CACHE) $default->log->debug("Session::create() new session for $iUserId, from $ip, sessionID=$sessionID");

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
    function removeStaleSessions($iUserId = null) {
        global $default;
        $sessionTimeout = $default->sessionTimeout;
        $time = time() - $sessionTimeout;

        // for web service sessions, we will expire after a month.
        $monthPeriod = 30 * 24 * 60 * 60;
        $wsTimeout = time() - $monthPeriod;

        if (is_null($iUserId))
        {
            $iUserId = $_SESSION['userID'];
            // if the userid cannot be resolved, we will cleanup the entire sessions table
            if (empty($iUserId))
            {
                $iUserId = 0;
            }
        }

        $sTable = KTUtil::getTableName('sessions');
        $mintime = formatDateTime($time);
        $mintime2 = formatDateTime($wsTimeout);
        $aQuery = array(
            sprintf("SELECT id, user_id, lastused, apptype FROM %s WHERE (user_id = $iUserId OR $iUserId = 0) AND ( (lastused <= '$mintime' and apptype = 'webapp') or (lastused <= '$mintime2' and apptype !='webapp') )", $sTable)
        );

        $aSessions = DBUtil::getResultArray($aQuery);
        $sSessionId = session_id();

        $deleteIds = array();

        foreach ($aSessions as $aSessionData) {
            $iId = $aSessionData['id'];
            $user_id = $aSessionData['user_id'];
            $app_type = $aSessionData['apptype'];
            $dLastUsed = $aSessionData['lastused'];
            $iTime = strtotime($dLastUsed);

            $timeoutPeriod = ($app_type != 'webapp') ? $monthPeriod : $sessionTimeout;
            $iTime = $iTime + (int)$timeoutPeriod;
            $aParams = array(
                'userid' => $user_id,
                'datetime' => formatDateTime($iTime),
                'actionnamespace' => 'ktcore.user_history.timeout',
                'comments' => 'Session timed out',
                'sessionid' => $sSessionId,
            );
            require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
            $res = KTUserHistory::createFromArray($aParams);

            $deleteIds[] = $iId;
        }

        if (!empty($deleteIds))
        {
            $deleteIds = implode(',',$deleteIds);
            $sql = "delete from active_sessions where id in ($deleteIds)";
            DBUtil::runQuery($sql);
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

        if($default->dbversioncompare){
            // Compare the system version and the database version to determine if the database needs to be upgraded.
            $version = KTUtil::getSystemSetting('databaseVersion');

            if (trim($default->systemVersion) != trim($version)) {
                if (KTLOG_CACHE) $default->log->info("Session::verify : Database not upgraded");
                $_SESSION['errormessage']['login'] = sprintf(_kt('Database incompatibility error: <br> Please ensure that you have completed the database upgrade procedure. <br> Please <a href=%s>click here</a> to complete.'),'setup/upgrade.php');
                return PEAR::raiseError($_SESSION['errormessage']['login']);
            }
        }

        if (empty($sessionID)) {
            if (KTLOG_CACHE) $default->log->info("Session::verify session not in db");
            return PEAR::raiseError(_kt('You need to login to access this page'));
        }

        // this should be an existing session, so check the db
        $aRows = DBUtil::getResultArray(array("SELECT * FROM $default->sessions_table WHERE session_id = ? ORDER BY id DESC", $sessionID));

        $numrows = count($aRows);

        // FIXME: if there aren't more rows that the max sessions for this user
        if ($numrows < 1) {
            // the session doesn't exist in the db
            if (KTLOG_CACHE)   $default->log->info("Session::verify sessionID=$sessionID, not in db");
            return PEAR::raiseError(_kt('You need to login to access this page'));
            return false;
        }

        if (KTLOG_CACHE) $default->log->debug("Session::verify found session in db");
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
        
        //To make sure the sessionTimeout setting is nonzero
       	if($default->sessionTimeout<=0)$default->sessionTimeout=1200;
       	
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

<?php
/**
 * $Id$
 *
 * Session management class.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.session
 */ 
class Session {

    /**
     * Creates a session.
     *
     * @param int the id of the user to create a session for
     * @return string the generated sessionID
     */
	function create($iUserID) {
        global $default;
        
        session_start();
        
        // bind user id to session
        $_SESSION["userID"] = $iUserID;
        
        // use the PHP generated session id
        $sessionID = session_id();

        // retrieve client ip
        $ip = $this->getClientIP();

        $default->log->debug("Session::create() new session for $iUserID, from $ip, sessionID=$sessionID");
        
        // insert session information into db
        $sql = $default->db;
        $query = "INSERT INTO $default->sessions_table (session_id, user_id, lastused, ip) VALUES ('$sessionID', $iUserID, '" . date("Y-m-d H:i:s", time()) . "', '$ip')";

        $result = $sql->query($query);        
        if(!$result) {
            die("$lang_err_sess_write");
        }

		return $sessionID;
	}
    
    /**
     * Destroys the current session.
     */
    function destroy() {
        global $default;

        session_start();
        $sSessionID = session_id();
        $iUserID = $_SESSION["userID"];
        
        // remove the session information from the database
        $sql = $default->db;
        $query = "DELETE FROM $default->sessions_table WHERE session_id = '$sSessionID'" . ($iUserID ? " AND user_id=$iUserID" : "");
        $default->log->info("Session::destroy $query");
        $sql->query($query);

        // remove the php4 session
        session_unset();
        session_destroy();
    }
    
    /**
     * Removes any stale sessions for the specified userID
     *
     * @param int the userID to remove stale sessions for
     */
    function removeStaleSessions($userID = -1) {
        global $default;
        // deletes any sessions for this userID where the default timeout has elapsed.
        $time = time() -  $default->sessionTimeout;
        $sql = $default->db;
        $sQuery = "DELETE FROM $default->sessions_table WHERE " . (($userID != -1) ? "user_id=$userID AND " : "") . "lastused <= '" . formatDateTime($time) . "'";
        $sql->query($sQuery);
    }

    /**
     * Used to verify the current user's session.
     *
	 * @param boolean optional parameter set if we're downloading a file     
     * @return int session verification status
     */
    function verify($bDownload = false) {
        global $default, $lang_sesstimeout, $lang_sessinuse, $lang_err_sess_notvalid;
        // this is a workaround for an SSL download bug with IE.
        if ($bDownload) {
        	session_cache_limiter('public');
        }
        session_start();
        $sessionID = session_id();
        if (strlen($sessionID) > 0) {
            // initialise return status
            $sessionStatus = 0;
            
            // this should be an existing session, so check the db
            $sql = $default->db; 
            $sql->query("SELECT * FROM $default->sessions_table WHERE session_id = '$sessionID'");
            $numrows = $sql->num_rows($sql);

            // FIXME: if there aren't more rows that the max sessions for this user
            if ($numrows >= 1) {
                $default->log->debug("Session::verify found session in db");
                while($sql->next_record()) {
                    $iUserID = $sql->f("user_id");
                    $ip = $this->getClientIP();
                    // check that ip matches
                    if ($ip == $sql->f("ip")) {
                        // now check if the timeout has been exceeded
                        $lastused = $sql->f("lastused");
                        $diff = time() - strtotime($lastused);
                        if($diff <= $default->sessionTimeout) {
                            // session has been verified, update status
                            $sessionStatus = 1;
                            // use userID to refresh user details and set on session
                            
                            // ??: will this change during a user session?
                            // only set the userID if its not in the array already 
                            if (!$_SESSION["userID"]) {
                                $_SESSION["userID"] = $iUserID;
                            }
                            
                            // update last used timestamp
                            $sql->query("UPDATE $default->sessions_table SET lastused = '" . getCurrentDateTime() ."' " .
                                        "WHERE user_id = $iUserID AND session_id = '$sessionID'");
                            // add the array to the session
                            $_SESSION["sessionStatus"] = $sessionStatus;
                        } else {
                            // session timed out status
                            $sessionStatus = 2;
                            // destroy this session
                            $this->destroy();
                            $_SESSION["errorMessage"] = $lang_sesstimeout;
                        }
                    } else {
                        // session in use status
                        $sessionStatus = 3;
                        $_SESSION["errorMessage"] = $lang_sessinuse;
                    }
                }
            } else {
                // the session doesn't exist in the db
                $default->log->info("Session::verify sessionID=$sessionID, not in db");
                $sessionStatus = false;
            }
        } else {
            $default->log->info("Session::verify session not in db");
            // there is no session
            $sessionStatus = false;
        }
        
        // remove old sessions
        Session::removeStaleSessions();
                
        // return the status
        return $sessionStatus;
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

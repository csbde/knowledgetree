<?php
/**
 * $Id$
 *
 * This class is used for session management.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @version $Revision$
 * @package lib.session
 */
class Session {

    /**
     * Creates a session.
     *
     * @param int the id of the user to create a session for
     * @return string the generated sessionID
     */
	function create($userID) {
        global $default;
        
        session_start();
        
        // bind user id to session
        $_SESSION["userID"] = $userID;
        
        // use the PHP generated session id
        $sessionID = session_id();

        // retrieve client ip
        $ip = $this->getClientIP();

        $default->log->debug("Session::create() new session for $userID, from $ip, sessionID=$sessionID");
        
        // insert session information into db
        $sql = $default->db;
        $query = "INSERT INTO $default->owl_sessions_table (session_id, user_id, lastused, ip) VALUES ('$sessionID', '$userID', '" . date("Y-m-d H:i:s", time()) . "', '$ip')";

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
        // remove the session information from the database
        $sql = $default->db;
        $query = "DELETE FROM $default->owl_sessions_table WHERE session_id = '" . session_id() . "'";
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
    function removeStaleSessions($userID) {
        global $default;
        // deletes any sessions for this userID where the default timeout has elapsed.
        $time = time() -  $default->owl_timeout;
        $sql = $default->db;
        $sql->query("DELETE FROM $default->owl_sessions_table WHERE user_id = '" . $userID . "' AND lastused <= '" . formatDateTime($time) . "'");
    }

    /**
     * Used to verify the current user's session.
     *
     * @return int session verification status
     */
    function verify() {
        global $default, $lang_sesstimeout, $lang_sessinuse, $lang_err_sess_notvalid;
        
        session_start();
        $sessionID = session_id();
        $default->log->debug("Session::verify() retrieved sessionID=$sessionID");
        if (strlen($sessionID) > 0) {
            // initialise return status
            $sessionStatus = 0;
            
            // this should be an existing session, so check the db
            $sql = $default->db; 
            $sql->query("SELECT * FROM $default->owl_sessions_table WHERE session_id = '$sessionID'");
            $numrows = $sql->num_rows($sql);

            // FIXME: if there aren't more rows that the max sessions for this user
            if ($numrows >= 1) {
                $userID = $sql->f("user_id");
                $default->log->debug("Session::verify() found session in db");
                while($sql->next_record()) {
                    $ip = $this->getClientIP();
                    // check that ip matches
                    if ($ip == $sql->f("ip")) {
                        // now check if the timeout has been exceeded
                        $lastused = $sql->f("lastused");
                        $default->log->debug("Session::verify() lastused=$lastused; str=" . strtotime($lastused));
                        $default->log->debug("Session::verify() current time=" . time());
                        $diff = time() - strtotime($lastused);
                        $default->log->debug("Session::verify() timeout = " . $default->owl_timeout . "; diff=$diff");                        
                        if($diff <= $default->owl_timeout) {
                            // session has been verified, update status
                            $sessionStatus = 1;
                            // use userID to refresh user details and set on session
                            
                            // ??: will this change during a user session?
                            // only set the userID if its not in the array already 
                            if (!$_SESSION["userID"]) {
                                $_SESSION["userID"] = $sql->f("user_id");
                            }
                            
                            // update last used timestamp
                            $sql->query("UPDATE $default->owl_sessions_table SET lastused = '" . getCurrentDateTime() ."' " .
                                        "WHERE user_id = " . $_SESSION["userID"] . " AND session_id = '$sessionID'");
                            // add the array to the session
                            $_SESSION["sessionStatus"] = $sessionStatus;
                        } else {
                            // session timed out status
                            $sessionStatus = 2;
                            // destroy this session
                            $this->destroy();
                            // remove old sessions
                            Session::removeStaleSessions($userID);
                            $_SESSION["errorMessage"] = $lang_sesstimeout;
                        }
                    } else {
                        // session in use status
                        $sessionStatus = 3;
                        $_SESSION["errorMessage"] = $lang_sessinuse;
                    }
                }
            }                         
        } else {
            $default->log->error("verify() session not in db");
            // there is no session
            return false;
        }
        // return the array
        $default->log->debug("Session::verify() returning sessionStatus[\"status\"]=" . $sessionStatus);
        return $sessionStatus;
    }
    
    /**
     * Retrieves and returns the IP address of the current user
     */
    function getClientIP() {
        // get client ip 
        if(getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } else {
            $ip = getenv("REMOTE_ADDR");
        }
        return $ip;
    }    
}
?>

<?php
/**
 * $Id$
 *
 * This class is used for session management.
 *
 * @author owl sourceforge team
 * @version $Revision$
 * @package Owl
 */
class Session {

    /**
     * Creates a session.
     *
     * @param $userDetails    array containing user details
     * @return returns the generated sessionID
     */
	function create($userDetails) {
        global $default;
        
        session_start();
        
        // bind userID to session
        $_SESSION["userID"] = $userDetails["userID"];
        
        // lookup group id and add to session
        $_SESSION["groupID"] = $userDetails["groupID"];
        $_SESSION["unitID"] = $userDetails["unitID"];
        $_SESSION["username"] = $userDetails["username"];
        
        $default->log->debug("Session::create session variables=" . arrayToString($_SESSION));
        
        // use the PHP generated session id
        $sessionID = session_id();
        
        // retrieve client ip
        $ip = $this->getClientIP();
        
        // insert session information into db
        $sql = new Owl_DB;
        $query = "insert into $default->owl_sessions_table (session_id, user_id, lastused, ip) values ('$sessionID', '$userID', '" . date("Y-m-d H:i:s", time()) . "', '$ip')";
        //echo "query=$query<br>";
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
        $sql = new Owl_DB;
        $query = "delete from $default->owl_sessions_table where session_id = '" . session_id() . "'";
        $sql->query($query);

        // remove the php4 session
        session_unset();
        session_destroy();
    }
    
    /**
     * Removes any stale sessions for the specified userID
     *
     * @param userID
     *        the userID to remove stale sessions for
     */
    function removeStaleSessions($userID) {
        global $default;
        // deletes any sessions for this userID where the default timeout has elapsed.
        $time = time() -  $default->owl_timeout;
        $sql = new Owl_DB;
        $sql->query("delete from $default->owl_sessions_table where user_id = '" . $userID . "' and lastused <= '" . date("Y-m-d H:i:s",$time) . "'");
    }

    /**
     * Used to verify the current user's session.
     *
     * @return 
     *        array containing the userID, groupID and session verification status
     */
    function verify() {
        global $default, $lang_sesstimeout, $lang_sessinuse, $lang_err_sess_notvalid;
        
        session_start();
        $sessionID = session_id();
        $default->log->debug("Session::verify retrieved sessionID=$sessionID");
        if (strlen($sessionID) > 0) {
        
            // initialise return status
            $sessionStatus["status"] = 0;
            
            // this should be an existing session, so check the db
            $sql = new Owl_DB; 
            $sql->query("select * from $default->owl_sessions_table where session_id = '$sessionID'");
            $numrows = $sql->num_rows($sql);
            
            // found one match
            if ($numrows == 1) {
                $default->log->debug("Session::verify found session in db");
                while($sql->next_record()) {
                    $ip = $this->getClientIP();
                    // check that ip matches
                    if ($ip == $sql->f("ip")) {
                        // now check if the timeout has been exceeded
                        $lastused = $sql->f("lastused");
                        $default->log->debug("Session::verify lastused=$lastused; str=" . strtotime($lastused));
                        $default->log->debug("Session::verify current time=" . time());
                        $diff = time() - strtotime($lastused);
                        $default->log->debug("Session::verify timeout = " . $default->owl_timeout . "; diff=$diff");                        
                        if((time() - strtotime($lastused)) <= $default->owl_timeout) {
                            // session has been verified, update status
                            $sessionStatus["status"] = 1;
                            // only set the userID if its not in the array already 
                            if (!$sessionStatus["userID"]) {
                                $sessionStatus["userID"] = $sql->f("user_id");
                            }
                            // lookup the user                            
                            $sql->query("select * from $default->owl_users_table where id = '".$sessionStatus["userid"]."'");
                            while($sql->next_record()) {
                                // FIXME: this much change to look at users_groups_link
                                // only set the groupID if its not in the array already
                                if (!$sessionStatus["groupID"]) {
                                    $sessionStatus["groupID"] = $sql->f("group_id");
                                }
                            }
                            // update last used timestamps
                            $sql->query("update $default->owl_sessions_table set lastused = '" . date("Y-m-d H:i:s",time()) ."' where user_id = '" . $sessionStatus["userID"] . "'");
                            // add the array to the session
                            $_SESSION["sessionStatus"] = $sessionStatus;
                        } else {
                            // session timed out status
                            $sessionStatus["status"] = 2;
                            $default->errorMessage = $lang_sesstimeout;
                        }
                    } else {
                        // session in use status
                        $sessionStatus["status"] = 3;
                        $default->errorMessage = $lang_sessinuse;
                    }
                }
            }                         
        } else {
            $default->log->error("Session::verify session not in db");
            // there is no session
            return false;
        }
        // return the array
        $output = "Session::verify returning sessionStatus[\"status\"]=" . $sessionStatus["status"];
        $default->log->debug($output);
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

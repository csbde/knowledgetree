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
     * @param $userID
     *	      user identifier
     * @return $sessionID
     * 	      returns the generated sessionID
     */
	function create($userID) {
        global $default;
        
        session_start();
        
        // bind userID to session
        $_SESSION['userID'] = $userID;
        
        // lookup group id and add to session
        //$_SESSION['groupID'] = lookupGroupID($userID);
        $_SESSION['groupID'] = owlusergroup($userID);
        
        // use the PHP generated session id
        $sessionID = session_id();
        
        // retrieve client ip
        if(getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } else {
            $ip = getenv("REMOTE_ADDR");
        }
        
        $current = time();
        
        // insert session information into db
        $sql = new Owl_DB;
        $query = "insert into $default->owl_sessions_table (id, user_id, lastused, ip) values ('$sessionID', '$userID', '" . date("Y-m-d H:i:s",$current) . "', '$ip')";
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
        $query = "delete from $default->owl_sessions_table where id = '" . session_id() . "'";
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
        $sql->query("delete from $default->owl_sessions_table where user_id = '" . $userID . "' and lastused <= $time ");
    }
    
    /**
     * Used to verify the current user's session.
     *
     * @return 
     *        array containing the userID, groupID and session verification status
     */
    function verify() {       
        getprefs();
        global $default, $lang_sesstimeout, $lang_sessinuse, $lang_clicklogin;
        session_start();
        
        $sessionID = session_id();
        
        // initialise return status
        $verified["status"] = 0;
        
        // this should be an existing session, so check the db
        $sql = new Owl_DB; 
        $sql->query("select * from $default->owl_sessions_table where id = '$sessionID'");
        $numrows = $sql->num_rows($sql);
        $time = time();
        
        if ($numrows == "1") {
            while($sql->next_record()) {
                // get client ip 
                if(getenv("HTTP_CLIENT_IP")) {
                    $ip = getenv("HTTP_CLIENT_IP");
                } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
                    $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
                    list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
                } else {
                    $ip = getenv("REMOTE_ADDR");
                }
                
                // check that ip matches
                if ($ip == $sql->f("ip")) {
                    // if timeout not exceeded
                    if(($time - strtotime($sql->f("lastused"))) <= $default->owl_timeout) {
                        // set verified status
                        $verified["status"] = 1;
                        // update userID? this should be the same value on the session
                        $verified["userID"] = $sql->f("user_id");
                        $sql->query("select * from $default->owl_users_table where id = '".$verified["userid"]."'");
                        while($sql->next_record()) {
                            $verified["groupID"] = $sql->f("groupid");
                        }
                        // session verified, so update last user time
                        $lastused = time();
                        $userID = $sessionStatus["userID"];
                        $sql->query("update $default->owl_sessions_table set lastused = '$lastused' where user_id = '$userID'");

                    } else {
                        // session timed out status
                        $verified["status"] = 2;
                        $default->errorMessage = $lang_sesstimeout;
                    }
                } else {
                    // session in use status
                    $verified["status"] = 3;
                    $default->errorMessage = $lang_sessinuse;
                }
            }
        }
        // add this array to the session
        session_register($sessionStatus);
        
        // also return the array for good measure
        return $verified;
    }    
}
?>

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
        
		// create the session id from a md5 of the current time
        $current = time();
        //$random = $this->sessionID . $current;
        $sessionID = md5($current);
        $sql = new Owl_DB;
        
        // retrieve client ip
        if(getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
            $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } else {
            $ip = getenv("REMOTE_ADDR");
        }

        // insert session information into db
        $result = $sql->query("insert into $default->owl_sessions_table values ('$sessionID', '$userID', '$current', '$ip')");
        
        if(!'result') {
            die("$lang_err_sess_write");
        }

		return $sessionID;
	}
    
    /**
     * Removes the specified session from the application.
     *
     * @param sessionID
     *        the session to remove
     */
    function remove($sessionID) {
        $sql = new Owl_DB;
        $sql->query("delete from $default->owl_sessions_table where sessid = '$sessionID'");        
    }
    
    /**
     * Removes any stale sessions for the specified userID
     *
     * @param userID
     *        the userID to remove stale sessions for
     */
    function removeStateSessions($userID) {
        $time = time() -  $default->owl_timeout;
        $sql = new Owl_DB;
        $sql->query("delete from $default->owl_sessions_table where uid = '" . $userID . "' and lastused <= $time ");
    }
    
    /**
     * Used to verify a users session
     *
     * @param $sessionID
     *	      The session id to verify
     * @return 
     *        array containing the userID, groupID and session verifiction status
     */
    function verify($sessionID) {
        
        getprefs();
        global $default, $lang_sesstimeout, $lang_sessinuse, $lang_clicklogin;
        $sess = ltrim($sessionID);
        // initialise return status
        $verified["status"] = 0;
        
        // this should be an existing session, so check the db
        $sql = new Owl_DB; 
        $sql->query("select * from $default->owl_sessions_table where sessid = '$sessionID'");
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
                    if(($time - $sql->f("lastused")) <= $default->owl_timeout) {
                        $verified["status"] = 1;
                        $verified["userID"] = $sql->f("uid");
                        $sql->query("select * from $default->owl_users_table where id = '".$verified["userid"]."'");
                        while($sql->next_record()) {
                            $verified["groupID"] = $sql->f("groupid");
                        }
                    } else {
                        // TODO: don't want html here
                        // session time out status
                        $verified["status"] = 2;
                        /*
                        // Bozz Bug Fix begin
                        if (file_exists("./lib/header.inc")) {
                            include("./lib/header.inc");
                        } else {
                            include("../lib/header.inc");
                        }
                        // Bozz Bug Fix End
                        print("<BR><BR><CENTER>".$lang_sesstimeout);
                        if ($parent == "" || $fileid == "") {
                            print("<A HREF='$default->owl_root_url/index.php'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
                        } else {
                            print("<A HREF='$default->owl_root_url/index.php?parent=$parent&fileid=$fileid'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
                        }
                        exit();*/
                    }
                } else {
                    // session in use status
                    $verified["status"] = 3;
                    /*
                    // Bozz Bug Fix begin
                    if (file_exists("./lib/header.inc")) {
                        include("./lib/header.inc");
                    } else {
                        include("../lib/header.inc");
                    }
                    // Bozz Bug Fix End
                    print("<BR><BR><CENTER>".$lang_sessinuse);
                    exit;
                    */
                }
            }
        }
        return $verified;
    }    
}
?>

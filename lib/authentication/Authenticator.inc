<?php

/**
 * $Id$
 * 
 * Interface class that performs all authentication functions.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication 
 */
class Authenticator {
    
    /**
     * Verifies the login credentials
     *
     * @param string the user name of the user logging in
     * @param string the user's password
     * @return array containing userID and authentication status code
     */
    function login($userName, $password) {
        global $default, $lang_err_database;
        // initialise return array
        $userDetails = array();
        
        if ($this->checkPassword($userName, $password)) {
            // retrieve the userID
            $userID = lookupID($default->owl_users_table, "username", "$userName");
            $default->log->info("Authenticator::login authenticated user, id=$userID");
            if ($userID) {
                // add this to the return array
                $userDetails["userID"] = $userID;
                // remove stale sessions from the database for the user
                // that is signing on.
                Session::removeStaleSessions($userDetails["userID"]);
                
                // Check if Maxsessions has been reached
                // lookup maxsessions
                $maxSessions = lookupField($default->owl_users_table, "max_sessions", "id", $userID);
                $sql = $default->db;
                if ($sql->query("SELECT * FROM $default->owl_sessions_table WHERE user_id = '".$userDetails["user_id"]."'")) {
                    if ($sql->num_rows($sql) >= $maxSessions) {
                        // return too many sessions status code
                        $userDetails["status"] = 3;
                    } else {
                        // authenticated successfully
                        $userDetails["status"] = 1;
                    }
                    // FIXME: account disabled status???
                } else {
                    // db access failed
                    $_SESSION["errorMessage"] = $lang_err_database;
                    $userDetails["status"] = 0;
                }
            } else {
                // db access failed
                $_SESSION["errorMessage"] = $lang_err_database;
                $userDetails["status"] = 0;
            }
        } else {
            // authentication failed
            $userDetails["status"] = 0;
        }
        return $userDetails;
    }
    
    /**
     * Logs the user out of the application
     *
     * @param int the ID of user logging out
     * @param string the user's sessionID
     */
     function logout($userID, $sessionID) {
         // remove session from db
         Session::destroy($sessionID);
     }
     
     /**
      * [Abstract] Checks the user's password
      *
      * @param string the name of the user to check
      * @param string the password to check
      * @return boolean true if the password is correct, else false
      */
     function checkPassword($userName, $password) {
     }
}
?>
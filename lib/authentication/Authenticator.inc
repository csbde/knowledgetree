<?php

//require_once("$default->owl_fs_root/lib/administration/UserManager.inc");

/**
 * $Id$
 * 
 * Interface class that performs all authentication functions.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com">Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib 
 */
class Authenticator {
    
    /**
     * Verifies the login credentials
     *
     * @param userName  
     *        the user name of the user logging in
     * @param password  
     *        the user's password
     * @return array containing user details (userName, userID, groupID)
     *         and authentication status code
     */
    function login($userName, $password) {
        // TODO: create session, add user details to the session
        global $default, $lang_err_database;
        if ($this->checkPassword($userName, $password)) {
            // retrieve user details from the database and return
            /*
            $userID = lookupID($default->owl_users_table, "username", "'$userName'");
            $userDetails = UserManager::getUserDetails($userID);
            if (!$userDetails) {
                // we don't have a session yet, so return a general error message
                $userDetails["status"] = -1;
            }
            */
            // FIXME: remove when user manager method coded
            $sql = new Owl_DB(); 
            $query = "select * from $default->owl_users_table where username = '$userName'";
            $sql->query($query);
            $numrows = $sql->num_rows($sql);
            if ($numrows == "1") {
                while($sql->next_record()) {
                    if ( $sql->f("disabled") == 1 ) {  
                        $userDetails["status"]       = 2;
                    } else {
                        $userDetails["status"]       = 1;
                        $userDetails["userID"]      = $sql->f("id");
                        $userDetails["username"]     = $sql->f("username");
                        $userDetails["max_sessions"] = $sql->f("max_sessions") + 1;
                    }
                }
                
                // retrieve user groups
                $sql = new Owl_DB; 
                $query = "select group_id from $default->owl_users_groups_table where user_id = " . $userDetails["userID"];
                $sql->query($query);
                $userDetails["groupID"] = array();
                while($sql->next_record()) {
                    $userDetails["groupID"][] = $sql->f("group_id");
                    if (!isset($userDetails["unitID"])) {
                        $userDetails["unitID"] = lookupID($default->owl_groups_units_table, "group_id", $sql->f("group_id"));
                        $userDetails["organisationID"] = lookupField($default->owl_units_table, "organisation_id", "id", $userDetails["unitID"]);
                    }
                }
            // FIXME: remove when user manager method coded
                    
                // remove stale sessions from the database for the user
                // that is signing on.
                Session::removeStaleSessions($userDetails["userID"]);
                
                
                // Check if Maxsessions has been reached
                $sql = new Owl_DB;
                if ($sql->query("SELECT * FROM $default->owl_sessions_table WHERE user_id = '".$userDetails["user_id"]."'")) {
                    if ($sql->num_rows($sql) >= $userDetails["max_sessions"]) {
                        // FIXME: change for multiple groups
                        if ( $userDetails["groupID"] == 0) {
                            // ignore maxsessions check for admin group
                            $userDetails["status"] = 1;
                        } else {
                            // return too many sessions status code
                            $userDetails["status"] = 3;
                        }
                    }
                } else {
                    $_SESSION["errorMessage"] = $lang_err_database;
                }
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
     * @param userID  
     *        the ID of user logging out
     * @param sessionID
     *        the user's sessionID
     */
     function logout($userID, $sessionID) {
         // remove session from db
         Session::destroy($sessionID);
     }
     
     /**
      * [Abstract] Checks the user's password
      *
      * @param $userName
      *        the name of the user to check
      * @param $password
      *        the password to check
      * @return boolean true if the password is correct, else false
      */
     function checkPassword($userName, $password) {
     }
}
?>
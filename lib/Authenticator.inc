<?php

/**
 * $Id$
 * 
 * Interface class that performs all authentication functions.
 * 
 * @version $Revision$ 
 * @author michael@jamwarehouse.com
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
        global $default;
        if ($this->checkPassword($userName, $password)) {
            // retrieve user details from the database and return
            // $userDetails = UnitManager::getUserDetails($userName);
            // TODO: refactor the code below (and change for new db)
            //       also need to add ldap dn to user table
            $sql = new Owl_DB; 
            $query = "select * from $default->owl_users_table where username = '$username'";
            $sql->query($query);
            //$sql->query("select * from $default->owl_users_table where username = '$username' and password = '" . md5($password) . "'");
            $numrows = $sql->num_rows($sql);
            if ($numrows == "1") {
                while($sql->next_record()) {
                    if ( $sql->f("disabled") == 1 ) {  
                        $userDetails["status"]     = 2;
                    } else {
                        $userDetails["status"]     = 1;
                        $userDetails["userName"]   = $sql->f("username");
                        $userDetails["userID"]     = $sql->f("id");
                        $userDetails["groupID"]    = $sql->f("groupid");
                        $maxsessions               = $sql->f("maxsessions") + 1;
                    }
                }
                
                // remove stale sessions from the database for the user
                // that is signing on.
                Session::removeStaleSessions($userDetails["userID"]);
                
                 // Check if Maxsessions has been reached
                $sql = new Owl_DB; 
                $sql->query("select * from $default->owl_sessions_table where uid = '".$userDetails["userID"]."'");
                if ($sql->num_rows($sql) >= $maxsessions) {
                    if ( $userDetails["groupID"] == 0) {
                        // ignore maxsessions check for admin group
                        $userDetails["status"] = 1;
                    } else {
                        // return too many sessions status code
                        $userDetails["status"] = 3;
                    }
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
      * @return true if the password is correct, else false
      */
     function checkPassword($userName, $password) {
     }
}
?>
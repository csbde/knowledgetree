<?php

/**
 * $Id$
 * 
 * Interface class that performs all authentication functions.
 * 
 * @version $Revision$ 
 * @author michael@jamwarehouse.com
 * @package dms
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
        
        global $default;
        $sql = new Owl_DB; 
        $query = "select * from $default->owl_users_table where username = '$username' and password = '" . md5($password) . "'";
        $sql->query($query);
        //$sql->query("select * from $default->owl_users_table where username = '$username' and password = '" . md5($password) . "'");
        $numrows = $sql->num_rows($sql);
        // Bozz Begin added Password Encryption above, but for now 
        // I will allow admin to use non crypted password until he 
        // upgrades all users 
        if ($numrows == "1") {
            while($sql->next_record()) {
                if ( $sql->f("disabled") == 1 ) {  
                    $userDetails["status"]        = 2;
                } else {
                    $userDetails["status"]        = 1;
                    $userDetails["userName"]   = $sql->f("username");
                    $userDetails["userID"]     = $sql->f("id");
                    $userDetails["groupID"]    = $sql->f("groupid");
                    $maxsessions               = $sql->f("maxsessions") + 1;
                }
            }
        // Remove this else in a future version        
        } elseif ($username == "admin") {
            // username admin check password 
            $sql->query("select * from $default->owl_users_table where username = '$username' and password = '$password'");
            $numrows = $sql->num_rows($sql);
            if ($numrows == "1") {
                while($sql->next_record()) {
                    $userDetails["status"]     = 1;
                    $userDetails["userName"]   = $sql->f("username");
                    $userDetails["userID"]     = $sql->f("id");
                    $userDetails["groupID"]    = $sql->f("groupid");
                    $maxsessions               = $sql->f("maxsessions") + 1;
                }
            }
        // login failure
        } else {
            $userDetails["status"] = 0;
        }

        if (isset($userDetails["userID"]) && ($userDetails["status"] != 0)) {
            // remove stale sessions from the database for the user
            // that is signing on.
            Owl_Session::removeStaleSessions($userDetails["userID"]);
            
             // Check if Maxsessions has been reached
            $sql = new Owl_DB; 
            $sql->query("select * from $default->owl_sessions_table where uid = '".$userDetails["userID"]."'");
            if ($sql->num_rows($sql) >= $maxsessions && $userDetails["status"] != 0) {
                if ( $userDetails["groupID"] == 0) {
                    // ignore maxsessions check for admin group
                    $userDetails["status"] = 1;
                } else {
                    // return too many sessions status code
                    $userDetails["status"] = 3;
                }
            }
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
         Owl_Session::remove($sessionID)
     }
     

}

/**
 * Perform authentication tasks against the database.
 */
class DBAuthenticator extends Authenticator {
}

/**
 * Perform authentication tasks against LDAP compliant directory server.
 */
class LDAPAuthenticator extends Authenticator {
}

?>

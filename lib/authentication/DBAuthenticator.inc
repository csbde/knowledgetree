<?php

require_once("$default->owl_fs_root/lib/Authenticator.inc");

/**
 * $Id$
 * 
 * Perform authentication tasks against the database.
 * 
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */

class DBAuthenticator extends Authenticator {

    /**
     * Checks the user's password against the database
     *
     * @param $userName
     *        the name of the user to check
     * @param $password
     *        the password to check
     * @return true if the password is correct, else false
     */
    function checkPassword($userName, $password) {        
        global $default;

        $sql = new Owl_DB;
        $query = "select * from $default->owl_users_table where username = '$userName' and password = '" . md5($password) . "'";
        $sql->query($query);
        $numrows = $sql->num_rows($sql);

        if ($numrows == "1") {            
            return true;
        } else {
            return false;
        }
     }
}
?>

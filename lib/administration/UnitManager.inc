<?php

require_once("$default->owl_fs_root/lib/class.AuthLdap.php");

/**
 * $Id$
 * 
 * Performs unit administration tasks- this includes user, group and category management,
 *
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
class UnitManager {

    /**
     * Handle to the ldap util class
     */
    var $ldap;
    
    // user management
    
    /**
     * Searches the LDAP directory for users matching the supplied search string.
     * 
     * @param   $userNameSearch
     *          the username to search for
     * @return
     *          returns an array containing the users found
     */
    function listLdapUsers($userNameSearch) {
        global $default;

        // user attributes to search for
        $attributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");
        // initialise the ldap connection        
        $ldap = new AuthLdap();
        $server[0] = $default->ldapServer;
        $ldap->server = $server;       
        $ldap->dn = $default->ldapRootDn;
        
        if ( $ldap->connect()) {
            // search for the users
            // append and prepend wildcards
            $userArray = $ldap->getUsers("*" . $userNameSearch . "*", $attributes);
            if ($userArray) {
                // return the array
                return $userArray;                
            } else {
                // the search failed, bail
                return false;
            }
        } else {
            // ldap connection failed, bail
            // TODO: error handling
            return false;
            /*
            echo "There was a problem.<br>";
            echo "Error code : " . $ldap->ldapErrorCode . "<br>";
            echo "Error text : " . $ldap->ldapErrorText . "<br>";
            */
        }
    }
    
	/**
     * Adds a user to the unit.
     *
	 * @param    unitID
     *           the ID of the unit to add the user to
	 * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
	 */
	function addUser($unitID, $userDetails) {
        global $default;

        $sql = new Owl_DB;
        
        // TODO: userDetails validation
        
        // check that the username is unique
        $query = "select username from $default->owl_users_table where username = '" . $userDetails['username'] . "'";
        $sql->query($query);
        $numrows = $sql->num_rows($sql);
        
        if ($numrows > 0) {
            // duplicate username
            $default->errorMessage = "The username " . $userDetails['username'] . " is already in use, please choose another one";
            return false;
        }
        // insert the user
        $query = "insert into $default->owl_users_table (username, name, password, email, mobile, ldap_dn) " .
                 "values ( '" . $userDetails['username'] . "', " . 
                 "'" . $userDetails['name'] . "', " . 
                 "'', " .
                 "'" . $userDetails['email'] . "', " .
                 "'" . $userDetails['mobile'] . "', " .
                 "'" . $userDetails['ldap_dn'] . "')";                
        $result = $sql->query($query);
        if(!'result') {
            return false;
        } else {
            echo "insert into user table worked!<br>";
        }
        
        // TODO: insert into group table
        
        // TODO: must check that username is unique!
        // retrieve the generated id for insert into the user unit mapping table
        $query = "select * from $default->owl_users_table where username = '" . $userDetails['username'] . "'";
        $sql->query($query);
        $numrows = $sql->num_rows($sql);
        
        if ($numrows == "1") {
            while($sql->next_record()) {
                $userID = $sql->f("id");
                echo "read userID=$userID from db<br>";
            }
        } else {
            // select failed, bail
            // FIXME: need a rollback here
            echo "id select failed<br>";
            return false;
        }

        // now insert into the user-unit mapping table
        $query = "insert into $default->owl_user_unit_table (user_id, unit_id) values ($userID, $unitID)";
        $result = $sql->query($query);
        if (!'result') {
            // FIXME: rollback!
            echo "insert into user-unit table failed<br>";
            return false;
        } else {
            echo "insert into user-unit table worked!<br>";
        }
        
		return true;
	}

	/**
	* @param    unitID
	* @param    userID
	*/
	function removeUser($unitID, $userID){
		return false;
	}

	/**
	* @param    userID
	* @param    userDetails
	*/
	function updateUser($userID, $userDetails){
		return false;
	}

	function listUser(){
		//return null;
	}

    // group management
    
	/**
	* @param    name
	*/
	function createGroup($name){
		return false;
	}

	/**
	* @param    groupID
	*/
	function removeGroup($groupID){
		return false;
	}

	/**
	* @param    groupID
	* @param    name
	*/
	function updateGroup($groupID, $name){
		return false;
	}

	function listGroup(){
		//return null;
	}
    
	/**
	* @param    groupID
	* @param    userID
	*/
	function addUserToGroup($groupID, $userID){
		return false;
	}

	/**
	* @param    groupID
	* @param    userID
	*/
	function removeUserFromGroup($groupID, $userID){
		return false;
	}
    
    // category management
    
	/**
	* @param    name
	*/
	function createCategory($name){
		return false;
	}

	/**
	* @param    categoryID
	*/
	function removeCategory($categoryID){
		return false;
	}

	/**
	* @param    name
	* @param    categoryID
	*/
	function updateCategory($name, $categoryID){
		return false;
	}

	function listCategories(){
		//return null;
	}
    
}
?>

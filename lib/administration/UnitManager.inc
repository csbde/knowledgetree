<?php

require_once("$default->owl_fs_root/lib/class.AuthLdap.php");

/**
 * $Id$
 * 
 * Performs unit administration tasks- this includes user, group and category management,
 *
 * @version $Revision$ 
 * @author Mukhtar Dharsey
 * @package dmslib
 */
/*-----------------------------------------------------------------*/
/**
 * Class Unit Manager
 * 
 * Performs unit administration tasks- this includes user, group and category management,
 *
 * 
 */
/*-----------------------------------------------------------------*/

class UnitManager {

    /**
     * Handle to the ldap util class
     */
    var $ldap;
    
    // user management
    
    /*-----------------------------------------------------------------*/
    /*
     * Function ListLdapUsers($userNameSearch)
     *
     * Searches the LDAP directory for users matching the supplied search string.
     * 
     * @param   $userNameSearch
     *          the username to search for
     * @return
     *          returns an array containing the users found
     */
     /*-----------------------------------------------------------------*/
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
    
    
    //-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
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

    //-----------------------------------------------------------------
      /*
     * Function RemoveUser($unitID, $userID)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *    The ID of the unit to add the user to
     * @param    userID
     *    The Id of the User that must be deleted
     * @return
     *    True if the deletion was successful, else false if not or nonexistant.
     */
    //-----------------------------------------------------------------
	function removeUser($unitID, $userID)
	{
		global $default;
		// create a connection
        	$db = new Owl_DB;
        
        	//do validation that userid exists
        	$sql = "SELECT * FROM $default->owl_users_table WHERE $id = $userID";
        	$result = $db->query($sql)
        	if(!'result')
        	{
        		printf"User does not exist in the database")
        		return false
        	}
        	
               	//if user id exists delete it from the users table
               	$sql = "DELETE FROM $default->owl_users_table WHERE id = $userID";
               	$result = $db->query($sql)
        	if(!'result') 
        	{
           		return false;
        	} 
        	else
        	 {
            		echo "Deletion from user table Successful<br>";
            		return true;
        	 }
			
	 }

    //-----------------------------------------------------------------
    /*
     * Function updateUser($userID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    userID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function updateUser($userID, $userDetails)
	{
		global $default;
		// create a connection
        	$db = new Owl_DB;
        
        	//do validation that userid exists
        	$sql = "SELECT * FROM $default->owl_users_table WHERE $id = $userID";
        	$result = $db->query($sql)
        	if(!'result')
        	{
        		printf"User does not exist in the database")
        		return false
        	}
        	
               	//if user id exists update all info into the users table
               	//TODO group id change??????
               	$sql = "UPDATE $default->owl_users_table SET " .
               	 " username 	 	= " . $userDetails['username'] . "," . 
                 " name 		= " . $userDetails['name'] .     "," . 
                 " password	 	= " . $userDetails['password'] . "," . 
                 " quota_max 	 	= " . $userDetails['quota_max'] ."," . 
                 " quota_current	= " . $userDetails['quota_current'] ."," . 
                 " name 	 	= " . $userDetails['name'] .     "," . 
                 " email 	 	= " . $userDetails['email'] .    "," . 
                 " mobile 	 	= " . $userDetails['mobile'] .   "," . 
                 " email_notification   = " . $userDetails['email_notification'] ."," . 
                 " sms_notification     = " . $userDetails['sms_notification'] . "  
                 " WHERE id = ". $userID ."" ;
                 
                 
               	$result = $db->query($sql)
        	if(!'result') 
        	{
           		printf("Not Updated)
           		return false;
        	} 
        	else
        	 {
            		echo "Deletion from user table Successful<br>";
            		return true;
        	 }
	}
    //-----------------------------------------------------------------
    /*
     * Function listUser()
     *
     * returns an array of all the usernames
     *
     * @return
     *     an array of usernames
    */
    //-----------------------------------------------------------------
	function listUser(){
		
		global $default;
		// create a connection
        	$db = new Owl_DB;
        
        	//Get list of all the usernames
        	$sql = "SELECT username FROM $default->owl_users_table";
        	$result = $db->query($sql)
        
		//return an array of the usernames
		return $result;
		
	}
	
    //-----------------------------------------------------------------
    /*
     * Function GetUserDetails($userID)
     *
     * Returns an array of all the details for a specified user.
     *
     * @return
     *           an array of usernames
    */
    //-----------------------------------------------------------------
	function GetUserDetails($userID){
		
		global $default;
		// create a connection
        	$db = new Owl_DB;
        
        	//do validation that userid exists
        	$sql = "SELECT * FROM $default->owl_users_table WHERE $id = $userID";
        	$result = $db->query($sql)
        	if(!'result')
        	{
        		printf"User does not exist in the database")
        		return false
        	}
        
		//return an array of the usernames
		return $result;
		
	}

    // group management
    
	//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function createGroup($name){
		return false;
	}

	//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function removeGroup($groupID){
		return false;
	}

	//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function updateGroup($groupID, $name){
		return false;
	}
//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function listGroup(){
		//return null;
	}
    //-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	/**
	* @param    groupID
	* @param    userID
	*/
	function addUserToGroup($groupID, $userID){
		return false;
	}
//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	/**
	* @param    groupID
	* @param    userID
	*/
	function removeUserFromGroup($groupID, $userID){
		return false;
	}
 //-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------   
    // category management
    
	/**
	* @param    name
	*/
	function createCategory($name){
		return false;
	}
//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	/**
	* @param    categoryID
	*/
	function removeCategory($categoryID){
		return false;
	}
//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	/**
	* @param    name
	* @param    categoryID
	*/
	function updateCategory($name, $categoryID){
		return false;
	}
//-----------------------------------------------------------------
    /*
     * Function addUser($unitID, $userDetails)
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function listCategories(){
		//return null;
	}
    
}
?>

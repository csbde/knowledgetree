<?php

require_once("$default->owl_fs_root/lib/authentication/class.AuthLdap.php");

/**
 * $Id$
 * 
 * Performs user administration tasks- this includes create, remove, update
 * as well as addUserToGroup and removeUserFromGroup ..etc
 *
 * @version $Revision$ 
 * @author Mukhtar Dharsey
 * @package dmslib 
 */

//common admin functions that dont really form part of class
require_once ("$default->owl_fs_root/lib/administration/GroupManager.inc");
 
class UserManager
 {

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
     * @return  array
     *          returns an array containing the users found
     */
    function listLdapUsers($userNameSearch) 
    {
        global $default;

        // user attributes to search for
        $attributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");
        // initialise the ldap connection        
        $ldap = new AuthLdap();
        $server[0] = $default->ldapServer;
        $ldap->server = $server;       
        $ldap->dn = $default->ldapRootDn;
        
        if ( $ldap->connect())
         {
            // search for the users
            // append and prepend wildcards
            $userArray = $ldap->getUsers("*" . $userNameSearch . "*", $attributes);
            if ($userArray)
             {
                // return the array
                return $userArray;                
            } 
            else 
            {
                // the search failed, bail
                return false;
            }
          }
         else 
        {
           // $_SESSION["errorMessage"] = "LDAP error: (" . $ldap->ldapErrorCode ") " . $ldap->ldapErrorText;
            return false;
        }
    }
    
    /**
     *
     * Adds a user to the unit.
     *
     * @param    unitID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return 	 boolean
     *           true if the addition was successful, else false.
     */
    //-----------------------------------------------------------------
	function createUser($userDetails) {
        global $default;

        $sql = new Owl_DB;
        
             
        // check that the username is unique
        $query = "SELECT username FROM $default->owl_users_table WHERE username = '" . $userDetails['username'] . "'";
        $sql->query($query);
        $rows = $sql->num_rows($sql);
        
        if ($rows > 0) 
        {
            // duplicate username
            $default->errorMessage = "UserManager::The username " . $userDetails['username'] . " is already in use, please choose another one";
            $default->log->debug($default->errorMessage);
            return false;
        }
        // insert the user
        $query = "INSERT INTO $default->owl_users_table (username, name, password, quota_max,quota_current, email, mobile, email_notification, sms_notification, ldap_dn,language,max_sessions) " .
                 "VALUES ( '" . $userDetails['username'] . "'," . 
             	 	  "'" . $userDetails['name'] . "'," . 
                 	  "'" . $userDetails['password'] . "'," . 
                 	  "'" . $userDetails['quota_max'] . "'," .
                 	  "'" . $userDetails['quota_current'] . "'," .
                 	  "'" . $userDetails['email'] . "'," .
                  	  "'" . $userDetails['mobile'] . "'," .
                  	  "'" . $userDetails['email_notification'] . "'," .
                  	  "'" . $userDetails['sms_notification'] . "'," .
                 	  "'" . $userDetails['ldap_dn'] . "'," .
                 	  "'" . $userDetails['language'] . "'," .
                 	  "'" . $userDetails['max_sessions'] . "'" .
                 	  ")";                
                 
        $result = $sql->query($query);
        
        if(!'result') 
        {
            $default->log->debug( "UserManager::Addition Unsuccessful!<br>");
            return false;
        } 
        else 
        {
            $default->log->debug ("UserManager::User added Successfully!<br>");
        }
         return true;
         
        
      /*  
      //   TODO: insert into group table
        
         //TODO: must check that username is unique!
         //retrieve the generated id for insert into the user unit mapping table
        $query = "select * from $default->owl_users_table where username = '" . $userDetails['username'] . "'";
        $sql->query($query);
        $numrows = $sql->num_rows($sql);
        
        if ($numrows == "1") {
            while($sql->next_record()) {
                $userID = $sql->f("id");
                $default->log->debug "read userID=$userID from db<br>";
            }
         } else {
             select failed, bail
           // FIXME: need a rollback here
            $default->log->debug "id select failed<br>";
            return false;
        }

        // now insert into the user-unit mapping table
        $query = "insert into $default->owl_user_unit_table (user_id, unit_id) values ($userID, $unitID)";
        $result = $sql->query($query);
        if (!'result') {
            // FIXME: rollback!
            $default->log->debug "insert into user-unit table failed<br>";
            return false;
        } 
        else 
        {
            $default->log->debug "insert into user-unit table worked!<br>";
        }*/
        
		
	}

    /**
     * Removes a user from the users table...since a user does not exist anymore..
     * deletion from all its groups is also required
     *
     * @param    unitID
     *    The ID of the unit to add the user to
     * @param    userID
     *    The Id of the User that must be deleted
     * @return boolean
     *    True if the deletion was successful, else false if not or nonexistant.
     */
	function removeUser($userID)
	 {
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_table WHERE id = $userID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// check if result was found..0 if not
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::User does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists delete it from the users table
               	$query = "DELETE FROM $default->owl_users_table WHERE id = $userID";
               	$result = $sql->query($query);
        	
        	if(!'result') 
        	{
        		$default->log->debug("UserManager::Deletion unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug ("UserManager::Deletion from user table Successful<br>");
            		//check if belongs to group
            		$result= $this->removeUserFromAllGroups($userID);
            		return true;
        	 }
        	 
        	
			
	 }

    /**
     * Updates a users details
     *
     * @param    userID
     *           the ID of the unit to add the user to
     * @param    userDetails
     *           an array containing the details of the user
     * @return   boolean
     *           true if the addition was successful, else false.
     */
	function updateUser($userID, $userDetails)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_table WHERE id = $userID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	//if row = 0 ...then no entry was found..so return false
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::User does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists update all info into the users table
               	$query = "UPDATE $default->owl_users_table SET " .
               	 " username 	 	= '" . $userDetails['username'] . "'" .  
                 ", name 		= '" . $userDetails['name'] . "'" .    
                 ", password	 	= '" . $userDetails['password'] . "'" . 
                 ", quota_max 	 	= '" . $userDetails['quota_max'] ."'" . 
                 ", quota_current	= '" . $userDetails['quota_current'] ."'" . 
                 ", email 	 	= '" . $userDetails['email'] . "'" . 
                 ", mobile 	 	= '" . $userDetails['mobile'] . "'" . 
                 ", email_notification  = '" . $userDetails['email_notification'] . "'" . 
                 ", sms_notification    = '" . $userDetails['sms_notification'] .  "'" .
                 " WHERE id = $userID " ;
                 
                $result = $sql->query($query);
                
                
               // error checking to see if success	
        	if(!'result') 
        	{
           		$default->log->debug("UserManager::Not Updated");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("UserManager::Update Successful<br>");
            		return true;
        	 }
	}

    /**
     * Returns an array of all the usernames
     *
     * @return array
     *     An array of usernames
     */
	function listUsers()
	{
		
		global $default;
		$users = array ();
		$i = 0;
		// create a connection
        	$sql = new Owl_DB;
        
        	//Get list of all the usernames
        	$query = "SELECT username FROM $default->owl_users_table";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::No users in table");
        		return false;
        	}
        
        	while($sql->next_record())
        	{
        		$users["$i"] = array("username" => $sql->f("username"));
        		$i++;	
        	}
		//return an array of the usernames
		return $users;
		
	}
	
    /**
     * Function getUserDetails($userID)
     *
     * Returns an array of all the details for a specified user.
     *
     * @return array
     *    An array of details of a specified user
    */
	function getUserDetails($userID)
	{
		
		global $default;
		$details = array();
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_table WHERE id = $userID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::User does not exist in the database<br>");
        		return false;
        	}
        	
        	while($sql->next_record())
        	 {
        	 	$details[$sql->f("id")] = array("id" => $sql->f("id"),
                                                       "username" => $sql->f("username"),
                                                       "name" => $sql->f("name"),
                                                       "password" => $sql->f("password"),
                                                       "quota_max" => $sql->f("quota_max"),
                                                       "quota_current" => $sql->f("quota_current"),
                                                       "email" => $sql->f("email"),
                                                       "mobile" => $sql->f("mobile"),
                                                       "email_notification" => $sql->f("email_notification"),
                                                       "sms_notification" => $sql->f("sms_notification")); 
                }
        
		//return an array of the usernames
		return $details;
		
	}

    /**
     *
     * Adds a user to the group.
     *
     * @param    group
     *    The ID of the group to add the user to
     * @param    userID
     *    The Id of the User that must be deleted
     * @return boolean
     *    True if the addition was successful, else false if not or nonexistant.
     */
	function addUserToGroup($userID,$groupID)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_groups_table WHERE user_id = $userID AND group_id = $groupID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	        	
        	if($row >= 1)
        	{
        		$default->log->debug("UserManager::User already belongs to group<br>");
        		return false;
        	}
        	
               	//add user to the table
               	$query = "INSERT INTO $default->owl_users_groups_table (user_id, group_id) VALUES($userID, $groupID)" ;
               	$result = $sql->query($query);
               	
        	if(!'result') 
        	{
        		$default->log->debug("UserManager::Insertion into user_group table unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("UserManager::Insertion into user_group table Successful<br>");
            		return true;
        	 }
			
	 }
	 
	 
    /**
     * Removes a user from a group
     *
     * @param    groupID
     *    The ID of the group to remove the user from
     * @param    userID
     *    The Id of the User that must be removed from the table
     * @return boolean
     *    True if the deletion was successful, else false if not or nonexistant.
     */
	function removeUserFromGroup($userID,$groupID)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_groups_table WHERE user_id = $userID AND group_id = $groupID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// check if result was found..0 if not
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::User does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists delete it from the users table
               	$query = "DELETE FROM $default->owl_users_groups_table WHERE user_id = $userID AND group_id = $groupID";
               	$result = $sql->query($query);
        	
        	if(!'result') 
        	{
        		$default->log->debug ("UserManager::Deletion unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("UserManager::Deletion from user_group_link table Successful<br>");
            		return true;
        	 }
			
	 }
	 
    /**
     * Removes a user from ALL groups it belongs to
     *
     * @param    userID
     *    The Id of the User that must be removed from the table
     * @return boolean
     *    True if the deletion was successful, else false if not or nonexistant.
     */
	function removeUserFromAllGroups($userID)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_users_groups_table WHERE user_id = $userID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// check if result was found..0 if not
        	if($row == 0)
        	{
        		$default->log->debug("UserManager::User does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists delete it from the users table
               	$query = "DELETE FROM $default->owl_users_groups_table WHERE user_id = $userID";
               	$result = $sql->query($query);
        	
        	if(!'result') 
        	{
        		$default->log->debug ("UserManager::Deletion unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug ("UserManager::Deletion from user_group_link table Successful<br>");
            		return true;
        	 }
			
	 }


    /*
     * Function getUserID($username)
     *
     * gets the id of a user using their username
     *
     * @param    $username
     *   The username for which we want its ID
     * @return Integer
     *   The username's Id
     */
     
	function getUserID($username)
	 {
	        global $default;
	
	        $sql = new Owl_DB;
	        
	             
	        // check that username exists if it does'nt return false
	        $query = "SELECT id FROM $default->owl_users_table WHERE username = '" . $username . "'";
	        $sql->query($query);
	        $rows = $sql->num_rows($sql);
	        // go into record set
	        $sql->next_record();
	        
	        // store the id in a variable
	        $id = $sql->f("id");
	        
	        // if no entry..username does not exist
	        if ($rows == 0) 
	        {
	            // duplicate username
	            $default->errorMessage = "UserManager::The username " . $username . " does not exist<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	        else
	        {
	        	return $id;
	        }
	}
	
    /*
     * Function getGroups($userID)
     *
     * Gets the group that the user belongs to
     *
     * @param    $userID
     *   The ID of the user 
     * @return Array
     *   array of groupID's and name
     */
     function getGroups($userID)
	 {
	        global $default;
		$groups = array();
	        $sql = new Owl_DB;
	        $groupName = new GroupManager;
	        
	             
	        // check that username exists if it does'nt return false
	        $query = "SELECT group_id FROM $default->owl_users_groups_table WHERE  user_id = '" . $userID . "'";
	        $sql->query($query);
	        $rows = $sql->num_rows($sql);
	        
	        // if no entry..user does not belong to any groups
	        if ($rows == 0) 
	        {
	            // duplicate username
	            $default->errorMessage = "UserManager::The user does not belong to any groups<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	        
	        $i =0;
	        
	        while($sql->next_record())
	        {
	        	$groups[$i] = array("id" => $sql->f("group_id"),
	        			"name" => $groupName->getGroupName($sql->f("group_id"))
	        			);
	        	$i++;
	        }
	        
	        return $groups;
	}
	
    
    
}
?>

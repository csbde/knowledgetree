<?php
/**
 * $Id: GroupManager.inc
 * 
 * Performs group administration tasks- this includes create, remove, update
 * as well as addGroupToUnit and removeGroupFromUnit ..etc
 *
 * @version $Revision: 1.0  
 * @author Mukhtar Dharsey
 * @package dmslib 
 */
 
// group management
 class GroupManager
 {
    
    /**
     *
     * Adds a new group
     *
     * @param    name
     *           name of new group
     * @return 	 boolean
     *           true if the addition was successful, else false.
     */
 
	function createGroup($name) {
        global $default;

        $sql = new Owl_DB;
        
             
        // check that the group name is unique
        $query = "SELECT name FROM $default->owl_groups_table WHERE name = '" . $name . "'";
        $sql->query($query);
        $rows = $sql->num_rows($sql);
        
        if ($rows > 0) 
        {
            // duplicate username
            $default->errorMessage = "GroupManager::The Group name " . $name . " is already in use, please choose another one";
            $default->log->debug($default->errorMessage);
            return false;
        }
        // insert the user
        $query = "INSERT INTO $default->owl_groups_table (name) VALUES ('" . $name . "')";                
                 
        $result = $sql->query($query);
        
        if(!'result') 
        {
            $default->log->debug( "GroupManager::New Group Addition Unsuccessful!<br>");
            return false;
        } 
        else 
        {
            $default->log->debug ("GroupManager::New Group added Successfully!<br>");
            return true;
        }
        
        
		
}

    /**
     * Removes a group from the groups table as well as from the groups_units_link table
     *
     * @param    groupID
     *    The id of the group to be deleted
     * @return boolean
     *    True if the deletion was successful, else false if not or nonexistant.
     */
	function removeGroup($groupID)
	 {
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_groups_table WHERE id = '" . $groupID ."'";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// check if result was found..0 if not
        	if($row == 0)
        	{
        		$default->log->debug("GroupManager::Group does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists delete it from the users table
               	$query = "DELETE FROM $default->owl_groups_table WHERE id = $groupID";
               	$result = $sql->query($query);
        	
        	if(!'result') 
        	{
        		$default->log->debug("GroupManager::Deletion unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug ("GroupManager::Deletion from user table Successful<br>");
            		//check if belongs to group
            		
            		$result= $this->removeGroupFromUnit($groupID);
            		return true;
        	 }
        	 
        	
			
	 }

    /**
     * Updates groups details ..ie rename the group
     *
     * @param    groupID
     *           the group which is to be renamed
     * @param    name
     *           new name of the group
     * @return   boolean
     *           true if the addition was successful, else false.
     */
	function updateGroup($groupID, $name)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that userid exists
        	$query = "SELECT * FROM $default->owl_groups_table WHERE id = $groupID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	//if row = 0 ...then no entry was found..so return false
        	if($row == 0)
        	{
        		$default->log->debug("GroupManager::Group does not exist in the database<br>");
        		return false;
        	}
        	
               	//if user id exists update all info into the users table
               	$query = "UPDATE $default->owl_groups_table SET name = '" . $name . "' WHERE id = $groupID " ;
                 
                $result = $sql->query($query);
                
                
               // error checking to see if success	
        	if(!'result') 
        	{
           		$default->log->debug("GroupManager::Not Updated");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("GroupManager::Update Successful<br>");
            		return true;
        	 }
	}

    /**
     * Returns an array of all the groups
     *
     * @return array
     *     An array of groups
     */
	function listGroups()
	{
		
		global $default;
		$groups = array ();
		$i = 0;
		// create a connection
        	$sql = new Owl_DB;
        
        	//Get list of all the usernames
        	$query = "SELECT name FROM $default->owl_groups_table";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	//error checking
        	if($row == 0)
        	{
        		$default->log->debug("GroupManager::No users in table");
        		return false;
        	}
        
        	//store in array
        	while($sql->next_record())
        	{
        		$groups["$i"] = array("name" => $sql->f("name"));
        		$i++;	
        	}
        	
		//return an array of the groups
		return $groups;
		
	}
	
   /**
     *
     * Adds a group to a unit NB: Group belongs to 1 unit only
     *
     * @param    groupID
     *    The ID of the group to add the user to
     * @param    unitID
     *    The Id of the unit to add to
     * @return boolean
     *    True if the addition was successful, else false if not or nonexistant.
     */
	function addGroupToUnit($groupID,$unitID)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that groupid exists
        	$query = "SELECT * FROM $default->owl_groups_units_table WHERE group_id = $groupID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// if it does exist return false..already there        	
        	if($row >= 1)
        	{
        		$default->log->debug("GroupManager::Group already belongs to a unit<br>");
        		return false;
        	}
        	
               	//add user to the table
               	$query = "INSERT INTO $default->owl_groups_units_table (group_id,unit_id) VALUES($groupID,$unitID)";
               	$result = $sql->query($query);
               	
        	if(!'result') 
        	{
        		$default->log->debug("GroupManager::Insertion into groups_units table unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("GroupManager::Insertion into groups_units table Successful<br>");
            		return true;
        	 }
			
	 }
	 
	 
    /**
     * Removes a group from a unit NB: Group can only belong to 1 unit
     *
     * @param    groupID
     *    The ID of the group be removed from a unit

     * @return boolean
     *    True if the deletion was successful, else false if not or nonexistant.
     */
	function removeGroupFromUnit($groupID)
	{
		global $default;
		// create a connection
        	$sql = new Owl_DB;
        
        	//do validation that groupid exists
        	$query = "SELECT * FROM $default->owl_groups_units_table WHERE group_id = $groupID";
        	$result = $sql->query($query);
        	$row = $sql->num_rows($result);
        	
        	// check if result was found..0 if not
        	if($row == 0)
        	{
        		$default->log->debug("GroupManager::Group does not exist in the database<br>");
        		return false;
        	}
        	
               	//if group id exists delete it from the users table
               	$query = "DELETE FROM $default->owl_groups_units_table WHERE group_id = $groupID";
               	$result = $sql->query($query);
        	
        	if(!'result') 
        	{
        		$default->log->debug ("GroupManager::Deletion unsuccessful<br>");
           		return false;
        	} 
        	else
        	 {
            		$default->log->debug("GroupManager::Deletion from user_group_link table Successful<br>");
            		return true;
        	 }
			
	}	
    /*
     *
     * gets the id of a group using its name
     *
     * @param    $groupName
     *   The GroupName for which we want its ID
     * @return Integer
     *   The groupname's Id
     */
     
	function getGroupID($groupName)
	 {
	        global $default;
	
	        $sql = new Owl_DB;
	             
	        // check that group exists if it does'nt return false
	        $query = "SELECT id FROM $default->owl_groups_table WHERE name = '" . $groupName . "'";
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
	            $default->errorMessage = "GroupManager::The Group  " . $groupName . " does not exist<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	        else
	        {
	        	return $id;
	        }
	}	
			
    /*
     * Function getGroupName($groupID)
     *
     * gets the id of a user using their username
     *
     * @param    $username
     *   The username for which we want its ID
     * @return char
     *   name of group
     */
     
	function getGroupName($groupID)
	 {
	        global $default;
	
	        $sql = new Owl_DB;
	        
	             
	        // check that username exists if it does'nt return false
	        $query = "SELECT name FROM $default->owl_groups_table WHERE id = '" . $groupID . "'";
	        $sql->query($query);
	        $rows = $sql->num_rows($sql);
	        // go into record set
	        $sql->next_record();
	        
	        // store the id in a variable
	        $name = $sql->f("name");
	        
	        // if no entry..group name does'nt exist
	        if ($rows == 0) 
	        {
	            // duplicate username
	            $default->errorMessage = "GroupManager::The group does not exist<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	        else
	        {
	        	return $name;
	        }
	}	

   /*
     *
     * Gets the unit that the group belongs to
     *
     * @param    $groupID
     *   The ID of the group
     * @return Array
     *   array of unitID and name of the unit
     */
     function getUnit($groupID)
	 {
	        global $default;
		$unitinfo = array();
	        $sql = new Owl_DB;
	        //$groupName = new GroupManager;
	        
	             
	        // check that group exists if it does'nt return false
	        $query = "SELECT unit_id FROM $default->owl_groups_units_table WHERE  group_id = '" . $groupID . "'";
	        $sql->query($query);
	        $rows = $sql->num_rows($sql);
	        
	        // if no entry..group does not belong to a unit
	        if ($rows == 0) 
	        {
	            // duplicate username
	            $default->errorMessage = "GroupManager::The group does not belong to a unit<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	 
	        	        
	        while($sql->next_record())
	        {
	        	$unitinfo[1] = array("id" => $sql->f("unit_id"),
	        			    "name" => $this->getUnitName($sql->f("unit_id")) // TODO change this to unit obj
	        			  );
	        	
	        }
	        
	        return $unitinfo;
	}	
	
	
	
    /*  
     *
     * gets the name of a unit using their username
     *
     * @param   unitID
     *   The id of the unit who's name we want
     * @return char
     *   name of unit
     */
     
	function getUnitName($unitID)
	 {
	        global $default;
	
	        $sql = new Owl_DB;
	        
	             
	        // check that username exists if it does'nt return false
	        $query = "SELECT name FROM $default->owl_units_table WHERE id = '" . $unitID . "'";
	        $sql->query($query);
	        $rows = $sql->num_rows($sql);
	        // go into record set
	        $sql->next_record();
	        
	        // store the id in a variable
	        $name = $sql->f("name");
	        
	        // if no entry..unit name does'nt exist
	        if ($rows == 0) 
	        {
	            // duplicate username
	            $default->errorMessage = "GroupManager::The unit does not exist<br>";
	            $default->log->debug($default->errorMessage);
	            return false;
	        }
	        else
	        {
	        	return $name;
	        }
	}
		
 }

?>
<?php
/**
 * $Id$
 *
 * Represents a document discussion comment.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Omar Rahbeeni, CS Holdings, South Africa
 * @package lib.discussions 
 */
class DiscussionComment {

    /**
     * The underlying Discussion Comments class
     */
    var $iId;
	var $iThreadID;
	var $iUserID;
	var $sSubject;
	var $sBody;
	var $dDate;
	
    /**
     * DiscussionComment Constructor
     *
     * @param string for body of text
     * @param string for subject line
     * @param integer for userID who is creating the Discussion Comment
     * @param integer for the ThreadID which links all the comments for a document
     */
    function DiscussionComment($sNewBody, $sNewSubject, $iNewUserID, $iNewThreadID) {
        global $default;

        // start initializing variables.
        $this->sBody = $sNewBody;
        $this->sSubject = $sNewSubject;
        $this->iUserID = $iNewUserID;
        $this->iThreadID = $iNewThreadID;
        $this->dDate = null;				// This will get added in the SQL statement
    	$this->iId = -1;						// This will get created when the entry SQL statement
    }

	
    /**   
     * Return the CommentID
     */    
    function getID(){
   		return $this->iId; 	
    }
    
    /**   
     * Return the ThreadID for the Comment
     */    
    function getThreadID(){
    	return $this->iThreadID;    	
    }
    
    /**   
     * Set a new Thread ID
     */    
    function setThreadID($iNewThreadID){
    	$this->iThreadID = $iNewThreadID;   	
    }
    
    /**
     * Return the UserID
     */    
    function getUserID(){
    	return $this->iUserID;
    }
    
    /*    
     * Set a new User ID
     */    
    function setUserID($iNewUserID){
    	$this->iUserID = $iNewUserID;    	
    }
    
    /**    
     * Return the Subject text
     */    
    function getSubject(){
    	return $this->sSubject;    	
    }
    
    /**    
     * Set a new Subject text
     */    
    function setSubject($sNewSubject){
    	$this->sSubject = $sNewSubject;
    	
    }
    
    /**    
     * Return the Text Body
     */    
    function getBody(){
    	return $this->sBody;    	
    }
    
    /**    
     * Set a new TextBody
     */        
    function setBody($sNewBody){
    	$this->sBody = $sNewBody;
    }
    
    /**    
     * Get a Date Created
     */        
    function getDate(){
    	return $this->dDate;
    }
    
    /**
    * Static function.
    * Given a web_documents primary key it will create a 
    * discusson comment object and populate it with the 
    * corresponding database values
    *
    * @return Comment populated Comment object on successful query, false otherwise and set $_SESSION["errorMessage"]
    */
    function & get($iNewCommentID) {
        global $default;
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->discussion_comments_table WHERE id = $iNewCommentID");
        if ($result) {
            if ($sql->next_record()) {                
                $oDiscussionComment = & new DiscussionComment($sql->f("body"),$sql->f("subject"),$sql->f("user_id"),$sql->f("thread_id"));
                $oDiscussionComment->iId = $iNewCommentID;
                $oDiscussionComment->dDate = $sql->f("date");
                return $oDiscussionComment;
            }
            $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iNewCommentID . " table = $default->discussion_comments_table";
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
    
    function create(){
        global $default, $lang_err_database, $lang_err_object_exists;
        //if the object hasn't been created
        if ($this->iId < 0) { //check to see if comment exists
            $sql = $default->db;            																																
            
            $result = $sql->query("INSERT INTO " . $default->discussion_comments_table . " (thread_id,user_id,subject, body, date)" .
             				       "VALUES (" . $this->iThreadID . "," . $this->iUserID . ",\"" . $this->sSubject . "\",\"" . $this->sBody . "\",Now())");
            if ($result) {
	          	$this->iId = $sql->insert_id();
	         	return true;                    
	        }
	        $_SESSION["errorMessage"] = $lang_err_database;
	        return false;    
            
        }
        $_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->units_table";
        return false;	
    }
    
    function update(){    	
    	global $default, $lang_err_database, $lang_err_object_key;
        //only update if the object has been stored
        if ($this->iId > 0) {
            $sql = $default->db;
            // Do update query            
            
            $result = $sql->query(	"UPDATE " . $default->discussion_comments_table . 
            						" SET thread_id = " 		. $this->iThreadID .
            						" AND user_id = " 			. $this->iUserID .
            						" AND subject = " 			. $this->sSubject .
            						" AND body = " 				. $this->sBody .
            						" AND date = Now()" 		. $this->dDate .            						  
            						" WHERE id = " 				. $this->iId);
            if ($result) {           
     	       return true;
            }
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_object_key;
        return false;
    }
    
     	function delete(){
    	 global $default, $lang_err_database, $lang_err_object_key;
        
        //only delete the object if it exists in the database
        if ($this->iId > 0) {
            //check to see if group is linked to a unit
            $sql = $default->db;
            $query = "SELECT * FROM ". $default->discussion_comments_table . " WHERE id = " . $this->iId  ;
            $sql->query($query);
            $rows = $sql->num_rows($sql);

            if ($rows > 1) {
                // duplicate Thread exists
                $_SESSION["errorMessage"] = "Thread::The Thread id " . $this->iId . " has duplicates!";
                return false;

            } else {
                $sql = $default->db;
                $result = $sql->query("DELETE FROM $default->discussion_comments_table WHERE id = $this->iId");
                if ($result) {
                    return true;
                }
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }
        }
        $_SESSION["errorMessage"] = $lang_err_object_key;
        return false;
    }    
    
   
}

?>

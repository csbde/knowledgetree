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
class DiscussionComment extends KTEntity {

    /**
     * The underlying Discussion Comments class
     */
    var $iId;
	var $iThreadID;
	var $iUserID;
	var $sSubject;
	var $sBody;
	var $dDate;
	var $iInReplyTo;
	
    /**
     * DiscussionComment Constructor
     *
     * @param string for body of text
     * @param string for subject line
     * @param integer for userID who is creating the Discussion Comment
     * @param integer for the ThreadID which links all the comments for a document
     */
    function DiscussionComment($sNewBody, $sNewSubject, $iNewUserID, $iNewThreadID, $iNewInReplyTo) {
        global $default;

        // start initializing variables.
        $this->sBody = $sNewBody;
        $this->sSubject = $sNewSubject;
        $this->iUserID = $iNewUserID;
        $this->iThreadID = $iNewThreadID;
        $this->dDate = null;				// This will get added in the SQL statement
        $this->iInReplyTo = $iNewInReplyTo;
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
     * Return the comment this is a reply to
     */    
    function getInReplyTo(){
    	return $this->iInReplyTo;    	
    }
    
    /**    
     * Sets the comment this is a reply to
     */        
    function setInReplyTo($sNewCommentID){
    	$this->iInReplyTo = $sNewCommentID;
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
        $result = $sql->query(array("SELECT * FROM $default->discussion_comments_table WHERE id = ?", $iNewCommentID));/*ok*/
        if ($result) {
            if ($sql->next_record()) {                
                $oDiscussionComment = & new DiscussionComment($sql->f("body"),$sql->f("subject"),$sql->f("user_id"),$sql->f("thread_id"),$sql->f("in_reply_to"));
                $oDiscussionComment->iId = $iNewCommentID;
                $oDiscussionComment->dDate = $sql->f("date");
                return $oDiscussionComment;
            }
            return false;
        }
        return false;
    }
    
    /**
     * Static function
     * Get a list of DiscussionComments
     *
     * @param  String  Where clause (optional)
     *
     * @return Array array of DiscussionComments objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aDiscussionComments = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->discussion_comments_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));/*wc*/
        if ($result) {
            while ($sql->next_record()) {
                $aDiscussionComments[] = & DiscussionComment::get($sql->f("id"));
            }
            return $aDiscussionComments;
        }
        return false;
    }

    function _fieldValues () {
        return array(
            'thread_id' => $this->iThreadID,
            'user_id' => $this->iUserID,
            'subject' => $this->sSubject,
            'body' => $this->sBody,
            'date' => getCurrentDate(),
            'in_reply_to' => $this->iInReplyTo,
        );
    }

    function _table () {
        return $default->discussion_comments_table;
    }
        
	function delete(){
		global $default;
		  
		// only delete the object if it exists in the database
		if ($this->iId > 0) {
			//check to see if group is linked to a unit
			$sql = $default->db;
			$query = array("SELECT * FROM ". $default->discussion_comments_table . " WHERE id = ?", $this->iId);/*ok*/
			$sql->query($query);
			$rows = $sql->num_rows($sql);
			
			if ($rows > 1) {
				// duplicate Thread exists
				return false;
			} else {
				$sql = $default->db;
				$result = $sql->query("DELETE FROM $default->discussion_comments_table WHERE id = $this->iId");
				if ($result) {
					return true;
				}
				return false;
			}
		}
		return false;
	}   
}

?>

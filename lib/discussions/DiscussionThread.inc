<?php
/**
 * $Id$
 *
 * Represents a document discussion thread.
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
class DiscussionThread extends KTEntity{

    /**
     * The underlying Discussion Comments class
     */
    var $iId;
	var $iDocumentID;
	var $iFirstCommentID;
	var $iLastCommentID;
	var $iNumberOfViews;
	var $iNumberOfReplies;
	var $iCreatorID;
		
	
    /**
     * DiscussionThread Constructor
     */
    function DiscussionThread($iNewFirstCommentID, $iNewDocumentID, $iNewCreatorID) {
        global $default;
        // create a new Discussion Thread object.        
        $this->iDocumentID = $iNewDocumentID;
        $this->iCreatorID = $iNewCreatorID;
        $this->iId  = -1;
        $this->iFirstCommentID = $iNewFirstCommentID;
        $this->iLastCommentID = -1;
        $this->iNumberOfViews = 0;
        $this->iNumberOfReplies = 0;        
    }

    /**
   	 * Get the iId for current thread
   	 */   
    function getID(){
    	return $this->iId;	
    }
       
    /**
   	 * Get document id
   	 */ 	
    function getDocumentID(){
    	return $this->iDocumentID;
    }
    
    /**
   	 * Get the id of the first comment
   	 */
    function getFirstCommentID(){
    	return $this->iFirstCommentID;
    }
    
    /**
   	 * Set the First Comment ID
   	 */
 	function setFirstCommentID($NewFirstCommentID){
    	$this->iFirstCommentID = $NewFirstCommentID;
    }
    
    /**
   	 * get the id of the last comment
   	 */
    function getLastCommentID(){
    	return $this->iLastCommentID;
    }
    
    /**
   	 * Set the id of the last comment
   	 */
    function setLastCommentID($iNewLastComment){
    	$this->iLastCommentID = $iNewLastComment;
    }
    
    /**
   	 * Get the total number of time the thread was viewed
   	 */
    function getNumberOfViews(){
    	return $this->iNumberOfViews;
    }
    /**
   	 * Set the total number of times the thread was viewed
   	 */
    function setNumberOfViews($iValue) {
    	$this->iNumberOfViews = $iValue;    	 
    }
        
    /**
   	 * Increment the total number of times the thread was viewed
   	 */
    function incrementNumberOfViews() {
    	$this->iNumberOfViews += 1;    	 
    }
    
    /**
   	 * Get the total number of replies(comments) in a thread
   	 */
    function getNumberOfReplies(){
    	return $this->iNumberOfReplies;
    }
    
    /**
   	 * Increment the total number of replies (comments) in a thread
   	 */
    function incrementNumberOfReplies(){
    	$this->iNumberOfReplies += 1; 
    }
    /**
   	 * Set the total number of number of replies (comments) in a thread
   	 */    
    function setNumberOfReplies($iValue){
    	$this->iNumberOfReplies = $iValue; 
    }    
     
    /**
     * Get a All commentID's seperated by a comma ","
	 */
    function getAllCommentID() {
    	global $default;
    	
    	$sql = $default->db;
        $result = $sql->query("SELECT id FROM $default->discussion_threads_table WHERE document_id = $this->iDocumentID ORDER BY id");
        if ($result) {
        	$sql->next_record();
        	$iThreadID = $sql->f("id");
        
	        $result = $sql->query("SELECT id FROM $default->discussion_comments_table WHERE thread_id = $iThreadID ORDER BY date Desc");
	        
	        if ($result) {           
	            while ($sql->next_record()) {
	                if ($sql->f("id") > 0) {
	                	$sAllCommentID .= $sql->f("id") . ",";                		
	                } else {
	                	//ID not valid 
	                }                
	            }
	            return $sAllCommentID ;            
	        }
	        return false;
        } else {
         // No Thread for document
         return false;
        }    	
    }    
    
    /**
     * Static function
     * Get a list of DiscussionThreads
     *
     * @param  String  Where clause (optional)
     *
     * @return Array array of DiscussionThreads objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aDiscussionThreads = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->discussion_threads_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            while ($sql->next_record()) {
                $aDiscussionThreads[] = & DiscussionThread::get($sql->f("id"));
            }
            return $aDiscussionThreads;
        }
        return false;
    }
	     
    function getThreadIDforDoc($iDocumentID){
    	global $default;	
		$sql = $default->db;
        $result = $sql->query("SELECT id FROM $default->discussion_threads_table WHERE document_id = $iDocumentID");
        if ($result) {        
            if ($sql->next_record()) {
                if ($sql->f("id") > 0) {
                	return $sql->f("id");	
                } else {
                	return "false";
                }
            }
            return false;
        }
        return false;
    }
    
    
    /**
    * Static function.
    * Given a web_documents primary key it will create a 
    * DiscussionThread object and populate it with the 
    * corresponding database values
    *
    * @return Unit populated Unit object on successful query, false otherwise and set $_SESSION["errorMessage"]
    */
    function & get($iNewThreadID) {
        global $default;
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->discussion_threads_table WHERE id = $iNewThreadID");
        if ($result) {
            if ($sql->next_record()) {
                			
                $oDiscussionThread = & new DiscussionThread($sql->f("first_comment_id"),
			                								$sql->f("document_id"),
			                								$sql->f("creator_id"));
              	
                $oDiscussionThread->iId 			 = $iNewThreadID;
                $oDiscussionThread->iLastCommentID   = $sql->f("last_comment_id");
                $oDiscussionThread->iNumberOfViews   = $sql->f("views");
                $oDiscussionThread->iNumberOfReplies = $sql->f("replies");
                return $oDiscussionThread;
            }
            return false;
        }
        return false;
    }

    function _fieldValues () {
        return array(
            'document_id' => $this->iDocumentID,
            'first_comment_id' => $this->iFirstCommentID,
            'last_comment_id' => $this->iLastCommentID,
            'views' => $this->iNumberOfViews,
            'replies' => $this->iNumberOfReplies,
            'creator_id' => $this->iCreatorID,
        );
    }

    function _table () {
        global $default;
        return $default->discussion_threads_table;
    }

    /**
   	 * Delete a thread in the table
   	 */
   	function delete(){
    	 global $default;
        
        //only delete the object if it exists in the database
        if ($this->iId > 0) {
            //check to see if group is linked to a unit
            $sql = $default->db;
            $query = "SELECT * FROM ". $default->discussion_threads_table ." WHERE id = " . $this->iId  ;
            $sql->query($query);
            $rows = $sql->num_rows($sql);

            if ($rows > 1) {
                // duplicate Thread exists
                $_SESSION["errorMessage"] = "Thread::The Thread id " . $this->iId . " has duplicates!";
                return false;

            } else {
                $sql = $default->db;
                $result = $sql->query("DELETE FROM $default->discussion_threads_table WHERE id = $this->iId");
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

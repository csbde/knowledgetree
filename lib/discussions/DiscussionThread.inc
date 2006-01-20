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
    var $_bUsePearError = true;

	var $iDocumentId;
	var $iFirstCommentId = -1;
	var $iLastCommentId = -1;
	var $iNumberOfViews = 0;
	var $iNumberOfReplies = 0;
	var $iState = 0;
	var $iCloseMetadataVersion = 0;
	var $sCloseReason = "";
	var $iCreatorId;
		
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iDocumentId' => 'document_id',
        'iFirstCommentId' => 'first_comment_id',
        'iLastCommentId' => 'last_comment_id',
        'iNumberOfViews' => 'views',
        'iNumberOfReplies' => 'replies',
		'iState' => 'state',
		'iCloseMetadataVersion' => 'close_metadata_version',
		'sCloseReason' => 'close_reason',
        'iCreatorId' => 'creator_id',
    );
	
    /**
     * DiscussionThread Constructor
     */
    function DiscussionThread($iFirstCommentId = null, $iDocumentId = null, $iCreatorId = null) {
        if (!empty($iDocumentId)) {
            $this->iDocumentId = $iDocumentId;
        }
        if (!empty($iCreatorId)) {
            $this->iCreatorId = $iCreatorId;
        }
        if (!empty($iFirstCommentId)) {
            $this->iFirstCommentId = $iFirstCommentId;
        }
    }

    function getDocumentId(){ return $this->iDocumentId; }
    function getCreatorId(){ return $this->iCreatorId; }
    function getFirstCommentId(){ return $this->iFirstCommentId; }
 	function setFirstCommentId($NewFirstCommentId){ $this->iFirstCommentId = $NewFirstCommentId; }
    function getLastCommentId(){ return $this->iLastCommentId; }
    function setLastCommentId($iNewLastComment){ $this->iLastCommentId = $iNewLastComment; }
    function getNumberOfViews(){ return $this->iNumberOfViews; }
    function setNumberOfViews($iValue) { $this->iNumberOfViews = $iValue; }
    function incrementNumberOfViews() { $this->iNumberOfViews += 1; }
    function getNumberOfReplies(){ return $this->iNumberOfReplies; }
    function incrementNumberOfReplies(){ $this->iNumberOfReplies += 1; }
    function setNumberOfReplies($iValue){ $this->iNumberOfReplies = $iValue; }    
	function getState(){ return $this->iState; }
	function setState($iValue){ $this->iState = $iValue; }
	function getCloseMetadataVersion(){ return $this->iCloseMetadataVersion; }
	function setCloseMetadataVersion($iValue){ $this->iCloseMetadataVersion = $iValue; }
	function getCloseReason(){ return $this->sCloseReason; }
	function setCloseReason($sValue){ $this->sCloseReason = $sValue; }
	
	
    /**
     * Get a All commentId's seperated by a comma ","
	 */
    function getAllCommentId() {
    	global $default;
    	
    	$sql = $default->db;
        $aQuery = array("SELECT id FROM $default->discussion_threads_table WHERE document_id = ? ORDER BY id",/*ok*/
            $this->iDocumentId);
        $result = $sql->query($aQuery);
        if ($result) {
        	$sql->next_record();
        	$iThreadId = $sql->f("id");
        
            $aQuery = array("SELECT id FROM $default->discussion_comments_table WHERE thread_id = ? ORDER BY date DESC",/*ok*/
                $iThreadId);
	        $result = $sql->query($aQuery);
	        
	        if ($result) {           
	            while ($sql->next_record()) {
	                if ($sql->f("id") > 0) {
	                	$sAllCommentId .= $sql->f("id") . ",";                		
	                } else {
	                	//Id not valid 
	                }                
	            }
	            return $sAllCommentId ;            
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
        return KTEntityUtil::getList(DiscussionThread::_table(), 'DiscussionThread', $sWhereClause);
    }
	     
    function getThreadIdforDoc($iDocumentId){
    	global $default;	
		$sql = $default->db;
        $result = $sql->query(array("SELECT id FROM $default->discussion_threads_table WHERE document_id = ?", $iDocumentId));/*ok*/
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
    
    
    function &get($iId) {
        return KTEntityUtil::get('DiscussionThread', $iId);
    }

    function _table () {
        global $default;
        return $default->discussion_threads_table;
    }

    function &createFromArray($aArray) {
        return KTEntityUTil::createFromArray('DiscussionThread', $aArray);
    }

    function getComments() {
        return DiscussionComment::getByThread($this);
    }

    function getCommentsSortedByDate() {
        return DiscussionComment::getByThreadSortedByDate($this);
    }
}

?>

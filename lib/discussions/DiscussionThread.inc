<?php
/**
 * $Id$
 *
 * Represents a document discussion thread.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
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

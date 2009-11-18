<?php
/**
 * $Id$
 *
 * Represents a document discussion thread.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 */

class DiscussionThread extends KTEntity{
    var $_bUsePearError = true;

	var $iDocumentId;
	var $iFirstCommentId = null;
	var $iLastCommentId = null;
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

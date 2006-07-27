<?php
/**
 * $Id$
 *
 * Represents a document discussion comment.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse, South Africa
 * @package lib.discussions
 */

class DiscussionComment extends KTEntity {
    var $_bUsePearError = true;
    
    var $iThreadId;
    var $iUserId;
    var $sSubject;
    var $sBody;
    var $dDate;
    var $iInReplyTo = -1;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iThreadId' => 'thread_id',
        'iUserId' => 'user_id',
        'sSubject' => 'subject',
        'sBody' => 'body',
        'iInReplyTo' => 'in_reply_to',
        'dDate' => 'date',
    );

    function DiscussionComment($sBody = null, $sSubject = null, $iUserId = null, $iThreadId = null, $iInReplyTo = null) {
        $this->sBody = $sBody;
        $this->sSubject = $sSubject;
        $this->iUserId = $iUserId;
        $this->iThreadId = $iThreadId;
        $this->iInReplyTo = $iInReplyTo;
    }

    function getThreadId(){ return $this->iThreadId; }
    function setThreadId($iThreadId){ $this->iThreadId = $iThreadId; }
    function getUserId(){ return $this->iUserId; } 
    function setUserId($iNewUserId){ $this->iUserId = $iNewUserId; }
    function getSubject(){ return $this->sSubject; }
    function setSubject($sNewSubject){ $this->sSubject = $sNewSubject; }
    function getBody(){ return $this->sBody; }
    function setBody($sNewBody){ $this->sBody = $sNewBody; }
    function getDate(){ return $this->dDate; }
    function getInReplyTo(){ return $this->iInReplyTo; }
    function setInReplyTo($sNewCommentId){ $this->iInReplyTo = $sNewCommentId; }

    function & get($iId) {
        return KTEntityUtil::get('DiscussionComment', $iId);
    }

    function getList($sWhereClause = null) {
        return KTEntityUtil::getList2('DiscussionComment', $sWhereClause);
    }

    function create() {
        if (empty($this->dDate)) {
            $this->dDate = getCurrentDateTime();
        }
        return parent::create();
    }

    function _table () {
        global $default;
        return $default->discussion_comments_table;
    }

    function &createFromArray($aArray) {
        return KTEntityUtil::createFromArray('DiscussionComment', $aArray);
    }

    function &getByThread($oThread) {
        $iThreadId = KTUtil::getId($oThread);
        return KTEntityUtil::getByDict('DiscussionComment', array(
            'thread_id' => $iThreadId,
        ), array(
            'multi' => true,
        ));
    }

    function &getByThreadSortedByDate($oThread) {
        $iThreadId = KTUtil::getId($oThread);
        return KTEntityUtil::getByDict('DiscussionComment', array(
            'thread_id' => $iThreadId,
        ), array(
            'multi' => true,
            'order' => 'date',
        ));
    }
}

?>

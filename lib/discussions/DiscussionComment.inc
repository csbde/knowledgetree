<?php
/**
 * $Id$
 *
 * Represents a document discussion comment.
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

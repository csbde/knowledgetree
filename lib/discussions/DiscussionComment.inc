<?php
/**
 * $Id$
 *
 * Represents a document discussion comment.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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

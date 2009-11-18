<?php
/**
 * $Id$
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
 *
 */

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTUserHistory extends KTEntity {
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'dDateTime' => 'datetime',
        'iUserId' => 'user_id',
        'sActionNamespace' => 'action_namespace',
        'sComments' => 'comments',
        'iSessionId' => 'session_id',
    );

    var $_bUsePearError = true;

    function getUserId() { return $this->iUserId; }
    function setUserId($mValue) { $this->iUserId = $mValue; }
    function getDateTime() { return $this->dDateTime; }
    function setDateTime($mValue) { $this->dDateTime = $sComments; }
    function getComments() { return $this->sComments; }
    function setComments($mValue) { $this->sComments = $sComments; }
    function getActionNamespace() { return $this->sActionNamespace; }
    function setActionNamespace($mValue) { $this->sActionNamespace = $mValue; }
    function getSessionId() { return $this->iSessionId; }
    function setSessionId($mValue) { $this->iSessionId = $mValue; }

    function _table () {
        return KTUtil::getTableName('user_history');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTUserHistory', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTUserHistory', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTUserHistory', $sWhereClause);
    }

    function &getByUser($oUser, $aOptions = null) {
        $aOptions = KTUtil::meldOptions(array(
            'multi' => true,
        ), $aOptions);
        $iUserId = KTUtil::getId($oUser);
        return KTEntityUtil::getByDict('KTUserHistory', array(
            'user_id' => $iUserId,
        ), $aOptions);
    }

    function getLastLogins($aOptions = null) {
        $oUser = KTUtil::arrayGet($aOptions, 'user');
        if ($oUser) {
            $iUserId = KTUtil::getId($oUser);
        } else {
            $iUserId = null;
        }

        $aOptions = KTUtil::meldOptions(array(
            'limit' => 5,
            'orderby' => 'datetime DESC',
        ), $aOptions);

        $sTable = KTUserHistory::_table();
        if ($iUserId) {
            return KTEntityUtil::getByDict($sTable, array(
                'user_id' => $iUserId,
            ), $aOptions);
        } else {
            return KTEntityUtil::getList2('KTUserHistory', null, $aOptions);
        }
    }
}

?>

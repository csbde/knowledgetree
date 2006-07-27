<?php

/**
 * $Id$
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

<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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

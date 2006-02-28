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

/** a _disable_ flag for a particular dashlet, and a particular user.

 shouldn't use an enable flag, since the target user is transient and
 may need to know about the item on creation, not on dashlet registration.
 
*/


class KTDashletDisable extends KTEntity {
        
    /** primary key value */
    var $iId = -1;
    var $iUserId;
    var $sNamespace;

    var $_bUsePearError = true;
        
    function getId() { return $this->iId; }
    function getUserId() { return $this->iUserId; }
    function setUserId($iNewValue) { $this->iUserId = $iNewValue; }
    function getNamespace() { return $this->sNamespace; }
    function setNamespace($sNewValue) {	$this->sNamespace = $sNewValue; }

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iUserId" => "user_id",
        "sNamespace" => "dashlet_namespace",
    );
        
    function _table () {
        return KTUtil::getTableName('dashlet_disable');
    }

    // Static function
    function &get($iId) { return KTEntityUtil::get('KTDashletDisable', $iId); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTDashletDisable', $sWhereClause);	}	
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTDashletDisable', $aOptions); }

    function &getForUserAndDashlet($iUserId, $sNamespace) {
        $sWhereClause = 'WHERE user_id = ? AND dashlet_namespace = ?';
        $aParams = array($iUserId, $sNamespace);
        
        return KTDashletDisable::getList(array($sWhereClause, $aParams));
    }
}

?>
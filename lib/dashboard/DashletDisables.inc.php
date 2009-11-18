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

<?php
/**
 * $Id$
 *
 * Represents the type of link between documents
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

require_once(KT_DIR . '/lib/ktentity.inc');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class LinkType extends KTEntity {
    var $sName;
    var $sDescription;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'sDescription' => 'description',
    );

	function LinkType($sName = null, $sDescription = null) {
		$this->iId = -1;
		$this->sName = $sName;
		$this->sDescription = $sDescription;
	}

	function getDescription() {
		return sanitizeForSQLtoHTML($this->sDescription);
	}

	function setDescription($sNewValue) {
		$this->sDescription = sanitizeForSQL($sNewValue);
	}

	function getName() {
		return sanitizeForSQLtoHTML($this->sName);
	}

	function setName($sNewValue) {
		$this->sName = sanitizeForSQL($sNewValue);
	}

    function _fieldValues () {
        return array(
            'name' => sanitizeForSQLtoHTML($this->sName),
            'description' => sanitizeForSQLtoHTML($this->sDescription),
        );
    }

    function _table () {
        global $default;
        return $default->document_link_types_table;
    }

    function getList($sWhereClause = null) {
            return KTEntityUtil::getList(LinkType::_table(), 'LinkType', $sWhereClause);
    }

    /*
    function &get($id) {
        $sQuery = "SELECT id, name, description FROM " . LinkType::_table() . " WHERE id = ?";
        $aParams = array($id);
        $res = DBUtil::getOneResult(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }
        if (is_null($res)) {
            // XXX: No such key exception type
            return $res;
        }
        $oLinkType =new LinkType($res['name'], $res['description']);
        $oLinkType->iId = $res['id'];
        return $oLinkType;
    }
    */

    function &get($iId) {
        $oObject =& KTEntityUtil::get('LinkType', $iId);
        return $oObject;
    }

}

function &linktypecreateFromArray($aParameters) {
    $oLinkType = new LinkType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oLinkType;
}


?>

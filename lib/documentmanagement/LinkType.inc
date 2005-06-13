<?php
/**
 * $Id$
 *
 * Represents the type of link between documents
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_DIR . '/lib/ktentity.inc');

class LinkType extends KTEntity {
	var $sName;
	var $sDescription;
	
	function LinkType($sName, $sDescription) {
		$this->iId = -1;
		$this->sName = $sName;
		$this->sDescription = $sDescription;
	}
	
	function getDescription() {
		return $this->sDescription;
	}
	
	function setDescription($sNewValue) {
		$this->sDescription = $sNewValue;
	}
	
	function getName() {
		return $this->sName;
	}
	
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
    function _fieldValues () {
        return array(
            'name' => $this->sName,
            'description' => $this->sDescription,
        );
    }
    
    function _table () {
        global $default;
        return $default->document_link_types_table;
    }
	
    function getList($sWhereClause = null) {
            return KTEntityUtil::getList(Link::_table(), 'LinkType', $sWhereClause);
    }

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
        $oLinkType =& new LinkType($res['name'], $res['description']);
        $oLinkType->iId = $res['id'];
        return $oLinkType;
    }
}

function &linktypecreateFromArray($aParameters) {
    $oLinkType = & new LinkType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oLinkType;
}


?>

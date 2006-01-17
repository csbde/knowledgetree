<?php
/**
 * $Id$
 *
 * Represents a role as per the roles database table.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Brad Shuttleworth, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.roles
 */
 
require_once(KT_LIB_DIR . "/ktentity.inc"); 
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 
 
class Role extends KTEntity {
	
	/** role object primary key */
	var $sName;
	
	var $_aFieldToSelect = array(
	    'iId' => 'id',
		'sName' => 'name',
	);
	
	function Role($sNewName = null, $x=null, $y=null) {
		//object not yet created in database
		$this->sName = $sNewName;
	}

    var $_bUsePearError = true;

	function getId() { return $this->iId; }
	function getName() { return $this->sName; }	
	function setName($sNewValue) { $this->sName = $sNewValue; }
	
    function _fieldValues () { return array(
		'name' => $this->sName,
        );
    }

    function _table () { return KTUtil::getTableName('roles'); }
    function get($iRoleId) { return KTEntityUtil::get('Role', $iRoleId); }
	function & getList($sWhereClause = null) { return KTEntityUtil::getList2('Role', $sWhereClause); }
	function & createFromArray($aOptions) { return KTEntityUtil::createFromArray('Role', $aOptions); }
}

?>

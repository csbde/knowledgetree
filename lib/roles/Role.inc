<?php
/**
 * $Id$
 *
 * Represents a role as per the roles database table.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
	
    // STATIC
    function _ktentityOptions() {
        return array(
            'orderby' => 'name',
        );
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

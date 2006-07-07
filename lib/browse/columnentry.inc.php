<?php

/**
 * $Id: columnentry.inc.php 5492 2006-06-04 20:50:43Z bshuttle $
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
 
class KTColumnEntry extends KTEntity {

	
	/** role object primary key */
	var $sViewNamespace;
	var $sColumnNamespace;
    var $iPosition; 
    var $sConfigArray;
    var $bRequired;
	
	var $_aFieldToSelect = array(
	    'iId' => 'id',
		'sColumnNamespace' => 'column_namespace',
		'sViewNamespace' => 'view_namespace',
		'iPosition' => 'position',
		'sConfigArray' => 'config_array',
		'bRequired' => 'required'
	);
	
    var $_bUsePearError = true;

	function getColumnNamespace() { return $this->sColumnNamespace; }	
	function setColumnNamespace($sNewValue) { $this->sColumnNamespace = $sNewValue; }
	function getViewNamespace() { return $this->sViewNamespace; }	
	function setViewNamespace($sNewValue) { $this->sViewNamespace = $sNewValue; }
	function getPosition() { return $this->iPosition; }	
	function setPosition($iNewValue) { $this->iPosition = $iNewValue; }
	function getConfigArray() { return unserialize($this->sConfigArray); }	
	function setConfigArray($aNewValue) { $this->sConfigArray = serialize($aNewValue); }
	function getRequired() { return $this->bRequired; }	
	function setRequired($bNewValue) { $this->bRequired = $bNewValue; }

	
    function _fieldValues () { return array(
		'column_namespace' => $this->sColumnNamespace,
		'view_namespace' => $this->sViewNamespace,		
		'config_array' => $this->sConfigArray,
		'position' => $this->iPosition,
		'required' => $this->bRequired,
        );
    }

    function _table () { return KTUtil::getTableName('column_entries'); }
    function get($iEntryId) { return KTEntityUtil::get('KTColumnEntry', $iEntryId); }
	function & getList($sWhereClause = null) { return KTEntityUtil::getList2('KTColumnEntry', $sWhereClause); }
	function & createFromArray($aOptions) { 
        // transparently convert the options.	
	
	    $aOptions['configarray'] = serialize(KTUtil::arrayGet($aOptions, 'config', array()));
	    unset($aOptions['config']);
	
	    return KTEntityUtil::createFromArray('KTColumnEntry', $aOptions); 
	} 
	
	function & getByView($sViewNamespace, $aOptions = null) {
	    if (is_null($aOptions)) { $aOptions = array(); }
        $aOptions['multi'] = true;
        $aSelect = array('view_namespace' => $sViewNamespace);
	    
    	return KTEntityUtil::getByDict('KTColumnEntry', $aSelect, $aOptions);
	}

}
?>
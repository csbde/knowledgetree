<?php

/**
 * $Id: columnentry.inc.php 5492 2006-06-04 20:50:43Z bshuttle $
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
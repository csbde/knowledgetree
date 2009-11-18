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
        $aOptions['orderby'] = 'position';
        $aSelect = array('view_namespace' => $sViewNamespace);

    	return KTEntityUtil::getByDict('KTColumnEntry', $aSelect, $aOptions);
	}

	/**
	 * Get the postion of the last entry
	 */
	function getNextEntryPosition($sViewNamespace){
	    $sql = "SELECT position FROM column_entries
	       WHERE view_namespace = ?
	       ORDER BY position DESC LIMIT 1";
	    $aParams = array($sViewNamespace);

	    $result = DBUtil::getResultArray(array($sql, $aParams));

	    if(PEAR::isError($result) || empty($result)){
	        return 1000;
	    }
	    return $result[0]['position'] + 1;
	}

    /**
     * Reset the order of the columns
     *
     */
    function reorderColumns($sViewNamespace) {
        // Get the columns in the order they'll appear - first by position then by id
        $sql = "SELECT id, position FROM column_entries
	       WHERE view_namespace = ?
	       ORDER BY position, id ASC";
	    $aParams = array($sViewNamespace);

	    $result = DBUtil::getResultArray(array($sql, $aParams));

	    if(PEAR::isError($result) || empty($result)){
	        return false;
	    }

	    // Set all positions to be unique and in order
	    foreach($result as $key => $column){
	        $position = $column['position'];

	        // If the column position is correct in the order, continue
	        if($position == $key){
	            continue;
	        }

	        // Reset the position
	        $aFields = array();
	        $aFields['position'] = $key;

	        $res = DBUtil::autoUpdate('column_entries', $aFields, $column['id']);
	    }
	    return true;
    }

	/**
	 * Get the next postion up / down
	 */
	function getNextPosition($sViewNamespace, $iId, $position, $dir = 'up') {
	    switch($dir){
	        case 'down':
	            $comp = '>';
	            $order = 'ASC';
	            break;
	        default:
	            $comp = '<';
	            $order = 'DESC';
	    }

	    // Get the column above / below to swop position
	    $sql = "SELECT id, position FROM column_entries
	       WHERE view_namespace = ? AND (position {$comp} ? OR (position = ? AND id {$comp} ?))
	       ORDER BY position {$order} LIMIT 1";
	    $aParams = array($sViewNamespace, $position, $position, $iId);

	    $result = DBUtil::getOneResult(array($sql, $aParams));

	    if(PEAR::isError($result) || empty($result)){
	        return false;
	    }
	    return $result;
	}

	/**
	 * Get the updated position of the column
	 *
	 * @param int $iId
	 * @return int
	 */
	function getNewPosition($iId){
	    // Get the new position
	    $sql = "SELECT id, position FROM column_entries
    	       WHERE id = ?";
	    $aParams = array($iId);
	    $result = DBUtil::getOneResult(array($sql, $aParams));

	    if(PEAR::isError($result) || empty($result)){
	        return false;
	    }
	    return $result['position'];
	}

	/**
	 * Update the position of a column
	 */
	function updatePosition($iId, $position) {
	    $aFields = array('position' => $position);
	    DBUtil::autoUpdate('column_entries', $aFields, $iId);
	}

	/**
	 * Move the display position of the column up / down
	 */
	function movePosition($sViewNamespace, $iId, $dir = 'up') {
	    $position = $this->getPosition();

	    // Get the column to swop position with
	    $next = $this->getNextPosition($sViewNamespace, $iId, $position, $dir);
	    if($next === false){
	        return false;
	    }

	    // Get position of the next column up / down
	    $newPos = $next['position'];
	    $iNextId = $next['id'];

	    if($newPos == $position){
	        // 2 columns have the same position - reorder them
	        $res = $this->reorderColumns($sViewNamespace);
	        if($res === false){
	            return false;
	        }

    	    $position = $this->getNewPosition($iId);
    	    if($position === false){
	            return false;
	        }

    	    // Get the column to swop with
    	    $next = $this->getNextPosition($sViewNamespace, $iId, $position, $dir);
    	    if($next === false){
    	        return false;
    	    }
    	    $newPos = $next['position'];
    	    $iNextId = $next['id'];
	    }

	    // update the columns
	    $this->updatePosition($iId, $newPos);
	    $this->updatePosition($iNextId, $position);
	    return true;
	}
}
?>

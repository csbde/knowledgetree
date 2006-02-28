<?php
/**
 * $Id$
 *
 * Represents time units.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class TimeUnit extends KTEntity {
	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The time unit
	 */
	var $sName;
	
	/**
	 * Constructs an time unit instance
	 *
	 * @param string the time unit name 
	 */
	function TimeUnit($sNewName) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->sName = $sNewName;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the name
	 */
	function getName() {
		return $this->sName;
	}

	/**
	 * Sets the name
	 *
	 * @param string the new name
	 */
	function setName($sNewName){
		$this->sName = $sNewName;
	}

    function _fieldValues () {
        return array(
            'name' => $this->sName,
        );
    }

    function _table () {
        global $default;
        $sTable = $default->time_unit_lookup_table;
    }

    /**
     * Static function.  Given a news item primary key will create
     * a TimeUnit object and populate it with the corresponding
     * database values
     *
     * @return TimeUnit populated TimeUnit object on success, false otherwise
     */
    function & get($iTimeUnitID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * FROM $default->time_unit_lookup_table WHERE id = ?", $iTimeUnitID));/*ok*/
        if ($sql->next_record()) {
            $oTimeUnit = & new TimeUnit($sql->f("name"));
            $oTimeUnit->iId = $iTimeUnitID;
            return $oTimeUnit;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of TimeUnit objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of TimeUnit objects, false otherwise
     */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(TimeUnit::_table(), 'TimeUnit', $sWhereClause);
    }    
}

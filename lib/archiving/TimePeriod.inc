<?php
/**
 * $Id$
 *
 * Represents a time period.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class TimePeriod extends KTEntity {
	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The time units
	 */
	var $iTimeUnitID;
	/**
	 * The number of units
	 */
	var $iUnits;
	
	/**
	 * Constructs an time period instance
	 *
	 * @param date the time unit
	 * @param integer the expiration time period id
	 */
	function TimePeriod($iNewTimeUnitID, $iNewUnits) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iTimeUnitID = $iNewTimeUnitID;
		$this->iUnits = $iNewUnits;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the time unit
	 */
	function getTimeUnitID() {
		return $this->iTimeUnitID;
	}

	/**
	 * Sets the time unit
	 *
	 * @param integer the new time unit
	 */
	function setTimeUnitID($iNewTimeUnitID){
		$this->iTimeUnitID = $iNewTimeUnitID;
	}

	/**
	 * Gets the units
	 */
	function getUnits(){
		return $this->iUnits;
	}

	/**
	 * Sets the units
	 * 
	 * @param integer the new units
	 */ 
	function setUnits($iNewUnits){
		$this->iUnits = $iNewUnits;
	}

    function _fieldValues () {
        return array(
            'time_unit_id' => $this->iTimeUnitID,
            'units' => $this->iUnits
        );
    }

    function _table () {
        global $default;
        return $default->time_period_table;
    }

    /**
     * Static function.  Given a primary key will create
     * a TimePeriod object and populate it with the corresponding
     * database values
     *
     * @return TimePeriod populated TimePeriod object on success, false otherwise
     */
    function & get($iTimePeriodID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->time_period_table WHERE id = $iTimePeriodID");
        if ($sql->next_record()) {
            $oTimePeriod = & new TimePeriod($sql->f("time_unit_id"), $sql->f("units"));
            $oTimePeriod->iId = $iTimePeriodID;
            return $oTimePeriod;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of TimePeriod objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of TimePeriod objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aTimePeriodArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->time_period_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oTimePeriod = & TimePeriod::get($sql->f("id"));
                $aTimePeriodArray[$iCount++] = $oTimePeriod;
            }
            return $aTimePeriodArray;
        }
        return false;
    }    
}

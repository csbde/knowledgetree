<?php

/**
 * $Id$
 * 
 * Represents a time period
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class TimePeriod {

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

	/**
	 * Inserts the time period into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->time_period_table (time_unit_id, units) " .
                                  "VALUES ($this->iTimeUnitID, $this->iUnits)");
            if ($result) {
                //set the current primary key
                $this->iId = $sql->insert_id();
                return true;
            }
            return false;
        }
        return false;
	}

    /**
     * Update the time period current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->time_period_table SET " .
					  "time_unit_id = $this->iTimeUnitID, " .								  
                      "units = $this->iUnits " .                                  
           			  "WHERE id = $this->iId";
            $result = $sql->query($sQuery);
            if ($result) {
                return true;
            }
            return false;
        }
        return false;
	}

    /**
     * Delete the current time period from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM $default->time_period_table WHERE id = $this->iId");
            if ($result) {
                $this->iId = -1;
                return true;
            }
            return false;
        }
        return false;
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
<?php
/**
 * $Id$
 *
 * Represents time units.
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
class TimeUnit {

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

	/**
	 * Inserts the time unit into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->time_unit_lookup_table (name) " .
                                  "VALUES ('" . addslashes($this->sName) . "')");
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
     * Update the current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->time_unit_lookup_table SET " .
					  "name = '" . addslashes($this->sName) . "' " .                                
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
     * Delete the current archive type from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM $default->time_unit_lookup_table WHERE id = $this->iId");
            if ($result) {
                $this->iId = -1;
                return true;
            }
            return false;
        }
        return false;
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
        $sql->query("SELECT * FROM $default->time_unit_lookup_table WHERE id = $iTimeUnitID");
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
        global $default;
        $aTimeUnitArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->time_unit_lookup_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oTimeUnit = & TimeUnit::get($sql->f("id"));
                $aTimeUnitArray[$iCount++] = $oTimeUnit;
            }
            return $aTimeUnitArray;
        }
        return false;
    }    
}
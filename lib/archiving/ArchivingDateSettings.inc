<?php

/**
 * $Id$
 * 
 * Represents archive date settings for a document
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class ArchivingDateSettings {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The expiration date
	 */
	var $dExpirationDate;
	/**
	 * The expiration time period
	 */
	var $iTimePeriodID;
	
	/**
	 * Constructs an archive date settings instance
	 *
	 * @param date the expiration date
	 * @param integer the expiration time period id
	 */
	function ArchivingDateSettings($dNewExpirationDate, $iNewTimePeriodID) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->setExpirationDate($dNewExpirationDate);
		$this->iTimePeriodID = $iNewTimePeriodID;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the expiration date
	 */
	function getExpirationDate() {
		return ($this->dExpirationDate == "NULL" ? "" : $this->dExpirationDate);
	}

	/**
	 * Sets the expiration date
	 *
	 * @param date the new expiration date
	 */
	function setExpirationDate($dNewExpirationDate){
		$this->dExpirationDate = strlen($dNewExpirationDate) == 0 ? "NULL" : $dNewExpirationDate;
	}

	/**
	 * Gets the time period id
	 */
	function getTimePeriodID(){
		return $this->iTimePeriodID;
	}

	/**
	 * Sets the time period id
	 * 
	 * @param integer the new time period id
	 */ 
	function setTimePeriodID($iNewTimePeriodID){
		$this->iTimePeriodID = $iNewTimePeriodID;
	}

	/**
	 * Inserts the archive date settings into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $sQuery = "INSERT INTO $default->owl_archiving_date_settings_table (expiration_date, time_period_id) " .
                      "VALUES (" . $this->addQuotes($this->dExpirationDate) . ", $this->iTimePeriodID)";
            $result = $sql->query($sQuery);
                                  $default->log->info($sQuery);
            if ($result) {
                //set the current primary key
                $this->iId = $sql->insert_id();
                return true;
            }
            return false;
        }
        return false;
	}
	function addQuotes($sDate) {
		if ($sDate == "NULL") {
			return $sDate;
		} else {
			return "'$sDate'";
		}
	}
	
    /**
     * Update the archive date settings current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->owl_archiving_date_settings_table SET " .
					  "expiration_date = " . $this->addQuotes($this->dExpirationDate) . ", " .								  
                      "time_period_id = $this->iTimePeriodID " .                                  
           			  "WHERE id = $this->iId";
           			  $default->log->info($sQuery);
            $result = $sql->query($sQuery);
            if ($result) {
                return true;
            }
            return false;
        }
        return false;
	}

    /**
     * Delete the current archive date settings from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM $default->owl_archiving_date_settings_table WHERE id = $this->iId");
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
     * a ArchivingDateSettings object and populate it with the corresponding
     * database values
     *
     * @return ArchivingDateSettings populated ArchivingDateSettings object on success, false otherwise
     */
    function & get($iArchivingDateSettingsID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->owl_archiving_date_settings_table WHERE id = $iArchivingDateSettingsID");
        if ($sql->next_record()) {
            $oArchivingDateSettings = & new ArchivingDateSettings($sql->f("expiration_date"), $sql->f("time_period_id"));
            $oArchivingDateSettings->iId = $iArchivingDateSettingsID;
            return $oArchivingDateSettings;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchivingDateSettings objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchivingDateSettings objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchivingDateSettingsArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->owl_archiving_date_settings_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oArchivingDateSettings = & ArchivingDateSettings::get($sql->f("id"));
                $aArchivingDateSettingsArray[$iCount++] = $oArchivingDateSettings;
            }
            return $aArchivingDateSettingsArray;
        }
        return false;
    }    
}
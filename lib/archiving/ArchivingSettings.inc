<?php

/**
 * $Id$
 * 
 * Represents archive settings for a document.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class ArchivingSettings {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The archiving method- date or utilisation
	 */
	var $iArchivingTypeID;	
	/**
	 * The expiration date
	 */
	var $dExpirationDate;
	/**
	 * The document transaction id
	 */
	var $iDocumentTransactionID;	
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
	function ArchivingSettings($iNewArchivingTypeID, $dNewExpirationDate, $iNewDocumentTransactionID, $iNewTimePeriodID) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iArchivingTypeID = $iNewArchivingTypeID;
		$this->setExpirationDate($dNewExpirationDate);
		$this->iDocumentTransactionID = $iNewDocumentTransactionID;
		$this->iTimePeriodID = $iNewTimePeriodID;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the archiving type
	 */
	function getArchivingTypeID(){
		return $this->iArchivingTypeID;
	}

	/**
	 * Sets the archiving type
	 * 
	 * @param integer the new archiving type
	 */ 
	function setArchivingTypeID($iNewArchivingTypeID){
		$this->iArchivingTypeID = $iNewArchivingTypeID;
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
	 * Gets the document transaction id
	 */
	function getDocumentTransactionID() {
		return $this->iDocumentTransactionID;
	}

	/**
	 * Sets the document transaction id
	 *
	 * @param integer the new document transaction id
	 */
	function setDocumentTransactionID($iNewDocumentTransactionID){
		$this->iDocumentTransactionID = $iNewDocumentTransactionID;
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
            $sQuery = "INSERT INTO $default->archiving_settings_table (archiving_type_id, expiration_date, document_transaction_id, time_period_id) " .
                      "VALUES ($this->iArchivingTypeID, " . $this->addQuotes($this->dExpirationDate) . ", $this->iDocumentTransactionID, $this->iTimePeriodID)";
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
			$sQuery = "UPDATE $default->archiving_settings_table SET " .
                      "archiving_type_id = $this->iArchivingTypeID, " . 
					  "expiration_date = " . $this->addQuotes($this->dExpirationDate) . ", " .
					  "document_transaction_id = $this->iDocumentTransactionID, " .								  
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
            $result = $sql->query("DELETE FROM $default->archiving_settings_table WHERE id = $this->iId");
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
     * a ArchivingSettings object and populate it with the corresponding
     * database values
     *
     * @return ArchivingSettings populated ArchivingSettings object on success, false otherwise
     */
    function & get($iArchivingSettingsID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->archiving_settings_table WHERE id = $iArchivingSettingsID");
        if ($sql->next_record()) {
            $oArchivingSettings = & new ArchivingSettings($sql->f("archiving_type_id"), 
            											  $sql->f("expiration_date"),
            											  $sql->f("document_transaction_id"),
            											  $sql->f("time_period_id"));
            $oArchivingSettings->iId = $iArchivingSettingsID;
            return $oArchivingSettings;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchivingSettings objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchivingSettings objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchivingSettings = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->archiving_settings_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            while ($sql->next_record()) {
                $oArchivingSettings = & ArchivingSettings::get($sql->f("id"));
                $aArchivingSettings[] = $oArchivingSettings;
            }
            return $aArchivingSettings;
        }
        return false;
    }    
}
?>
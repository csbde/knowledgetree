<?php

/**
 * $Id$
 * 
 * Represents archive utilisation settings for a document
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class ArchivingUtilisationSettings {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The document transaction id
	 */
	var $iDocumentTransactionID;
	/**
	 * The expiration time period
	 */
	var $iTimePeriodID;
	
	/**
	 * Constructs an archive utilisation settings instance
	 *
	 * @param date the expiration date
	 * @param integer the expiration time period id
	 */
	function ArchivingUtilisationSettings($iNewDocumentTransactionID, $iNewTimePeriodID) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
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
	 * Inserts the archive utilisation settings into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->owl_archiving_utilisation_settings_table (document_transaction_id, time_period_id) " .
                                  "VALUES ($this->iDocumentTransactionID, $this->iTimePeriodID)");
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
     * Update the archive utilisation settings current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->owl_archiving_utilisation_settings_table SET " .
					  "document_transaction_id = $this->iDocumentTransactionID, " .								  
                      "time_period_id = $this->iTimePeriodID " .                                  
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
     * Delete the current archive utilisation settings from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM $default->owl_archiving_utilisation_settings_table WHERE id = $this->iId");
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
     * a ArchivingUtilisationSettings object and populate it with the corresponding
     * database values
     *
     * @return ArchivingUtilisationSettings populated ArchivingUtilisationSettings object on success, false otherwise
     */
    function & get($iArchivingUtilisationSettingsID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->owl_archiving_utilisation_settings_table WHERE id = $iArchivingUtilisationSettingsID");
        if ($sql->next_record()) {
            $oArchivingUtilisationSettings = & new ArchivingUtilisationSettings($sql->f("document_transaction_id"), $sql->f("time_period_id"));
            $oArchivingUtilisationSettings->iId = $iArchivingUtilisationSettingsID;
            return $oArchivingUtilisationSettings;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchivingUtilisationSettings objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchivingUtilisationSettings objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchivingUtilisationSettingsArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->owl_archiving_utilisation_settings_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oArchivingUtilisationSettings = & ArchivingUtilisationSettings::get($sql->f("id"));
                $aArchivingUtilisationSettingsArray[$iCount++] = $oArchivingUtilisationSettings;
            }
            return $aArchivingUtilisationSettingsArray;
        }
        return false;
    }    
}
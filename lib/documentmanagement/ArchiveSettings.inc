<?php

/**
 * $Id$
 * 
 * Represents archive settings for a document
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.dashboard 
 */

class ArchiveSettings {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The document ID
	 */
	var $iDocumentID;
	/**
	 * The expiration date
	 */ 
	var $dExpirationDate;
	/**
	 * The utilisation threshold
	 */
	var $iUtilisationThreshold;
	
	/**
	 * Constructs an archive settings instance
	 *
	 * @param integer the document id
	 * @param date the expiration date
	 * @param integer the utilisation threshold
	 */
	function ArchiveSettings($iNewDocumentID, $dNewExpirationDate, $iNewUtilisationThreshold) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->dExpirationDate = $dNewExpirationDate;
		$this->iUtilisationThreshold = $iNewUtilisationThreshold;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the document id
	 */
	function getDocumentID(){
		return $this->iDocumentID;
	}

	/**
	 * Sets the document id
	 *
	 * @param string the new document id
	 */
	function setDocumentID($iNewDocumentID){
		$this->iDocumentID = $iNewDocumentID;
	}

	/**
	 * Gets the expiration date
	 */
	function getExpirationDate(){
		return $this->dExpirationDate;
	}

	/**
	 * Sets the expiration date
	 * 
	 * @param date the new expiration date
	 */ 
	function setExpirationDate($dNewExpirationDate){
		$this->dExpirationDate = $dNewExpirationDate;
	}

	/**
	 * Gets the utilisation threshold
	 */
	function getUtilisationThreshold(){
		return $this->iUtilisationThreshold;
	}

	/**
	 * Sets the utilisation threshold
	 *
	 * @param integer the utilisation threshold
	 */
	function setUtilisationThreshold($iNewUtilisationThreshold){
		$this->iUtilisationThreshold = $iNewUtilisationThreshold;
	}

	/**
	 * Inserts the archive settings into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->owl_archive_settings_table  (document_id, expiration_date, utilisation_threshold) " .
                                  "VALUES ($this->iDocumentID, '$this->dExpirationDate', $this->iUtilisationThreshold)");
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
     * Update the archive settings current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE " . $default->owl_archive_settings_table . " SET " .
								  "document_id = " . $this->iDocumentID . ", " .
                                  "expiration_date = '" . $this->dExpirationDate . "', " .
                                  "utilisation_theshold = $this->iUtilisationThreshold)" .                                  
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
     * Delete the current archive settings from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM  " . $default->owl_archive_settings_table . "  WHERE id = $this->iId");
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
     * a ArchiveSettings object and populate it with the corresponding
     * database values
     *
     * @return ArchiveSettings populated ArchiveSettings object on success, false otherwise
     */
    function & get($iArchiveSettingsID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->owl_archive_settings_table WHERE id = $iArchiveSettingsID");
        if ($sql->next_record()) {
            $oArchiveSettings = & new ArchiveSettings($sql->f("document_id"), $sql->f("expiration_date"), $sql->f("utilisation_threshold"));
            $oArchiveSettings->iId = $iArchiveSettingsID;
            return $oArchiveSettings;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchiveSettings objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchiveSettings objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchiveSettingsArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->owl_archive_settings_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oArchiveSettings = & ArchiveSettings::get($sql->f("id"));
                $aArchiveSettingsArray[$iCount++] = $oArchiveSettings;
            }
            return $aArchiveSettingsArray;
        }
        return false;
    }    
}
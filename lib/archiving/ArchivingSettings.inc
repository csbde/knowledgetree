<?php
/**
 * $Id$
 *
 * Represents archive settings for a document.
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

class ArchivingSettings extends KTEntity {

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

    function _fieldValues () {
        return array(
            'archiving_type_id' => $this->iArchivingTypeID,
            'expiration_date' => $this->dExpirationDate,
            'document_transaction_id' => $this->iDocumentTransactionID,
            'time_period_id' => $this->iTimePeriodID,
        );
    }

    function _table () {
        global $default;
        return $default->archiving_settings_table;
    }

	function addQuotes($sDate) {
		if ($sDate == "NULL") {
			return $sDate;
		} else {
			return "'$sDate'";
		}
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
        $sql->query(array("SELECT * FROM $default->archiving_settings_table WHERE id = ?", $iArchivingSettingsID));/*ok*/
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
        return KTEntityUtil::getList(ArchivingSettings::_table(), 'ArchivingSettings', $sWhereClause);
    }    
}
?>

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
class DocumentArchiving {
	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The document ID
	 */
	var $iDocumentID;

	/**
	 * The archiving settings
	 */
	var $iArchivingSettingsID;	
	
	/**
	 * Constructs an archive settings instance
	 *
	 * @param integer the document id
	 * @param integer the archiving type id
	 * @param integer the archiving settings id
	 */
	function DocumentArchiving($iNewDocumentID, $iNewArchivingSettingsID) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iArchivingSettingsID = $iNewArchivingSettingsID;
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
	 * @param integer the new document id
	 */
	function setDocumentID($iNewDocumentID){
		$this->iDocumentID = $iNewDocumentID;
	}
	
	/**
	 * Gets the archiving settings
	 */
	function getArchivingSettingsID(){
		return $this->iArchivingSettingsID;
	}

	/**
	 * Sets the archiving settings
	 * 
	 * @param integer the new archiving settings
	 */ 
	function setArchivingSettingsID($iNewArchivingSettingsID){
		$this->iArchivingSettingsID = $iNewArchivingSettingsID;
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
            $result = $sql->query("INSERT INTO $default->document_archiving_table (document_id, archiving_settings_id) " .
                                  "VALUES ($this->iDocumentID, $this->iArchivingSettingsID)");
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
			$sQuery = "UPDATE $default->document_archiving_table SET " .
					  "document_id = $this->iDocumentID, " .								  
                      "archiving_settings_id = $this->iArchivingSettingsID " .                                  
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
            $result = $sql->query("DELETE FROM $default->document_archiving_table WHERE id = $this->iId");
            if ($result) {
                $this->iId = -1;
                return true;
            }
            return false;
        }
        return false;
    }	

    /**
     * Static function.  Given a document primary key will create
     * a DocumentArchiving object and populate it with the corresponding
     * database values
     *
     * @return DocumentArchiving populated DocumentArchiving object on success, false otherwise
     */
    function & getFromDocumentID($iDocumentID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->document_archiving_table WHERE document_id = $iDocumentID");
        if ($sql->next_record()) {
            $oDocumentArchiving = & new DocumentArchiving($sql->f("document_id"), $sql->f("archiving_settings_id"));
            $oDocumentArchiving->iId = $sql->f("id");
            return $oDocumentArchiving;          
        }
        return false;
    }
    
    /**
     * Static function.  Given a news item primary key will create
     * a DocumentArchiving object and populate it with the corresponding
     * database values
     *
     * @return DocumentArchiving populated DocumentArchiving object on success, false otherwise
     */
    function & get($iDocumentArchivingID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->document_archiving_table WHERE id = $iDocumentArchivingID");
        if ($sql->next_record()) {
            $oDocumentArchiving = & new DocumentArchiving($sql->f("document_id"), $sql->f("archiving_settings_id"));
            $oDocumentArchiving->iId = $iDocumentArchivingID;
            return $oDocumentArchiving;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of DocumentArchiving objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of DocumentArchiving objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aDocumentArchivingArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->document_archiving_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDocumentArchiving = & DocumentArchiving::get($sql->f("id"));
                $aDocumentArchivingArray[$iCount++] = $oDocumentArchiving;
            }
            return $aDocumentArchivingArray;
        }
        return false;
    }    
}
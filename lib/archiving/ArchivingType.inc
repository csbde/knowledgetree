<?php

/**
 * $Id$
 * 
 * Represents archiving types
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class ArchivingType {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The archiving type
	 */
	var $sName;
	
	/**
	 * Constructs an archiving type instance
	 *
	 * @param string the archiving type name 
	 */
	function ArchivingType($sNewName) {
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
	 * Inserts the archive type into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->archiving_type_lookup_table (name) " .
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
     * Update the archive type's current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->archiving_type_lookup_table SET " .
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
            $result = $sql->query("DELETE FROM $default->archiving_type_lookup_table WHERE id = $this->iId");
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
     * a ArchivingType object and populate it with the corresponding
     * database values
     *
     * @return ArchivingType populated ArchivingType object on success, false otherwise
     */
    function & get($iArchivingTypeID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->archiving_type_lookup_table WHERE id = $iArchivingTypeID");
        if ($sql->next_record()) {
            $oArchivingType = & new ArchivingType($sql->f("name"));
            $oArchivingType->iId = $iArchivingTypeID;
            return $oArchivingType;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchivingType objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchivingType objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchivingTypeArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->archiving_type_lookup_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oArchivingType = & ArchivingType::get($sql->f("id"));
                $aArchivingTypeArray[$iCount++] = $oArchivingType;
            }
            return $aArchivingTypeArray;
        }
        return false;
    }    
}
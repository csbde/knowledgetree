<?php

/**
 * $Id$
 * 
 * Represents restoration request for a document
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.archiving 
 */

class ArchiveRestorationRequest {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The document to be restored
	 */
	var $iDocumentID;
	/**
	 * The user requesting the restoration
	 */
	var $iRequestUserID;
	/**
	 * The administrator to perform the restore
	 */
	var $iAdminUserID;
	/**
	 * Timestamp
	 */
	var $dDateTime;			
	
	/**
	 * Constructs an archive restoration request instance
	 *
	 * @param integer the document id
	 * @param integer the request user id
	 * @param integer the admin user id
	 */
	function ArchiveRestorationRequest($iNewDocumentID, $iNewRequestUserID, $iNewAdminUserID, $dNewDateTime = "") {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iRequestUserID = $iNewRequestUserID;
		$this->iAdminUserID = $iNewAdminUserID;
		$this->dDateTime = strlen($dNewDateTime) == 0 ? getCurrentDateTime() : $dNewDateTime;
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
	 * Gets the request user
	 */
	function getRequestUserID(){
		return $this->iRequestUserID;
	}

	/**
	 * Sets the request user
	 * 
	 * @param integer the new user id
	 */ 
	function setRequestUserID($iNewRequestUserID){
		$this->iRequestUserID = $iNewRequestUserID;
	}
	
	/**
	 * Gets the admin user
	 */
	function getAdminUserID(){
		return $this->iAdminUserID;
	}

	/**
	 * Sets the admin user
	 * 
	 * @param integer the new user id
	 */ 
	function setAdminUserID($iNewAdminUserID){
		$this->iAdminUserID = $iNewAdminUserID;
	}

	/**
	 * Gets the datetime
	 */
	function getDateTime(){
		return $this->dDateTime;
	}

	/**
	 * Sets the datetime
	 * 
	 * @param datetime the new date time
	 */ 
	function setDateTime($dNewDateTime){
		$this->dDateTime = $dNewDateTime;
	}
			
	/**
	 * Inserts the restoration request into the database
	 *
	 * @return boolean true on successful update, false otherwise
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->archive_restoration_table (document_id, request_user_id, admin_user_id, datetime) " .
                                  "VALUES ($this->iDocumentID, $this->iRequestUserID, $this->iAdminUserID, '$this->dDateTime')");
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
     * Update the restoration request current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE $default->archive_restoration_table SET " .
					  "document_id = $this->iDocumentID, " .								  
                      "request_user_id = $this->iRequestUserID, " .
                      "admin_user_id = $this->iAdminUserID, " .
                      "datetime = '$this->dDateTime' " .                                  
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
     * Delete the current restoration request from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM $default->archive_restoration_table WHERE id = $this->iId");
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
     * a ArchiveRestorationRequest object and populate it with the corresponding
     * database values
     *
     * @return ArchiveRestorationRequest populated ArchiveRestorationRequest object on success, false otherwise
     */
    function & getFromDocumentID($iDocumentID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->archive_restoration_table WHERE document_id = $iDocumentID");
        if ($sql->next_record()) {
            $oArchiveRestorationRequest = & new ArchiveRestorationRequest($sql->f("document_id"), $sql->f("request_user_id"), $sql->f("admin_user_id"), $sql->f("datetime"));
            $oArchiveRestorationRequest->iId = $sql->f("id");
            return $oArchiveRestorationRequest;          
        }
        return false;
    }
    
    /**
     * Static function.  Given a news item primary key will create
     * a ArchiveRestorationRequest object and populate it with the corresponding
     * database values
     *
     * @return ArchiveRestorationRequest populated ArchiveRestorationRequest object on success, false otherwise
     */
    function & get($iArchiveRestorationRequestID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT * FROM $default->archive_restoration_table WHERE id = $iArchiveRestorationRequestID");
        if ($sql->next_record()) {
            $oArchiveRestorationRequest = & new ArchiveRestorationRequest($sql->f("document_id"), $sql->f("request_user_id"), $sql->f("admin_user_id"), $sql->f("datetime"));
            $oArchiveRestorationRequest->iId = $iArchiveRestorationRequestID;
            return $oArchiveRestorationRequest;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchiveRestorationRequest objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchiveRestorationRequest objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchiveRestorationRequestArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->archive_restoration_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            while ($sql->next_record()) {
                $aArchiveRestorationRequestArray[] = & ArchiveRestorationRequest::get($sql->f("id"));
            }
            return $aArchiveRestorationRequestArray;
        }
        return false;
    }    
}
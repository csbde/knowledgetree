<?php
/**
 * $Id$
 *
 * Represents restoration requests for a document.
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
            $sTable = $default->archive_restoration_table;
            $aFieldValues = array(
                'document_id' => $this->iDocumentID,
                'request_user_id' => $this->iRequestUserID,
                'admin_user_id' => $this->iAdminUserID,
                'datetime' => $this->dDateTime,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
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

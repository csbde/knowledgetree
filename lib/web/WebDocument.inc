<?php

DEFINE("PENDING",1);
// FIXME: duplicate status in Document- need one class that defines all lookup constants
if (!defined("PUBLISHED")) {
	DEFINE("PUBLISHED", 2);
}
DEFINE("NOT_PUBLISHED",3);
/**
 * $Id$
 *
 * Represents a web document as per the web_documents table in the database.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.web
 */
class WebDocument extends KTEntity {
	
	/** primary key of web document */
	var $iId;
	/** primary key of document to which this web document related*/
	var $iDocumentID;
	/** primary key of web site on which document is published */
	var $iWebSiteID;
	/** unit to which this document belongs */
	var $iUnitID;
	/** status of document */
	var $iStatusID;
	
	var $dDateTime;
	
	function WebDocument($iNewDocumentID, $iNewWebSiteID, $iNewUnitID, $iNewStatusID, $dNewDateTime) {
		//object not yet created in database
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iWebSiteID = $iNewWebSiteID;
		$this->iUnitID = $iNewUnitID;
		$this->iStatusID = $iNewStatusID;
		$this->dDateTime = $dNewDateTime;
	}
    
    /** primary key accessor method */
    function getID() {
        return $this->iId;
    }
    
    /** document id accessor methods */
    function getDocumentID() {
        return $this->iDocumentID;
    }
	function setDocumentID($iNewValue) {
		$this->iDocumentID = $iNewValue;
	}
    
    /** status id accessor methods */
    function getStatusID() {
        return $this->iStatusID;
    }
	function setStatusID($iNewValue) {
		$this->iStatusID = $iNewValue;
	}
    
    /** website id accessor methods */
    function getWebSiteID() {
        return $this->iWebSiteID;
    }    
    function setWebSiteID($iNewValue) {
        $this->iWebSiteID = $iNewValue;
    }
    
    /** modification datetime accessor methods */
    function getDateTime() {
        return $this->dDateTime;
    }
    function setDateTime($dNewValue) {
        $this->dDateTime = $dNewValue;
    }
    
    /** return the document display path */
	function getDisplayPath() {
        $oDocument = Document::get($this->iDocumentID);
        return $oDocument->getDisplayPath();
    }

    function _fieldValues () {
        return array(
            'document_id' => $this->iDocumentID,
            'web_site_id' => $this->iWebSiteID,
            'unit_id' => $this->iUnitID,
            'status_id' => $this->iStatusID,
            'datetime' => $this->dDateTime,
        );
    }

    function _table () {
        global $default;
        return $default->web_documents_table;
    }
    
	/**
	* Static function.
	* Given a web_documents primary key it will create a 
	* WebDocuments object and populate it with the 
	* corresponding database values
	*
	* @return WebDocument populated WebDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iWebDocumentsID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->web_documents_table WHERE id = ?", $iWebDocumentsID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oWebDocument = & new WebDocument($sql->f("document_id"), $sql->f("web_site_id"), $sql->f("unit_id"), $sql->f("status_id"), $sql->f("datetime"));
				$oWebDocument->iId = $iWebDocumentsID;
				return $oWebDocument;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iWebDocumentsID . " table = $default->web_documents_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	* Static function
	* Get a list of web documents
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of WebDocument objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aWebDocumentArray = array();
		$sql = $default->db;
        $sQuery = "SELECT * FROM " . $default->web_documents_table;
        if (isset($sWhereClause)) {
            $sQuery .= " WHERE " . $sWhereClause;
        }
		$result = $sql->query($sQuery);
		if ($result) {			
			while ($sql->next_record()) {
				$aWebDocumentArray[]= & WebDocument::get($sql->f("id"));
			}
			return $aWebDocumentArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>

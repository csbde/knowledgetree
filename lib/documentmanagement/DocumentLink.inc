<?php 
/**
 * $Id$
 *
 * Represents a document link as per the database document_link table.
 * Links a child document to a parent document.  Direction of relationship
 * is defined through document collaboration.
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
 * @package lib.documentmanagement 
 */
class DocumentLink extends KTEntity {

	/** primary key value */
	var $iId;
	/** primary key of user responsible for creating document */
	var $iParentDocumentID;
	/** document title name */	
	var $iChildDocumentID;
	

	/**
	* Default constructor
	*
	* @param 	Parent document primary key
	* @param 	Child document primary key
	*
	*/
	function DocumentLink($iNewParentDocumentID, $iNewChildDocumentID) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->iParentDocumentID = $iNewParentDocumentID;
		$this->iChildDocumentID = $iNewChildDocumentID;
	}

	/**
	* Get the document field's primary key value
	*
	* @return int document field's primary key value
	*
	*/
	function getID() {
		return $this->iId;
	}

	/**
	* Get the primary key of the parent document
	*/
	function getParentDocumentID() {
		return $this->iParentDocumentID;
	}

	/**
	* Set the primary key of the parent document
	*
	* @param 	Primary key of parent document
	*
	*/
	function setParentDocumentID($iNewValue) {
		$this->iParentDocumentID = $iNewValue;
	}

	/**
	* Get the child document's primary key
	*
	* @return int primary key of child document
	*
	*/
	function getChildDocumentID() {
		return $this->iChildDocumentID;
	}

	/**
	* Set the child document's primary key
	*
	* @param 	Primary key of child document
	*
	*/
	function setChildDocumentID($iNewValue) {
		$this->iChildDocumentID = $iNewValue;
	}

    function _fieldValues () {
        return array(
            'parent_document_id' => $this->iParentDocumentID,
            'child_document_id' => $this->iChildDocumentID,
        );
    }

    function _table () {
        global $default;
        return $default->document_link_table;
    }

	/**
	* Static function.
	* Given a document_fields primary key it will create a 
	* DocumentFields object and populate it with the 
	* corresponding database values
	*
	* @return DocumentField populated DocumentField object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDocumentLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->document_link_table WHERE id = $iDocumentLinkID");
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentLink = & new DocumentLink($sql->f("parent_document_id"), $sql->f("parent_document_id"));
				$oDocumentLink->iId = $sql->f("id");
				return $oDocumentLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist."id = ".$iDocumentLinkID." table = $default->document_link_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}
?>

<?php 
/**
 *
 * Represents a document link as per the database document_link table.
 * Links a child document to a parent document.  Direction of relationship
 * is defined through document collaboration.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @author Bryn Divey, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement 
 */
class DocumentLink extends KTEntity {

	/** primary key value */
	var $iId;
	/** primary key of user responsible for creating document */
	var $iParentDocumentId;
	/** document title name */	
	var $iChildDocumentId;
    /** type of link */
    var $iLinkTypeId;


    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iParentDocumentId' => 'parent_document_id',
        'iChildDocumentId' => 'child_document_id',
        'iLinkTypeId' => 'link_type_id',
    );


	/**
	* Default constructor
	*
	* @param 	Parent document primary key
	* @param 	Child document primary key
	* @param 	Link type primary key
	*
	*/
	function DocumentLink($iNewParentDocumentID = null, $iNewChildDocumentID = null, $iLinkTypeId = null) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->iParentDocumentId = $iNewParentDocumentID;
		$this->iChildDocumentId = $iNewChildDocumentID;
		$this->iLinkTypeId = $iLinkTypeId;
	}

	function getID() { return $this->iId; }
	function getParentDocumentID() { return $this->iParentDocumentId; }
	function setParentDocumentID($iNewValue) { $this->iParentDocumentId = $iNewValue; }
	function getChildDocumentID() { return $this->iChildDocumentId;	}
	function setChildDocumentID($iNewValue) { $this->iChildDocumentId = $iNewValue; }
	function getLinkTypeID() { return $this->iLinkTypeId; }
	function setLinkTypeID($iNewValue) { $this->iLinkTypeId = $iNewValue; }

	/**
	* Helper getters
	*/
	
	/**
	* get parent document
	*/
	function & getParentDocument() {
	    return Document::get($this->getParentDocumentId());
    }
	
	/**
	* get child document
	*/
	function & getChildDocument() {
	    $oDocument = Document::get($this->getChildDocumentId());
		return $oDocument;
    }
	
	/**
	* get link type
	*/
	function & getLinkType() {
	    return LinkType::get($this->getLinkTypeId());
    }



    function _fieldValues () {
        return array(
            'parent_document_id' => $this->iParentDocumentId,
            'child_document_id' => $this->iChildDocumentId,
            'link_type_id' => $this->iLinkTypeId,
        );
    }

    function _table () {
        global $default;
        return $default->document_link_table;
    }


	// static boilerplate
	function & get($iDocumentLinkID) {
		global $default;
		
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->document_link_table WHERE id = ?", $iDocumentLinkID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oDocumentLink = & new DocumentLink($sql->f("parent_document_id"), $sql->f("child_document_id"), $sql->f("link_type_id"));
				$oDocumentLink->iId = $sql->f("id");
				return $oDocumentLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist."id = ".$iDocumentLinkID." table = $default->document_link_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList2('DocumentLink', $sWhereClause);
    }
	function &createFromArray($aArray) { return KTEntityUtil::createFromArray('DocumentLink', $aArray); }

    /**
     * Static function
     * Get a list of DocumentLinks where iDocumentId is the parent
     *
     * @param  Integer  Document ID of parent
     *
     * @return Array array of DocumentLink objects, false otherwise.
     */
    function getLinksFromDocument($iDocumentId) {
        return KTEntityUtil::getList2('DocumentLink', sprintf("parent_document_id = %d", $iDocumentId));
    }
	
    /**
     * Static function
     * Get a list of DocumentLinks where iDocumentId is the child
     *
     * @param  Integer  Document ID of child
     *
     * @return Array array of DocumentLink objects, false otherwise.
     */
    function getLinksToDocument($iDocumentId) {
        return KTEntityUtil::getList2('DocumentLink', sprintf("child_document_id = %d", $iDocumentId));
    }
}

?>

<?php
/**
 *
 * Represents a document link as per the database document_link table.
 * Links a child document to a parent document.  Direction of relationship
 * is defined through document collaboration.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KnowledgeTree
 * @version Version 0.9
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
    /** external url */
    var $sTargetUrl;
    /** external name */
    var $sTargetName;


    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iParentDocumentId' => 'parent_document_id',
        'iChildDocumentId' => 'child_document_id',
        'iLinkTypeId' => 'link_type_id',
        'sTargetUrl' => 'external_url',
        'sTargetName' => 'external_name',
    );


	/**
	* Default constructor
	*
	* @param 	Parent document primary key
	* @param 	Child document primary key
	* @param 	Link type primary key
	*
	*/
	function DocumentLink($iNewParentDocumentID = null, $iNewChildDocumentID = null, $iLinkTypeId = null, $sTargetUrl = null, $sTargetName = null) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->iParentDocumentId = $iNewParentDocumentID;
		$this->iChildDocumentId = $iNewChildDocumentID;
		$this->iLinkTypeId = $iLinkTypeId;
		$this->sTargetUrl = $sTargetUrl;
		$this->sTargetName = $sTargetName;
	}

	function getID() { return $this->iId; }
	function getParentDocumentID() { return $this->iParentDocumentId; }
	function setParentDocumentID($iNewValue) { $this->iParentDocumentId = $iNewValue; }
	function getChildDocumentID() { return $this->iChildDocumentId;	}
	function setChildDocumentID($iNewValue) { $this->iChildDocumentId = $iNewValue; }
	function getLinkTypeID() { return $this->iLinkTypeId; }
	function setLinkTypeID($iNewValue) { $this->iLinkTypeId = $iNewValue; }
	function getTargetUrl() { return $this->sTargetUrl; }
	function setTargetUrl($iNewValue) { $this->sTargetUrl = $iNewValue; }
	function getTargetName() { return $this->sTargetName; }
	function setTargetName($iNewValue) { $this->sTargetName = $iNewValue; }

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
            'external_url' => $this->sTargetUrl,
            'external_name' => $this->sTargetName,
        );
    }

    function _table () {
        global $default;
        return $default->document_link_table;
    }

	// static boilerplate
	function & get($iDocumentLinkID) {
	    $table = DocumentLink::_table();
	    $query = "SELECT * FROM $table WHERE id = '$iDocumentLinkID'";
	    $result = DBUtil::getOneResult($query);

	    if(PEAR::isError($result)) {
	        $_SESSION['errorMessage'] = $result->getMessage();
	        return false;
	    }

	    if(!empty($result)){
	        $oDocumentLink = new DocumentLink($result['parent_document_id'], $result['child_document_id'], $result['link_type_id'], $result['external_url'], $result['external_name']);
	        $oDocumentLink->iId = $result['id'];
	        return $oDocumentLink;
	    }
	    $_SESSION['errorMessage'] = sprintf(_kt('The object does not exist. id = %s table = %s ') , $iDocumentLinkID ,$table);
	    return false;
	}

    function getList($sWhereClause = null) {
        return KTEntityUtil::getList2('DocumentLink', $sWhereClause);
    }

	function &createFromArray($aArray) {
	    return KTEntityUtil::createFromArray('DocumentLink', $aArray);
	}

    /**
     * Static function
     * Get a list of DocumentLinks where iDocumentId is the parent
     *
     * @param  Integer  Document ID of parent
     *
     * @return Array array of DocumentLink objects, false otherwise.
     */
    function getLinksFromDocument($iDocumentId) {
        $sWhere = "parent_document_id = $iDocumentId AND child_document_id != $iDocumentId";
        return KTEntityUtil::getList2('DocumentLink', $sWhere);
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
        $sWhere = "child_document_id = $iDocumentId AND parent_document_id != $iDocumentId";
        return KTEntityUtil::getList2('DocumentLink', $sWhere);
    }

    /**
     * Static function
     * Get a list of external DocumentLinks where iDocumentId is the parent
     *
     * @param  Integer  Document ID of parent
     *
     * @return Array array of DocumentLink objects, false otherwise.
     */
    function getExternalLinks($iDocumentId) {
        $sWhere = "child_document_id = $iDocumentId AND parent_document_id = $iDocumentId";
        return KTEntityUtil::getList2('DocumentLink', $sWhere);
    }
}

?>

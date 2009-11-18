<?php
/**
 * $Id$
 *
 * Represents a document field link as per the database table document_fields_link.
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
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . "/util/sanitize.inc");

class DocumentFieldLink extends KTEntity {

	/** document field link primary key */
	var $iId;
	/** primary key of document to which field is linked */
	var $iMetadataVersionId;
	/** primary key of field to which document is linked */
	var $iDocumentFieldId;
	/** field value */
	var $sValue;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iMetadataVersionId' => 'metadata_version_id',
        'iDocumentFieldId' => 'document_field_id',
        'sValue' => 'value',
    );

	/**
	* Default constructor
	*
	* @param 	Primary key of document to which field is linked
	* @param 	Primary key of field to which document is linked
	* @param 	Value of field
	*
	*/
	function DocumentFieldLink($iNewDocumentId = null, $iNewDocumentFieldId = null, $sNewValue = null) {
		//object not create in database yet
		$this->iId = -1;
		$this->iDocumentId = $iNewDocumentId;
		$this->iDocumentFieldId = $iNewDocumentFieldId;
		$this->sValue = $sNewValue;
	}

	/**
	* Get the document field link primary key
	*
	*  @return int document field link primary key
	*
	*/
	function getId() {
		return $this->iId;
	}

	/**
	* Get the primary key of the document to which the field is linked
	*
	* @return int document primary key to which the field is linked
	*
	*/
	function getMetadataVersionId() {
		return $this->iMetadataVersionId;
	}

	/**
	* Set the primary key of the document to which the field is linked
	*
	* @param 	Document primary key to which field is linked
	*
	*/
	function setMetadataVersionId($iNewValue) {
		$this->iMetadataVersionId = $iNewValue;
	}

	/**
	* Get the primary key of the field to which the document is linked
	*
	* @return int primary key of field to which the document is related
	*
	*/
	function getDocumentFieldId() {
		return $this->iDocumentFieldId;
	}

	/**
	* Set the primary key of the field to which the document is related
	*
	* @param 	New primary key of field to which document is related
	*
	*/
	function setDocumentFieldId($iNewVale) {
		$this->iDocumentFieldId = $iNewValue;
	}

	/**
	* Get the value of the field
	*
	* @return String value of the field
	*
	*/
	function getValue() {
		return $this->sValue;
	}

	/**
	* Set the value of the field
	*
	* @param 	New value of the field
	*
	*/
	function setValue($sNewValue) {
		$this->sValue = sanitizeForSQL($sNewValue);
	}

    function _table () {
        return KTUtil::getTableName('document_fields_link');
    }

	/**
	*
	* Static function.  Given a document fields link primary key will create
	* a DocumentFieldLink object and populate it with the corresponding
	* database values
	*
	* @return DocumentFieldLink populated DocumentFieldLink object on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function &get($iId) {
        return KTEntityUtil::get('DocumentFieldLink', $iId);
	}

    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('DocumentFieldLink', $sWhereClause);
    }


    function &getByDocument($oDocument) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        $iMetadataVersionId = $oDocument->getMetadataVersionId();
        return KTEntityUtil::getByDict('DocumentFieldLink', array(
            'metadata_version_id' => $iMetadataVersionId,
        ), array('multi' => true));
    }

    function &getByDocumentAndField($oDocument, $oField) {
        $oDocument =& KTUtil::getObject('Document', $oDocument);
        $iMetadataVersionId = $oDocument->getMetadataVersionId();
        return KTEntityUtil::getByDict('DocumentFieldLink', array(
            'metadata_version_id' => $iMetadataVersionId,
            'document_field_id' => KTUtil::getId($oField),
        ));
    }

    function clearAllCaches() {
        return KTEntityUtil::clearAllCaches('DocumentFieldLink');
    }
}
?>

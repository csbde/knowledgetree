<?php 
/**
 * $Id$
 *
 * Represents a dependant document template as per the database table dependant_document_template.
 * Used to chain documents together in the collaboration process.
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
class DependantDocumentTemplate extends KTEntity {

	/** primary key value */
	var $iId;
	/** primary key of user responsible for creating document */
	var $iDefaultUserID;
	/** document title name */	
	var $sDocumentTitle;
	/** primary key of document to use as template */
	var $iTemplateDocumentID;	
	/** primary key of folder collaboration step that template belongs to */
	var $iGroupFolderApprovalLinkID;

	/**
	* Default constructor
	*
	* @param 	Name of document field
	* @param 	Document field data type
	*
	*/
	function DependantDocumentTemplate($sNewDocumentTitle, $iNewDefaultUserID, $iNewGroupFolderApprovalLinkID, $iNewTemplateDocumentID = null) {
		//object not created yet
		global $default;
		$this->iId = -1;
		$this->sDocumentTitle = $sNewDocumentTitle;
		$this->iDefaultUserID = $iNewDefaultUserID;
		$this->iTemplateDocumentID = $iNewTemplateDocumentID;
		$this->iGroupFolderApprovalLinkID = $iNewGroupFolderApprovalLinkID;
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
	* Get the primary key of the user responsbile for new document creation
	*/
	function getDefaultUserID() {
		return $this->iDefaultUserID;
	}
	
	function setDefaultUserID($iNewValue) {
		$this->iDefaultUserID = $iNewValue;
	}

	/**
	* Set the document field's name
	*
	* @param 	Document field's new name
	*
	*/
	function setDocumentTitle($sNewValue) {
		$this->sDocumentTitle = $sNewValue;
	}

	/**
	* Get the depedant document's title
	*
	* @return String dependant document's title	*
	*/
	function getDocumentTitle() {
		return $this->sDocumentTitle;
	}

	
	/**
	* Get the primary key of the template document
	*
	* @return int Primary key of template document
	*
	*/
	function getTemplateDocumentID() {
		return $this->iTemplateDocumentID;
	}

	/**
	* Set the template document's primary key
	*
	* @param 	Template document's primary key
	*
	*/
	function setTemplateDocumentID($iNewValue) {
		$this->iTemplateDocumentID = $iNewValue;
	}
	
	function getGroupFolderApprovalLinkID() {
		return $this->iGroupFolderApprovalLinkID;
	}
	
	function setGroupFolderApprovalLinkID($iNewValue) {
		$this->iGroupFolderApprovalLinkID = $iNewValue;
	}

    function _fieldValues () {
        return array(
            'document_title' => $this->sDocumentTitle,
            'default_user_id' => $this->iDefaultUserID,
            'template_document_id' => $this->iTemplateDocumentID,
            'group_folder_approval_link_id' => $this->iGroupFolderApprovalLinkID,
        );
    }

    function _table () {
        global $default;
        return $default->dependant_document_template_table;
    }

	/**
	* Static function.
	* Given a dependant_documents primary key it will create a 
	* DependantDocument object and populate it with the 
	* corresponding database values
	*
	* @return DependantDocument populated DependantDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDependantDocumentID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $default->dependant_document_template_table WHERE id = ?", $iDependantDocumentID));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$DependantDocumentTemplate = & new DependantDocumentTemplate($sql->f("document_title"), $sql->f("default_user_id"), $sql->f("group_folder_approval_link_id"), $sql->f("template_document_id"));
				$DependantDocumentTemplate->iId = $sql->f("id");
				/*if (!($sql->f("template_document_id") == null)) {
					 $DependantDocumentTemplate->setTemplateDocumentID($sql->f("template_document_id"));
				}*/
				return $DependantDocumentTemplate;
			}
			return false;
		}
		return false;
	}
	
	/**
    * Static function
    * Get a list of DependantDocumentTemplate
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of DependantDocumentTemplates objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(DependantDocumentTemplate::_table(), 'DependantDocumentTemplate', $sWhereClause);
    }
}

?>

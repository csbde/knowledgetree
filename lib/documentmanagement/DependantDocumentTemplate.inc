<?php 
/**
*
* Class DependantDocument
*
* Represents a dependant document as per the database table dependant_document
* Used to chain documents together in the collaboration process
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 14 May 2003
* @package lib.documentmanagement
*/


class DependantDocumentTemplate {

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
		$this -> sDocumentTitle = $sNewValue;
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
		return $this -> iTemplateDocumentID;
	}

	/**
	* Set the template document's primary key
	*
	* @param 	Template document's primary key
	*
	*/
	function setTemplateDocumentID($iNewValue) {
		$this -> iTemplateDocumentID = $iNewValue;
	}
	
	function getGroupFolderApprovalLinkID() {
		return $this->iGroupFolderApprovalLinkID;
	}
	
	function setGroupFolderApprovalLinkID($iNewValue) {
		$this->iGroupFolderApprovalLinkID = $iNewValue;
	}

	/**
	* Store the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_database, $lang_err_object_exists;		 
		//if the object hasn't been created	
		if ($this -> iId < 0) {
			$sql = $default -> db;
			$result = $sql -> query("INSERT INTO $default->owl_dependant_document_template_table (document_title, default_user_id,template_document_id, group_folder_approval_link_id) VALUES ('" . addslashes($this->sDocumentTitle) . "', $this->iDefaultUserID, " . (($this->iTemplateDocumentID == null) ? "NULL" : $this->iTemplateDocumentID) . ", $this->iGroupFolderApprovalLinkID)");
			if ($result) {
				$this -> iId = $sql -> insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists."id = ".$this->iId." Table = $default->owl_dependant_document_template_table";
		return false;
	}

	/**
	* Update the values in the database table with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this -> iId > 0) {
			$sql = $default -> db;
			$result = $sql -> query("UPDATE $default->owl_dependant_document_template_table SET document_title = '".addslashes($this -> sDocumentTitle)."', default_user_id = $this->iDefaultUserID, template_document_id = " . (($this->iTemplateDocumentID == null) ? "NULL" : $this->iTemplateDocumentID) . ", group_folder_approval_link_id = $this->iGroupFolderApprovalLinkID WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}

	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this -> iId >= 0) {
			$sql = $default -> db;
			$result = $sql -> query("DELETE FROM $default->owl_dependant_document_template_table WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
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
		$sql = $default -> db;
		$result = $sql -> query("SELECT * FROM $default->owl_dependant_document_template_table WHERE id = $iDependantDocumentID");
		if ($result) {
			if ($sql -> next_record()) {
				$DependantDocumentTemplate = & new DependantDocumentTemplate(stripslashes($sql -> f("document_title")), $sql -> f("default_user_id"), $sql->f("group_folder_approval_link_id"), $sql->f("template_document_id"));
				$DependantDocumentTemplate -> iId = $sql -> f("id");
				/*if (!($sql -> f("template_document_id") == null)) {
					 $DependantDocumentTemplate->setTemplateDocumentID($sql->f("template_document_id"));
				}*/
				return $DependantDocumentTemplate;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist."id = ".$iDependantDocumentID." table = $default->owl_dependant_document_template_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
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
        global $default, $lang_err_database;
        $aDependantDocumentTemplateArray;
        settype($aDependatDocumentTemplateArray, "array");
        $sql = $default->db;
        // TODO: join on sys_deleted
        $result = $sql->query("SELECT * FROM " . $default->owl_dependant_document_template_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDependantDocumentTemplate = & DependantDocumentTemplate::get($sql->f("id"));
                $aDependantDocumentTemplateArray[$iCount] = $oDependantDocumentTemplate;
                $iCount++;
            }
            return $aDependantDocumentTemplateArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
}

?>
<?php

/**
*	Class DocumentLib
*
*	Contains miscellaneous static helper functions concerned with
*	document management
*/

class DocumentLib {
	
	/**
	* Gets the folder id for a document
	*
	* @param $iDocumentID		Document primary key
	*
	* @return int (folder id) on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getDocumentFolderID($iDocumentID) {
		global $lang_err_doc_no_folder;
		$sql = new Owl_DB();
		$sql->query("SELECT folder_id from " . $default->owl_documents_table . " WHERE id = " . $iDocumentID);
		if ($sql->next_record()) {
			return $sql->f("folder_id");
		}
		$_SESSION["errorMessage"] = $lang_err_doc_no_folder . "document id " . $iDocumentID;
		return false;
	}
	
	/**
	* Check if a document already exists
	*
	* @param $sName			Name of document
	* @param $sFolderID		Primary key of folder to which document is assigned
	*
	* @return boolean true if document exists, false otherwise and set $_SESSION["errorMessage"]
	*/	
	function documentExists($sName, $iFolderID) {
		global $default, $lang_err_doc_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_documents_table . " WHERE name = '" . $sName "' AND folder_id = " . $iFolderID);
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_doc_not_exist . "name = " . $sName . " folder_id = " . $iFolderID;
		return false;
	}
	
	/** 
	* Create a new document type
	* 
	* @param $sName		Name of new document type
	*
	* @return boolean true on successful creation, false otherwise and set $default->errorMessage 
	*/
	function createDocumentType($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		//if the document type doesn't exist
		if (!(DocumentManager::documentTypeExists($sName))) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO  " . $default->owl_document_types_table . "  (name) values ('" . $sName . "')");			
			if (!$result) {
				$_SESSION["errorMessage"] = "Database Error. Failed to insert document type " . $sName;
				return false;
			}
			return true;
		}
		$_SESSION["errorMessage"] = "A document type with this name already exists";
		return false;
	}
	
	
}

?>

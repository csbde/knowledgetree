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
		if (sql->next_record()) {
			return sql->f("folder_id");
		}
		$_SESSION["errorMessage"] = $lang_err_doc_no_folder
		return false;
	}
	
	
}

?>

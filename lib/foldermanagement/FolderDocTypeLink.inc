<?php
/**
* Represents an entry in the database as per the folder_doctype_link table
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 27 February 2003 
* @package
*/

class FolderDocTypeLink {
	
	/** primary key of object */
	var $iId;
	/** primary key of folder */
	var $iFolderID;
	/** primary key of document type */
	var $iDocumentTypeID;
	
	function FolderDocTypeLink($iNewFolderID, $iNewDocumentTypeID) {
		$this->iId = -1;
		$this->iFolderID = $iNewFolderID;
		$this->iDocumentTypeID = $iNewDocumentTypeID;
	}
	
	/**
	* Create the current folder in the database
	*
	* @return boolean true and set $this->iId with new primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function create() {
		global $default, $lang_err_database; $lang_err_object_exists;
		//if the object has not already been stored		
		if ($this->iId < 0) {			
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_folder_doctypes_table . " (folder_id, document_type_id) " .
						"VALUES ($this->iFolderID, $this->iDocumentTypeID)");
			if ($result) {
				$this->iId = $sql->insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = folders";
		return false;
	}
	
	/**
	* Update the current folder values in the database
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//can only update the object if it has already been stored
		$sOldPath;
		if ($this->iId >= 0) {
			if ($bPathChange){
				$sOldPath = $default->documentRoot . "/" . $this->sFullPath . "/" . $this->sName;
			}
			$sql = $default->db;
			$sQuery = "UPDATE " . $default->owl_folder_doctypes_table . " SET " .
							"folder_id = $this->iFolderID, " .
							"document_type_id = $this->iDocumentTypeID " .							
							"WHERE id = " . $this->iId;
			$result = $sql->query($sQuery);
			if ($result) {			
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;			
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;		
	}
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true and reset id to -1 on successful delete, false otherwise and set $_SESSION["errorMessage"]
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM " . $default->owl_folder_doctypes_table . " WHERE id = " . $this->iId);
			if ($result) {
				$this->iId = -1;
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
	* Given a folder_doctype_link primary key will create
	* a FolderDocTypeLink object and populate it with the corresponding
	* database values
	*
	* @param $iFolderDocTypeID		Primary key of folder to get
	*
	* @return Folder folder object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
	*/
	function get($iFolderDocTypeLinkID) {		
		global $default,  $lang_err_object_not_exist;
		$sql = $default->db;		
		$sql->query("SELECT * FROM " . $default->owl_folder_doctypes_table . " WHERE id = " . $iFolderDocTypeLinkID);
		if ($sql->next_record()) {
			$oFolderDocTypeLink = & new FolderDocTypeLink($sql->f("folder_id"), $sql->f("document_type_id"));
            $oFolderDocTypeLink->iId = $iFolderDocTypeLinkID;			
			return $oFolderDocTypeLink;
		} 
 		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderID . " table = folders";
		return false;
	}
	
	/**
    * Static function
    * Get a list of Documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Documents objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        global $default, $lang_err_database;
        $aFolderDocTypeLinkArray;
        settype($aFolderDocTypeLinkArray, "array");
        $sql = $default->db;
        // TODO: join on sys_deleted
        $result = $sql->query("SELECT * FROM " . $default->owl_folder_doctypes_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oFolderDocTypeLink = & FolderDocTypeLink::get($sql->f("id"));
                $aFolderDocTypeLinkArray[$iCount] = $oFolderDocTypeLink;
                $iCount++;
            }
            return $aFolderDocTypeLinkArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
	
}
?>

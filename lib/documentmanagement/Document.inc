<?php
/**
*
* Class Document
*
* Represents a document as per the documents database table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 15 January 2003
*
* @todo add in document change transaction functionality
*
* @package lib.documentmanagement
*/

class Document {
	/** primary key */
	var $iId;
	/** document type primary key */
	var $iDocumentTypeID;
	/** document name */
	var $sName;
	/** document file name (path to document on file system) */
	var $sFileName;
	/** document file size */
	var $iSize;
	/** primary key of user who created document */
	var $iCreatorID;
	/** date the document was last modified */
	var $dModified;
	/** file description */
	var $sDescription;
	/** primary key of file mime type */
	var $iMimeTypeID;
	/** primary key of folder under which document is stored */
	var $iFolderID;
	/** major revision number */
	var $iMajorVersion;
	/** minor revision number */
	var $iMinorVersion;
	/** document checked out status */
	var $bIsCheckedOut;
	
	/**
	* Document class constructor
	*
	* @param $sName				File Name	
	* @param $iSize				File size in bytes
	* @param $iCreatorID		Primary key of user who created document
	* @param $sDescription		Description
	* @param $iMimeID			Primary key of file mime type
	* @param $iFolderID			Primary key of folder to which document belongs	
	*	
	*/
	function Document($sNewName, $sNewFileName, $iNewSize, $iNewCreatorID, $iNewMimeID, $iNewFolderID, $sNewDescription = "None") {
		$this->iId = -1;	//primary key not set as document is not stored yet
		$this->sName = $sNewName;
		$this->iSize = $iNewSize;
		$this->iCreatorID = $iNewCreatorID;
		$this->sFileName = $sNewFileName;
		$this->sDescription = $sNewDescription;
		$this->iMimeTypeID = $iNewMimeID;
		$this->iFolderID = $iNewFolderID;
		$this->iDocumentTypeID = Folder::getFolderDocumentType($this->iFolderID);
		$this->iMajorVersion = 0;
		$this->iMinorVersion = 1;
		$this->bIsCheckedOut = false;
	}
	
	/** Get the document primary key */
	function getID() {
		return $this->iID;
	}
	
	/** Get the document type id */
	function getDocumentTypeID() {
		return $this->iDocumentTypeID;
	}
	
	/** set the document type id */
	function setDocumentTypeID($sNewValue) {
		$this->iDocumentTypeID = $sNewValue;
	}
	
	/** get the document name */
	function getName() {
		return $this->sName;
	}
	
	/** set the document name */
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/** get the document path on the file system */
	function getFileName() {
		return $this->sFileName;
	}
	
	/** set the document path on the file system */
	function setFileName() {
		$this->sFileName = $sNewValue;
	}
	
	/** get the primary key of the folder in which the document is stored */
	function getFolderID() {
		return $this->iFolderID;
	}
	
	/** set the primary key of the folder in which the document is stored */
	function setFolderID($iNewValue) {
		$this->iFolderID = $iNewValue;
	}
	
	/** get the document file size in bytes */
	function getFileSize() {
		return $this->iSize;
	}
	
	/** set the document file size in bytes */
	function setFileSize($iNewValue) {
		$this->iSize = $iNewValue;
	}
	
	/** get the document creator id */
	function getCreatorID() {
		return $this->iCreatorID;
	}
	
	/** set the document creator id */
	function setCreatorID($iNewValue) {
		$this->iCreatorID = $iNewValue;
	}
	
	/** get the document last modified date */
	function getLastModifiedDate() {
		return $this->dModified;
	}
	
	/** set the document last modified date */
	function setLastModifiedDate($dNewValue) {
		$this->dModified = $dNewValue;
	}
	
	/** get the document description */
	function getDescription() {
		return $this->sDescription;
	}
	
	/** set the document description */
	function setDescription($sNewValue) {
		$this->sDescription = $sNewValue;
	}
	
	/** get the document mime type primary key */
	function getMimeTypeID() {
		return $this->iMimeTypeID;
	}
	
	/** get the document mime type primary key */
	function setMimeTypeID($iNewValue) {
		$this->iMimeTypeID = $iNewValue;
	}
	
	/** get the major version number */
	function getMajorVersionNumber() {
		return $this->iMajorVersion;
	}
	
	/** set the major version number */
	function setMajorVersionNumber($iNewValue) {
		$this->iMajorVersion = $iNewValue;
	}
	
	/** get the minor version number */
	function getMinorVersionNumber() {
		return $this->iMinorVersion;
	}
	
	/** set the minor version number */
	function setMinorVersionNumber($iNewValue) {
		$this->iMinorVersionNumber = $iNewValue;
	}
	
	/** get the document check out status */
	function getIsCheckedOut() {
		return $this->bCheckedOut;
	}
	
	/** set the document check out status */
	function setIsCheckedOut($bNewValue) {
		$this->bCheckedOut = $bNewValue;
	}
	
	/**
	* Insert the current document into the database
	*
	* @return boolean true on successful insert, false otherwise and set $_SESSION["errorMessage"]
	*/
	function create() {
		global $default, $lang_err_doc_exist, $lang_err_database;
		//if the id >= 0, then the object has already been created
		if ($this->iId < 0) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_documents_table . " (document_type_id, name, filename, size, creator_id, modified, description, mime_id, folder_id, major_version, minor_version, is_checked_out) " .
						"VALUES (" . $this->iDocumentTypeID . ", '" . addslashes($this->sName) . "', '" . addslashes($this->sFileName) . "', $this->iSize, $this->iCreatorID, '" . getCurrentDateTime() . "', '" . addslashes($this->sDescription) . "', $this->iMimeTypeID, $this->iFolderID, $this->iMajorVersion, $this->iMinorVersion, " . ($this->bIsCheckedOut ? 1 : 0) . ")");
			if ($result) {
				//set the current documents primary key
				$this->iId = $sql->insert_id();
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;			
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = documents";
		return false;
		
	}
	
	/**
	* Update the documents current values in the database
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("UPDATE " . $default->owl_documents_table . " SET " .
						"document_typeid = $this->iDocumentTypeID, " . 
						"file_name = '" . addslashes($this->file_name) . "', " .
						"size = $this->iSize, " .
						"creator_id = $this->iCreatorID, " .
						"modified = " . getCurrentDateTime() . ", " .
						"description = '" . addslashes($this->sDescription) . "', " .
						"mime_id = $this->iMimeTypeID, " .
						"folder_id = $this->iFolderID, " .
						"major_revision = $this->iMajorRevision, " .
						"minor_revision =  $this->iMinorRevision, " .
						"is_checked_out = $this->bIsCheckedOut " .
					"WHERE id = $this->id");
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
	* Delete the current document from the database.  Set the primary key to -1 
	* on successful deletion
	*
	* @return boolean true and reset id to -1 on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*/
	function delete() {	
		global $default, $lang_err_database, $lang_err_object_key;
		if ($this->iId >= 0) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_documents_table . "  WHERE id = $this->iId");
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
	*
	* Static function.  Given a document primary key will create
	* a document object and populate it with the corresponding
	* database values
	*
	* @return Document populated Document object on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iDocumentID) {
		global $default, $lang_err_doc_not_exist;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM $default->owl_documents_table WHERE id = $iDocumentID");
		if ($sql->next_record()) {			
			$oDocument = & new Document(stripslashes($sql->f("name")), $sql->f("filename"), $sql->f("size"), stripslashes($sql->f("creator_id")), $sql->f("mime_id"), $sql->f("folder_id"), $sql->f("description"));			
			$oDocument->setDocumentTypeID($sql->f("document_type_id"));
			$oDocument->setMajorVersionNumber($sql->f("major_version"));
			$oDocument->setMinorVersionNumber($sql->f("minor_version"));
			$oDocument->setIsCheckedOut($sql->f("is_checked_out"));
			$oDocument->iId = $iDocumentID;
			return $oDocument;
		}
		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = documents";
		return false;		
	}
	
	/**
	* Static function.
	* Get all the document field's associated with a document type
	*
	* @param 	Document type primary key
	* @param 	Get only the mandatory fields
	*
	* @return array array of document field objects, false otherwise and $_SESSION["errorMessage"]
	*/
	function getDocumentFieldsForDocumentType($iDocumentTypeID, $bMandatoryOnly = false) {
		$aDocumentFieldArray;
		settype($aDocumentFieldArray,"array");
		$sql = new Owl_DB();
		$result = $sql->query("SELECT DF.id AS id, DF.name AS name, DF.data_type AS data_type FROM document_fields AS DF INNER JOIN document_type_fields_link AS DTFL ON DF.id = DTFL.field_id WHERE DTFL.document_type_id = $iDocumentTypeID " . ($bMandatoryOnly ? "AND DFTL.is_mandatory = 1 " : " ") . "ORDER BY DF.name ASC");
		if ($result) {
			$iCount = 0;
			while ($sql->next_record()) {
				$oDocumentField = DocumentField::get($sql->f("id"));
				if (!($oDocumentField === false)) {
					$aDocumentFieldArray[$iCount] = $oDocumentField;
					$iCount++;
				}
			}
			return $aDocumentFieldArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
		
	}
	
	/**
	* Get a document's transaction history
	*
	* @return Array array of DocumentTransaction objects
	*
	*/
	function getDocumentHistory() {
		global $default, $lang_err_database;
		$aDocumentHistory;
		settype($aDocumentHistory, "array");
		$sql = new Owl_DB();
		$result = $sql->query("SELECT * FROM  " . $default->owl_document_transactions_table . " WHERE document_id = $this->iId ORDER BY datetime ASC");
		if ($result) {
			$iCount = 0;
			while($sql->next_record()) {				
				$oDocumentTransaction = DocumentTransaction::get($sql->f("id"));
				$aDocumentHistory[$iCount] = $oDocumentTransaction;
				$iCount++;				 
			}
			return $history;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
		
	}

	/**
	* Static function.
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
		$sql->query("SELECT * FROM $default->owl_documents_table WHERE name = '" . addslashes($sName) . "' AND folder_id = $iFolderID");
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_doc_not_exist . "name = " . $sName . " folder_id = " . $iFolderID;
		return false;
	}	
	
}

?>

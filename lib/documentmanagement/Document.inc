<?php
/**
 * $Id$
 *
 * Represents a document as per the documents database table
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement
 * @todo add in document change transaction functionality
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
    /** the user that currently has the document checked out */
    var $iCheckedOutUserID;    
	/** comma delimited string of folder hierarch this document falls under */
	var $sParentFolderIDs;
	/** forward slash deliminated path from file system root */
	var $sFullPath;

    /**
    * Document class constructor
    *
    * @param $sName    File Name 
    * @param $iSize    File size in bytes
    * @param $iCreatorID  Primary key of user who created document
    * @param $sDescription  Description
    * @param $iMimeID   Primary key of file mime type
    * @param $iFolderID   Primary key of folder to which document belongs 
    * 
    */
    function Document($sNewName, $sNewFileName, $iNewSize, $iNewCreatorID, $iNewMimeID, $iNewFolderID, $sNewDescription = "None") {
        $this->iId = -1; //primary key not set as document is not stored yet
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
        $this->iCheckedOutUserID = -1;
    }

    /** Get the document primary key */
    function getID() {
        return $this->iId;
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
        $this->iMinorVersion = $iNewValue;
    }

    /** get the document check out status */
    function getIsCheckedOut() {
        return $this->bIsCheckedOut;
    }

    /** set the document check out status */
    function setIsCheckedOut($bNewValue) {
        $this->bIsCheckedOut = $bNewValue;
    }
	
    /** get the user id that has the document checked out **/
    function getCheckedOutUserID() {
        return $this->iCheckedOutUserID;
    }

    /** set the user id that has the document checked out **/    
    function setCheckedOutUserID($iNewValue) {
        $this->iCheckedOutUserID = $iNewValue;
    }

	/**
	* Generate a comma delimited string containing
	* the parent folder ids
	* 
	* @return String	comma delimited string containing the parent folder ids
	*/
	function generateParentFolderIDS($iFolderID) {
		global $default;
		//if the folder is not the root folder 
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query("SELECT parent_id FROM $default->owl_folders_table WHERE ID = $iFolderID");
			$sql->next_record();
			return $this->generateParentFolderIDS($sql->f("parent_id")) . ",$iFolderID";
		}
		return;
		
	}
	
	/**
	* Forward slash deliminated string giving full path of document
	* from file system root url
	* 
	*/
	function generateFullFolderPath($iFolderID) {
		global $default;
		//if the folder is not the root folder 
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query("SELECT name, parent_id FROM $default->owl_folders_table WHERE ID = $iFolderID");
			$sql->next_record();			
			return $this->generateFullFolderPath($sql->f("parent_id")) . "/" . stripslashes($sql->f("name"));
		}
		return;
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
            $sql = $default->db;
			$sFullPath = $this->generateFullFolderPath($this->iFolderID); 
			$this->sFullPath = substr($sFullPath, 1, strlen($sFullPath));
			$sParentFolderIDs = $this->generateParentFolderIDS($this->iFolderID);
			$this->sParentFolderIDs = substr($sParentFolderIDs, 1, strlen($sParentFolderIDs));
            $result = $sql->query("INSERT INTO " . $default->owl_documents_table . " (document_type_id, name, filename, size, creator_id, modified, description, mime_id, folder_id, major_version, minor_version, is_checked_out, parent_folder_ids, full_path) " .
                                  "VALUES (" . $this->iDocumentTypeID . ", '" . addslashes($this->sName) . "', '" . addslashes($this->sFileName) . "', $this->iSize, $this->iCreatorID, '" . getCurrentDateTime() . "', '" . addslashes($this->sDescription) . "', $this->iMimeTypeID, $this->iFolderID, $this->iMajorVersion, $this->iMinorVersion, " . ($this->bIsCheckedOut ? 1 : 0) . ", '" . $this->sParentFolderIDs . "','" . addslashes($this->sFullPath) . "')");
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
    function update($aForMove = false) {
        global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE " . $default->owl_documents_table . " SET " .
                                  "document_type_id = $this->iDocumentTypeID, " .
                                  "filename = '" . addslashes($this->sFileName) . "', " .
                                  "size = $this->iSize, " .
                                  "creator_id = $this->iCreatorID, " .
                                  "modified = '" . getCurrentDateTime() . "', " .
                                  "description = '" . addslashes($this->sDescription) . "', " .
                                  "mime_id = $this->iMimeTypeID, " .
                                  "folder_id = " . $this->iFolderID . ", " .
                                  "major_version = " . $this->iMajorVersion . ", " .
                                  "minor_version = " . $this->iMinorVersion . ", ";
								  if ($aForMove) {
									  //only update these if the document is being moved
									  $sFullPath = $this->generateFullFolderPath($this->iFolderID); 
									  $this->sFullPath = substr($sFullPath, 1, strlen($sFullPath));
									  $sParentFolderIDs = $this->generateParentFolderIDS($this->iFolderID);
									  $this->sParentFolderIDs = substr($sParentFolderIDs, 1, strlen($sParentFolderIDs));
									  $sQuery .= "parent_folder_ids = '" . addslashes($this->sParentFolderIDs) . "'," .
									  			"full_path = '" . addslashes($this->sFullPath) . "', ";
								  }					
                                  $sQuery .= "is_checked_out = " . ($this->bIsCheckedOut ? "1" : "0") . ", " .
                                             "checked_out_user_id = " . $this->iCheckedOutUserID . " " .            								  			  
                                  			 "WHERE id = $this->iId";
            $result = $sql->query($sQuery);
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
            $sql = $default->db;
            // TODO: insert into sys_deleted
            //$result = $sql->query("INSERT INTO  " . $default->owl_sys_deleted_table . " () VALUES ()");
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
	* Begin the current document's collaboration process
	*
	*/	
	function beginCollaborationProcess() {
		global $default;
		//get the steps in this document's collaboration process
		$sQuery = "SELECT FURL.id, GFAL.precedence " .
				"FROM $default->owl_folders_user_roles_table AS FURL INNER JOIN $default->owl_groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " .
				"WHERE document_id = " . $this->iId . " " .
				"ORDER BY GFAL.precedence ASC";
		$sql = $default->db;
		$sql->query($sQuery);
		if  ($sql->next_record()) {
			//get the first step in the collaboration process			
			$iMinimumSequenceNumber = $sql->f("precedence");
			$oFolderUserRole = FolderUserRole::get($sql->f("id"));
			$oFolderUserRole->setActive(true);
			$oFolderUserRole->update();
			$oFolderCollaboration = FolderCollaboration::get($oFolderUserRole->getGroupFolderApprovalID());
			//get the role the user must perform
			$oRole = Role::get($oFolderCollaboration->getRoleID());
			//get the user to email
			$oUser = User::get($oFolderUserRole->getUserID());
			
			$sBody = $oUser->getUserName() . ", your role of '" . $oRole->getName() . "' in the document, '" . $this->sName . "' collaboration process is now active. " .
                                        "Click " . generateLink("control.php?action=viewDocument", "fDocumentID=" . $this->iId, "here") . " to access " .
										"the document";
										
			
			$oEmail = & new Email($default->owl_email_from, $default->owl_email_fromname);
			$oEmail->send($oUser->getEmail(), "Document collaboration role active", $sBody, $default->owl_email_from, $default->owl_email_fromname);
			
			//check if there are any other parallel steps that have to be started
			while ($sql->next_record()) {
				if ($sql->f("precedence") == $iMinimumSequenceNumber) {
					$oFolderUserRole = FolderUserRole::get($sql->f("id"));
					$oFolderUserRole->setActive(true);
					$oFolderUserRole->update();
					$oFolderCollaboration = FolderCollaboration::get($oFolderUserRole->getGroupFolderApprovalID());
					//get the role the user must perform
					$oRole = Role::get($oFolderCollaboration->getRoleID());
					//get the user to email
					$oUser = User::get($oFolderUserRole->getUserID());
			
					$sBody = $oUser->getUserName() . ", your role of '" . $oRole->getName() . "' in the document, '" . $this->sName . "' collaboration process is now active. " .
											"Click <a href=\"https://" . $_SERVER["SERVER_NAME"] . "$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->iId . "\">here</a> to access " .
											"the document";
			
					$oEmail = & new Email($default->owl_email_from, $default->owl_email_fromname);
					$oEmail->send($oUser->getEmail(), "Document collaboration role active", $sBody, $default->owl_email_from, $default->owl_email_fromname);
				} else {
					return;
				}
			}
		}
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
        $sql = $default->db;
        // TODO: join on sys_deleted
        $sql->query("SELECT * FROM $default->owl_documents_table WHERE id = $iDocumentID");
        if ($sql->next_record()) {			
            $oDocument = & new Document(stripslashes($sql->f("name")), stripslashes($sql->f("filename")), $sql->f("size"), $sql->f("creator_id"), $sql->f("mime_id"), $sql->f("folder_id"), $sql->f("description"));
            $oDocument->setDocumentTypeID($sql->f("document_type_id"));
            $oDocument->setMajorVersionNumber($sql->f("major_version"));
            $oDocument->setMinorVersionNumber($sql->f("minor_version"));
            $oDocument->setIsCheckedOut($sql->f("is_checked_out"));			
			$oDocument->sParentFolderIDs = stripslashes($sql->f("parent_folder_ids"));
			$oDocument->sFullPath = stripslashes($sql->f("full_path"));
            $oDocument->iId = $iDocumentID;
            $oDocument->setCheckedOutUserID = $sql->f("checked_out_user_id");
            return $oDocument;
        }
        $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentID . " table = documents";
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
        $aDocumentArray;
        settype($aDocumentArray, "array");
        $sql = $default->db;
        // TODO: join on sys_deleted
        $result = $sql->query("SELECT * FROM " . $default->owl_documents_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDocument = & Document::get($sql->f("id"));
                $aDocumentArray[$iCount] = $oDocument;
                $iCount++;
            }
            return $aDocumentArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }

    /**
    * Static function.
    * Get all the document field's associated with a document type
    *
    * @param  Document type primary key
    * @param  Get only the mandatory fields
    *
    * @return array array of document field objects, false otherwise and $_SESSION["errorMessage"]
    */
    function getDocumentFieldsForDocumentType($iDocumentTypeID, $bMandatoryOnly = false) {
        $aDocumentFieldArray;
        settype($aDocumentFieldArray,"array");
        $sql = $default->db;
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
        $sql = $default->db;
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
     * Returns the url to the file type icon associated with this document
     *
     * @return string the url to the relevant file icon, false if there is none
     */
    function getMimeTypeIconUrl() {
        global $default;
        if ($this->iMimeTypeID) {
            // lookup the icon from the table
            $sIconPath = lookupField($default->owl_mime_table, "icon_path", "id", $this->iMimeTypeID);
            if (strlen($sIconPath) > 0) {
                return $default->graphicsUrl . "/" . $sIconPath;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Get the full path for a document
     *
     * @param int primary key of document to generate path for 
     * @return string full path of document
     */
    function getPath() {
        return Folder::getFolderPath($this->iFolderID) . "/" . $this->sFileName;
    }
    
    /**
    * Static function.
    * Check if a document already exists
    *
    * @param String  File name of document
    * @param int  Primary key of folder to which document is assigned
    *
    * @return boolean true if document exists, false otherwise and set $_SESSION["errorMessage"]
    */
    function documentExists($sFileName, $iFolderID) {
        global $default, $lang_err_doc_not_exist;
        $sql = $default->db;
        // TODO: join on sys_deleted
        $sql->query("SELECT * FROM $default->owl_documents_table WHERE name = '" . addslashes($sFileName) . "' AND folder_id = $iFolderID");
        if ($sql->next_record()) {
            return true;
        }
        $_SESSION["errorMessage"] = $lang_err_doc_not_exist . "name = " . $sName . " folder_id = " . $iFolderID;
        return false;
    }

    /**
    * Lookup the document name for the document
    *
    * @param int the ID of the document to lookup the document name for
    * @return string the name of the document on success, false otherwise and set $_SESSION["errorMessage"]
    */
    function getDocumentName($iDocumentID) {
        global $default, $lang_err_database, $lang_err_doc_not_exist;
        $sql = $default->db;

        if ($sql->query("SELECT name FROM " . $default->owl_documents_table . " WHERE id = $iDocumentID")) {
            if ($sql->next_record()) {
                return $sql->f("name");
            } else {
                $_SESSION["errorMessage"] = $lang_err_doc_not_exist;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
    * Static function.
    * Get the path for a document that will be displayed to the user
    * 
    * @param integer primary key of document to generate path for 
    * @return string full path to document
    */
    function getDocumentDisplayPath($iDocumentID) {
        global $default;
        $oDocument = & Document::get($iDocumentID);
        return Folder::getFolderDisplayPath($oDocument->getFolderID()) . " > " . Document::getDocumentName($iDocumentID);
    }
	
	/**
	* Check if any documents are assigned the given
	* document type in a specified folder
	* 
	*/
	function documentIsAssignedDocTypeInFolder($iFolderID, $iFolderDocTypeID) {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT * " . 
					"FROM $default->owl_folder_doctypes_table AS FDL INNER JOIN $default->owl_folders_table AS F ON FDL.folder_id = F.id " .
					"INNER JOIN $default->owl_documents_table AS D ON D.folder_id = F.ID  " .
					"WHERE FDL.id = $iFolderDocTypeID " .
					"AND D.folder_id = $iFolderID");
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
}

?>

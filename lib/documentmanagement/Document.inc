<?php
// document statuses
DEFINE("LIVE", 1);
DEFINE("PUBLISHED", 2);
DEFINE("DELETED", 3);
DEFINE("ARCHIVED", 4);
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
/**
 * $Id$
 *
 * Represents a document as per the documents database table.
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
    //?? make checked out a status?
    /** document status **/
    var $iStatusID;
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
        $this->iDocumentTypeID = Folder::getDefaultFolderDocumentType($this->iFolderID);
        $this->iMajorVersion = 0;
        $this->iMinorVersion = 1;
        $this->bIsCheckedOut = false;
        $this->iCheckedOutUserID = -1;
        // FIXME: statuses
        $this->iStatusID = LIVE;
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
	
	/** returns the complete version number as a string */
	function getVersion() {
		return $this->iMajorVersion . "." . $this->iMinorVersion;
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

    /** Get the status id */
    function getStatusID() {
        return $this->iStatusID;
    }

    /** set the status id */
    function setStatusID($iNewValue) {
        $this->iStatusID = $iNewValue;
    }
    
    /**
     * Returns the live status of the document
     */
    function isLive() {
    	return $this->getStatusID() == LIVE;
    }
    
    /**
     * Get status ID wrapper for archived status
     */
    function isArchived() {
    	return $this->getStatusID() == ARCHIVED;
    }
    
    /** returns the number of days since this document has been last modified **/
    function getDaysSinceLastModified() {
        // convert to epoch
        $iLastModified = strtotime($this->dModified);
        $iCurrentDateTime = time();
        // calculate the difference
        $iDiff = $iCurrentDateTime - $iLastModified;
        // how many days is that?
        $iMinutes = $iDiff / 60;
        $iHours = $iMinutes / 60;
        $iDays = $iHours / 24;
        return floor($iDays);
    }
    
	/**
	 * Recursive function to generate a comma delimited string containing
	 * the parent folder ids
	 * 
	 * @return String	comma delimited string containing the parent folder ids
	 */
	function generateParentFolderIDS($iFolderID) {
		global $default;
		//if the folder is not the root folder 
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query("SELECT parent_id FROM $default->folders_table WHERE ID = " . quote($iFolderID));
			$sql->next_record();
			return $this->generateParentFolderIDS($sql->f("parent_id")) . ",$iFolderID";
		}
		return;
		
	}
	
	/**
	 * Returns a comma delimited string containing the parent folder ids, strips leading /
	 * 
	 * @return String	comma delimited string containing the parent folder ids
	 */	
	function generateFolderIDs($iFolderID) {
		$sFolderIDs = $this->generateParentFolderIDS($iFolderID);
		return substr($sFolderIDs, 1, strlen($sFolderIDs));
	}
	

	/**
	 * Recursively generates forward slash deliminated string giving full path of document
	 * from file system root url
	 */
	function generateFullFolderPath($iFolderID) {
		global $default;
		//if the folder is not the root folder 
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query("SELECT name, parent_id FROM $default->folders_table WHERE ID = " . quote($iFolderID));
			$sql->next_record();			
			return $this->generateFullFolderPath($sql->f("parent_id")) . "/" . $sql->f("name");
		}
		return;
	}

	/**
	 * Returns a forward slash deliminated string giving full path of document, strips leading /
	 */
	function generateFolderPath($iFolderID) {
		global $default;
		$sPath = $this->generateFullFolderPath($iFolderID);
		$sPath = substr($sPath, 1, strlen($sPath));
		$sPath = addslashes($sPath);
		return $sPath;			
	}	


    /**
    * Insert the current document into the database
    *
    * @return boolean true on successful insert, false otherwise
    */
    function create() {
        global $default, $lang_err_doc_exist, $lang_err_database;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
			$this->sFullPath = $this->generateFolderPath($this->iFolderID); 
			$this->sParentFolderIDs = $this->generateFolderIDs($this->iFolderID);
            $result = $sql->query("INSERT INTO " . $default->documents_table . " (document_type_id, name, filename, size, creator_id, modified, description, mime_id, folder_id, major_version, minor_version, is_checked_out, checked_out_user_id, parent_folder_ids, full_path, status_id) " .
                                  "VALUES (" . quote($this->iDocumentTypeID) . ", " . 
                                               quote($this->sName) . ", " . 
                                               quote($this->sFileName) . ", " . 
                                               quote($this->iSize) . ", " . 
                                               quote($this->iCreatorID) . ", " . 
                                               quote(getCurrentDateTime()) . ", " . 
                                               quote($this->sDescription) . ", " . 
                                               quote($this->iMimeTypeID) . ", " . 
                                               quote($this->iFolderID) . ", " . 
                                               quote($this->iMajorVersion) . ", " . 
                                               quote($this->iMinorVersion) . ", " . 
                                               quote($this->bIsCheckedOut) . ", " . 
                                               quote($this->iCheckedOutUserID) . ", " . 
                                               quote($this->sParentFolderIDs) . ", " . 
                                               quote($this->sFullPath) . ", " . 
                                               quote($this->iStatusID) . ")");
            if ($result) {
                //set the current documents primary key
                $this->iId = $sql->insert_id();
                // also insert into search permissions table to enable immediate metadata searching (#2681)
				$this->insertDocumentPermissions();
                return true;
            }
            return false;
        }
        return false;

    }
    
    function insertDocumentPermissions() {
    	global $default;
    	
	    $sql = $default->db;

	    // group permissions                
	    $sGroupPerms = "INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				       "SELECT UGL.user_id AS user_id, D.id AS document_id " .
				       "FROM $default->documents_table AS D INNER JOIN folders AS F ON D.folder_id = F.id " .
				       "INNER JOIN $default->groups_folders_table AS GFL ON GFL.folder_id = F.id " .
				       "INNER JOIN $default->users_groups_table AS UGL ON UGL.group_id = GFL.group_id " .
				       "WHERE D.id=" . quote($this->iId);
		$default->log->debug("addDocument groupPerms=$sGroupPerms");
		if ($sql->query($sGroupPerms)) {
			$default->log->debug("groupPerms succeeded");
		} else {
			$default->log->error("groupPerms failed");
		}
		// role permissions
	    $sRolePerms = "INSERT INTO $default->search_permissions_table (user_id, document_id) " .
				      "SELECT user_id, document_id " .
				      "FROM $default->folders_user_roles_table " .
				      "WHERE document_id=" . quote($this->iId);
		$default->log->info("addDocument rolePerms=$sRolePerms");
		if ($sql->query($sRolePerms)) {
			$default->log->debug("rolePerms succeeded");
		} else {
			$default->log->error("rolePerms failed");
		}
										            
		// public folders
		$sPublicFolderPerms = "INSERT INTO $default->search_permissions_table (user_id, document_id) " . 
                			  "SELECT U.id, D.id " . 
                			  "FROM $default->users_table AS U, $default->documents_table AS D INNER JOIN $default->folders_table AS F ON D.folder_id = F.id " .
							  "WHERE F.is_public = 1 " .
							  "AND D.id=" . quote($this->iId);
		$default->log->debug("addDocument publicFolder=$sPublicFolderPerms");
		if ($sql->query($sPublicFolderPerms)) {
			$default->log->debug("publicFolder succeeded");
		} else {
			$default->log->error("publicFolder failed");
		}
							
		// creator permissions
		$sCreatorPerms = "INSERT INTO $default->search_permissions_table (user_id, document_id) " .
						 "SELECT creator_id, id " .
						 "FROM $default->documents_table " .
						 "WHERE id=" . quote($this->iId);
		$default->log->debug("addDocument creatorPerms=$sCreatorPerms");
		if ($sql->query($sCreatorPerms)) {
			$default->log->debug("creatorPerms succeeded");
		} else {
			$default->log->error("creatorPerms failed");
		}		
    }

    /**
    * Update the documents current values in the database
    *
    * @return boolean true on successful update, false otherwise
    */
    function update($aForMove = false) {
        global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE " . $default->documents_table . " SET " .
                      "document_type_id = " . quote($this->iDocumentTypeID) . ", " .
                      "name = " . quote($this->sName) . ", " .
                      "filename = " . quote($this->sFileName) . ", " .
                      "size = " . quote($this->iSize) . ", " .
                      "creator_id = " . quote($this->iCreatorID) . ", " .
                      "modified = " . quote(getCurrentDateTime()) . ", " .
                      "description = " . quote($this->sDescription) . ", " .
                      "mime_id = " . quote($this->iMimeTypeID) . ", " .
                      "folder_id = " . quote($this->iFolderID) . ", " .
                      "major_version = " . quote($this->iMajorVersion) . ", " .
                      "minor_version = " . quote($this->iMinorVersion) . ", ";
			if ($aForMove) {
				//only update these if the document is being moved
				$this->sFullPath = $this->generateFolderPath($this->iFolderID); 
				$this->sParentFolderIDs = $this->generateFolderIDs($this->iFolderID);
	
				$sQuery .= "parent_folder_ids = " . quote($this->sParentFolderIDs) . ", " .
						   "full_path = " . quote($this->sFullPath) . ", ";
			}					
            $sQuery .= "is_checked_out = " . quote($this->bIsCheckedOut) . ", " .
                       "checked_out_user_id = " . quote($this->iCheckedOutUserID) . ", " .
                       "status_id = " . quote($this->iStatusID) . " " .
                       "WHERE id = " . quote($this->iId);
            $result = $sql->query($sQuery);
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
    * Delete the current document from the database.  Set the primary key to -1 
    * on successful deletion
    *
    * @return boolean true and reset id to -1 on successful deletion, false otherwise
    */
    function delete() {
        global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM  " . $default->documents_table . " " . 
                                  "WHERE id = " . quote($this->iId));
            if ($result) {
                $this->iId = -1;
                // clean up for this deleted document
                return true;
            }
            return false;
        }
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
				  "FROM $default->folders_user_roles_table AS FURL " .
                  "INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " .
				  "WHERE document_id = " . quote($this->iId) . " " .
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
			// FIXME: delegate this to message templating handling messaging layer
            // construct and send the mail
			$sBody = $oUser->getUserName() . ", your role of '" . $oRole->getName() . "' in the document, '" . $this->sName . "' collaboration process is now active. " .
                                        "Click " . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $this->iId, "here") . " to access " .
										"the document";
			$oEmail = & new Email();
			$oEmail->send($oUser->getEmail(), "Document collaboration role active", $sBody);
			DocumentCollaboration::createDependantDocuments($oFolderUserRole);
			
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
                    // FIXME: delegate this to message templating handling messaging layer
                    // construct and send the mail			
					$sBody = $oUser->getUserName() . ", your role of '" . $oRole->getName() . "' in the document, '" . $this->sName . "' collaboration process is now active. " .
                                            "Click " . generateLink("/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", "fDocumentID=" . $this->iId, "here") . " to access " .
											"the document";
					$oEmail = & new Email();
					$oEmail->send($oUser->getEmail(), "Document collaboration role active", $sBody);
					DocumentCollaboration::createDependantDocuments($oFolderUserRole);
				} else {
					return;
				}
			}			
		}
	}

	function endCollaborationProcess() {
		global $default;
		$sql = $default->db;
		//get the current step
		//if the user is assinged to two or more roles, make sure we get the current
		//one by ordering by precedence
		$sql->query("SELECT FURL.id AS id, GFAT.precedence " .
					"FROM $default->groups_folders_approval_table AS GFAT " . 
                    "INNER JOIN $default->folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " .
					"WHERE document_id = $this->iId AND FURL.user_id = " . quote($_SESSION["userID"]) . "  " . 
					"AND done = 0 " . 
					"ORDER BY precedence ASC");
		if ($sql->next_record()) {
			//set it as done
			$oFolderUserRole = FolderUserRole::get($sql->f("id"));
			$oFolderUserRole->setActive(false);
			$oFolderUserRole->setDone(true);
			$oFolderUserRole->setDateTime(getCurrentDateTime());
			return $oFolderUserRole->update();
		}
		return false;
	}
    
    /**
    *
    * Static function.  Given a document primary key will create
    * a document object and populate it with the corresponding
    * database values
    *
    * @return Document populated Document object on success, false otherwise.
    */
    function & get($iDocumentID) {
        global $default, $lang_err_doc_not_exist;
        if (strlen($iDocumentID) > 0) {
	        $sql = $default->db;
	        $sql->query("SELECT * FROM $default->documents_table WHERE id = " . quote($iDocumentID));
	        if ($sql->next_record()) {			
	            $oDocument = & new Document($sql->f("name"), $sql->f("filename"), $sql->f("size"), $sql->f("creator_id"), $sql->f("mime_id"), $sql->f("folder_id"), $sql->f("description"));
	            $oDocument->setDocumentTypeID($sql->f("document_type_id"));
	            $oDocument->setMajorVersionNumber($sql->f("major_version"));
	            $oDocument->setMinorVersionNumber($sql->f("minor_version"));
	            $oDocument->setIsCheckedOut($sql->f("is_checked_out"));
	            $oDocument->setLastModifiedDate($sql->f("modified"));			
				$oDocument->sParentFolderIDs = $sql->f("parent_folder_ids");
				$oDocument->sFullPath = $sql->f("full_path");
	            $oDocument->setCheckedOutUserID($sql->f("checked_out_user_id"));
	            // FIXME: nasty hack- paying the penalty for adding status_id late in phase 2
	            $oDocument->setStatusID( ($sql->f("status_id") == "" ? LIVE : $sql->f("status_id")) );
	            $oDocument->iId = $iDocumentID;
	            return $oDocument;
	        }
	        return false;
        } else {
        	return false;
        }
    }

    /**
    * Static function
    * Get a list of Documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Documents objects, false otherwise.
    */
    function getList($sWhereClause = null) {
        global $default, $lang_err_database;
        $aDocumentArray;
        settype($aDocumentArray, "array");
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->documents_table  . 
                              (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDocument = & Document::get($sql->f("id"));
                $aDocumentArray[$iCount] = $oDocument;
                $iCount++;
            }
            return $aDocumentArray;
        }
        return false;
    }

    /**
    * Static function.
    * Get all the document field's associated with a document type
    *
    * @param  Document type primary key
    * @param  Get only the mandatory fields
    *
    * @return array array of document field objects, false otherwise
    */
    function getDocumentFieldsForDocumentType($iDocumentTypeID, $bMandatoryOnly = false) {
        $aDocumentFieldArray;
        settype($aDocumentFieldArray,"array");
        $sql = $default->db;
        $result = $sql->query("SELECT DF.id AS id, DF.name AS name, DF.data_type AS data_type " .
                              "FROM $default->document_fields_table AS DF " .
                              "INNER JOIN $default->document_type_fields_table AS DTFL ON DF.id = DTFL.field_id " . 
                              "WHERE DTFL.document_type_id = " . quote($iDocumentTypeID) . ($bMandatoryOnly ? "AND DFTL.is_mandatory = 1 " : " ") . 
                              "ORDER BY DF.name ASC");
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
        $aDocumentHistory = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM  " . $default->document_transactions_table . " " .
                              "WHERE document_id = " . quote($this->iId) . " " .
                              "ORDER BY datetime DESC");
        if ($result) {
            $iCount = 0;
            while($sql->next_record()) {
                $oDocumentTransaction = DocumentTransaction::get($sql->f("id"));
                $aDocumentHistory[$iCount] = $oDocumentTransaction;
                $iCount++;
            }
            return $history;
        }
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
            $sIconPath = lookupField($default->mimetypes_table, "icon_path", "id", $this->iMimeTypeID);
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
     * Returns the html to display the document icon image
     */
    function getIcon() {
    	global $default;
    	return generateImage($this->getMimeTypeIconUrl() ? $this->getMimeTypeIconUrl() : "$default->graphicsUrl/unknown.gif");
    } 
    
    /**
     * Get the full path for a document
     *
     * @return string full path of document
     */
    function getPath() {
        return Folder::getFolderPath($this->iFolderID) . $this->sFileName;
    }
    
    /**
    * Get the path for a document that will be displayed to the user
    * 
    * @return string full path to document
    */
    function getDisplayPath($bDisplayIcon = false) {
    	$sFolderPath = Folder::getFolderDisplayPath($this->iFolderID);
    	// #3425 for consistency
        return ($bDisplayIcon ? $this->getIcon() : "") . 
        	   ($sFolderPath == "" ? "Deleted Folder" : $sFolderPath) . " > " . $this->sFileName;        	   
    }
    
    /**
    * Static function.
    * Check if a document already exists
    *
    * @param String  File name of document
    * @param int  Primary key of folder to which document is assigned
    *
    * @return boolean true if document exists, false otherwise.
    */
    function documentExists($sFileName, $iFolderID) {
        global $default;
        $sql = $default->db;
        $sQuery = "SELECT * FROM $default->documents_table " .
        		  "WHERE filename = " . quote($sFileName)  . 
        		  " AND folder_id = " . quote($iFolderID)  .
        		  " AND status_id = " . LIVE;
        $sql->query($sQuery);
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }

    /**
    * Lookup the document name for the document
    *
    * @param int the ID of the document to lookup the document name for
    * @return string the name of the document on success, false otherwise.
    */
    function getDocumentName($iDocumentID) {
        global $default, $lang_err_database, $lang_err_doc_not_exist;
        $sql = $default->db;

        if ($sql->query("SELECT name FROM $default->documents_table " .
                        "WHERE id = " . quote($iDocumentID))) {
            if ($sql->next_record()) {
                return $sql->f("name");
            } 
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
        return $oDocument->getDisplayPath();
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
					"FROM $default->folder_doctypes_table AS FDL " .
					"INNER JOIN $default->documents_table AS D ON D.document_type_id = FDL.document_type_id " .
					"WHERE FDL.id = " . quote($iFolderDocTypeID) . " " .
					"AND D.folder_id = " . quote($iFolderID));
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
	
	/**
	* On a document type change, delete all document field entries for the
	* old document type
	*/
	function removeInvalidDocumentTypeEntries() {
		global $default;
		$sQuery = "SELECT field_id FROM $default->document_type_fields_table DTFL " .
                  "INNER JOIN $default->document_fields_table AS DF ON DF.id = DTFL.field_id " .
                  "WHERE DTFL.document_type_id = " . quote($this->iDocumentTypeID) . " " .
                  "AND DF.is_generic = 0";
		$sql = $default->db;
		$sql->query($sQuery);
		$aFieldIDs = array();
		//get all the fields from the old document type
		while ($sql->next_record()) {
			$aFieldIDs[count($aFieldIDs)] = $sql->f("field_id");
		}
		if (count($aFieldIDs) > 0) {
			//delete the entries
			$sQuery = "DELETE FROM $default->document_fields_link_table " . 
                      "WHERE document_id = " . quote($this->iId) . "
                       AND document_field_id IN (" . implode(",",$aFieldIDs) . ")";
			if ($sql->query($sQuery)) {
				return true;
			}
			return false;
		}
		//none to remove if we get here
		return true;
		
		
	}
	
    /**
     * Checks if there is collaboration for this document
     *
     * @param integer the id of the document
     */
    function hasCollaboration() {
		global $default;
		$sql = $default->db;
		$sql->query("SELECT id AS count from $default->groups_folders_approval_table WHERE folder_id = $this->iFolderID");
		if ($sql->next_record()) {
			return true;
		}
		return false;
    }    
		
	/**
	 * Deletes content from document data tables
	 */
	function cleanupDocumentData($iDocumentID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("DELETE FROM $default->document_text_table WHERE document_id = $iDocumentID") &&
				  $sql->query("DELETE FROM $default->search_permissions_table WHERE document_id = $iDocumentID") &&
				  $sql->query("DELETE FROM $default->document_fields_link_table WHERE document_id = $iDocumentID");
		return $result;
	}		
}
?>
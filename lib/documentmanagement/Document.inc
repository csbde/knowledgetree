<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Represents a document as per the documents database table.
 *
 * Copyright (c) 2003, 2005 Jam Warehouse http://www.jamwarehouse.com
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

// document statuses
DEFINE("LIVE", 1);
DEFINE("PUBLISHED", 2);
DEFINE("DELETED", 3);
DEFINE("ARCHIVED", 4);
DEFINE("STATUS_INCOMPLETE", 5);

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

class Document extends KTEntity {
    // {{{ attributes
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
    /** date the document was created */
    var $dCreated;
    // }}}

    // {{{ constructor
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
    // }}}

    // {{{ getters/setters
    function getID() { return $this->iId; }
    function getDocumentTypeID() { return $this->iDocumentTypeID; }
    function setDocumentTypeID($sNewValue) { $this->iDocumentTypeID = $sNewValue; }
    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function getFileName() { return $this->sFileName; }
    function setFileName($sNewValue) { $this->sFileName = $sNewValue; }
    function getFolderID() { return $this->iFolderID; }
    function setFolderID($iNewValue) { $this->iFolderID = $iNewValue; }
    function getFileSize() { return $this->iSize; }
    function setFileSize($iNewValue) { $this->iSize = $iNewValue; }
    function getCreatorID() { return $this->iCreatorID; }
    function setCreatorID($iNewValue) { $this->iCreatorID = $iNewValue; }
    function getLastModifiedDate() { return $this->dModified; }
    function setLastModifiedDate($dNewValue) { $this->dModified = $dNewValue; }
    function getCreatedDateTime() { return $this->dCreated; }
    function getDescription() { return $this->sDescription; }
    function setDescription($sNewValue) { $this->sDescription = $sNewValue; }
    function getMimeTypeID() { return $this->iMimeTypeID; }
    function setMimeTypeID($iNewValue) { $this->iMimeTypeID = $iNewValue; }
    function getMajorVersionNumber() { return $this->iMajorVersion; }
    function setMajorVersionNumber($iNewValue) { $this->iMajorVersion = $iNewValue; }
    function getMinorVersionNumber() { return $this->iMinorVersion; }
    function setMinorVersionNumber($iNewValue) { $this->iMinorVersion = $iNewValue; }
    function getIsCheckedOut() { return $this->bIsCheckedOut; }
    function setIsCheckedOut($bNewValue) { $this->bIsCheckedOut = KTUtil::anyToBool($bNewValue); }
    function getCheckedOutUserID() { return $this->iCheckedOutUserID; }
    function setCheckedOutUserID($iNewValue) { $this->iCheckedOutUserID = $iNewValue; }
    function getStatusID() { return $this->iStatusID; }
    function setStatusID($iNewValue) { $this->iStatusID = $iNewValue; }
    // }}}

    // {{{ getVersion
    /** returns the complete version number as a string */
    function getVersion() {
        return $this->iMajorVersion . "." . $this->iMinorVersion;
    }
    // }}}

    // {{{ isLive
    /** Returns the live status of the document */
    function isLive() {
        return $this->getStatusID() == LIVE;
    }
    // }}}

    // {{{ isArchived
    /** Get status ID wrapper for archived status */
    function isArchived() {
        return $this->getStatusID() == ARCHIVED;
    }
    // }}}

    // {{{ getDaysSinceLastModified
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
    // }}}

    // {{{ ktentity requirements
    function _fieldValues () {
        $this->sFullPath = $this->_generateFolderPath($this->iFolderID);
        $this->sParentFolderIDs = $this->_generateFolderIDs($this->iFolderID);
        return array(
            'document_type_id' => $this->iDocumentTypeID,
            'name' => $this->sName,
            'filename' => $this->sFileName,
            'size' => $this->iSize,
            'creator_id' => $this->iCreatorID,
            'modified' => getCurrentDateTime(),
            'description' => $this->sDescription,
            'mime_id' => $this->iMimeTypeID,
            'folder_id' => $this->iFolderID,
            'major_version' => $this->iMajorVersion,
            'minor_version' => $this->iMinorVersion,
            'is_checked_out' => KTUtil::anyToBool($this->bIsCheckedOut),
            'checked_out_user_id' => $this->iCheckedOutUserID,
            'parent_folder_ids' => $this->sParentFolderIDs,
            'full_path' => $this->sFullPath,
            'status_id' => $this->iStatusID,
            'created' => $this->dCreated,
        );
    }

    function _table () {
        global $default;
        return $default->documents_table;
    }

    /**
     * Recursive function to generate a comma delimited string containing
     * the parent folder ids
     *
     * @return String	comma delimited string containing the parent folder ids
     */
    function _generateParentFolderIDS($iFolderID) {
        global $default;
        //if the folder is not the root folder
        if ($iFolderID != 0) {
            $sql = $default->db;
            $sql->query(array("SELECT parent_id FROM $default->folders_table WHERE ID = ?", $iFolderID));/*ok*/
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
    function _generateFolderIDs($iFolderID) {
        $sFolderIDs = $this->generateParentFolderIDS($iFolderID);
        return substr($sFolderIDs, 1, strlen($sFolderIDs));
    }

    /**
     * Recursively generates forward slash deliminated string giving full path of document
     * from file system root url
     */
    function _generateFullFolderPath($iFolderID) {
        global $default;
        //if the folder is not the root folder
        if ($iFolderID != 0) {
            $sql = $default->db;
            $sql->query(array("SELECT name, parent_id FROM $default->folders_table WHERE ID = ?", $iFolderID));/*ok*/
            $sql->next_record();
            return $this->_generateFullFolderPath($sql->f("parent_id")) . "/" . $sql->f("name");
        }
        return;
    }

    /**
     * Returns a forward slash deliminated string giving full path of document, strips leading /
     */
    function _generateFolderPath($iFolderID) {
        global $default;
        $sPath = $this->_generateFullFolderPath($iFolderID);
        $sPath = substr($sPath, 1, strlen($sPath));
        $sPath = addslashes($sPath);
        return $sPath;
    }
    // }}}

    // {{{ create
    /**
     * Insert the current document into the database
     *
     * @return boolean true on successful insert, false otherwise
     */
    function create() {
        $this->dCreated = getCurrentDateTime();
        $res = parent::create();

        if ($res === true) {
            Permission::updateSearchPermissionsForDocument($this->getID());
        }

        return $res;
    }
    // }}}

    // {{{ update
    function update($bPathMove = false) {
        $res = parent::update();
        if (($res === true) && ($bPathMove === true)) {
            Permission::updateSearchPermissionsForDocument($this->getID());
        }
        return $res;
    }
    // }}}

    // {{{ collaboration/approval

    // {{{ beginCollaborationProcess
    /**
     * Begin the current document's collaboration process
     *
     */
    function beginCollaborationProcess() {
        global $default;
        //get the steps in this document's collaboration process
        $sQuery = array("SELECT FURL.id, GFAL.precedence " ./*ok*/
                  "FROM $default->folders_user_roles_table AS FURL " .
                  "INNER JOIN $default->groups_folders_approval_table AS GFAL ON FURL.group_folder_approval_id = GFAL.id " .
                  "WHERE document_id = ? " .
                  "ORDER BY GFAL.precedence ASC", $this->iId);
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
    // }}}

    // {{{ endCollaborationProcess
    function endCollaborationProcess() {
        global $default;
        $sql = $default->db;
        //get the current step
        //if the user is assinged to two or more roles, make sure we get the current
        //one by ordering by precedence
        $sql->query(array("SELECT FURL.id AS id, GFAT.precedence " ./*ok*/
            "FROM $default->groups_folders_approval_table AS GFAT " .
            "INNER JOIN $default->folders_user_roles_table AS FURL ON GFAT.id = FURL.group_folder_approval_id " .
            "WHERE document_id = ? AND FURL.user_id = ? " .
            "AND done = 0 " .
            "ORDER BY precedence ASC", array($this->iId, $_SESSION["userID"])));
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
    // }}}

    // {{{ hasCollaboration
    /**
     * Checks if there is collaboration for this document
     *
     * @param integer the id of the document
     */
    function hasCollaboration() {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT id AS count from $default->groups_folders_approval_table WHERE folder_id = ?", $this->iFolderID));/*ok*/
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }
    // }}}

    // }}}

    // {{{ get
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
            $sql->query(array("SELECT * FROM $default->documents_table WHERE id = ?", $iDocumentID));/*ok*/
            if ($sql->next_record()) {
                $oDocument = & new Document($sql->f("name"), $sql->f("filename"), $sql->f("size"), $sql->f("creator_id"), $sql->f("mime_id"), $sql->f("folder_id"), $sql->f("description"));
                $oDocument->setDocumentTypeID($sql->f("document_type_id"));
                $oDocument->setMajorVersionNumber($sql->f("major_version"));
                $oDocument->setMinorVersionNumber($sql->f("minor_version"));
                $oDocument->setIsCheckedOut($sql->f("is_checked_out"));
                $oDocument->setLastModifiedDate($sql->f("modified"));
                $oDocument->dCreated = $sql->f("created");
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
    // }}}

    // {{{ getList
    /**
     * Static function
     * Get a list of Documents
     *
     * @param  String  Where clause (not required)
     *
     * @return Array array of Documents objects, false otherwise.
     */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(Document::_table(), 'Document', $sWhereClause);
    }
    // }}}

    // {{{ getDocumentFieldsForDocumentType
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
        $sQuery = "SELECT DF.id AS id, DF.name AS name, DF.data_type AS data_type " ./*ok*/
            "FROM $default->document_fields_table AS DF " .
            "INNER JOIN $default->document_type_fields_table AS DTFL ON DF.id = DTFL.field_id " .
             "WHERE DTFL.document_type_id = ? ";
        $aParams = array($iDocumentTypeID);
        if ($bMandatoryOnly) {
            $sQuery .= "AND DFTL.is_mandatory = ? ";
            $aParams[] = true;
        }
        $sQuery .= "ORDER BY DF.name ASC";
        $result = $sql->query(array($sQuery, $aParams));

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
    // }}}

    // {{{ getDocumentHistory
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
        $result = $sql->query(array("SELECT * FROM  " . $default->document_transactions_table . " " ./*ok*/
                              "WHERE document_id = ? " .
                              "ORDER BY datetime DESC", $this->iId));
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
    // }}}

    // {{{ getMimeTypeIconUrl
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
    // }}}

    // {{{ getIcon
    /**
     * Returns the html to display the document icon image
     */
    function getIcon() {
        global $default;
        return generateImage($this->getMimeTypeIconUrl() ? $this->getMimeTypeIconUrl() : "$default->graphicsUrl/unknown.gif");
    }
    // }}}

    // {{{ getPath
    /**
     * Get the full path for a document
     *
     * @return string full path of document
     */
    function getPath() {
        return Folder::getFolderPath($this->iFolderID) . $this->sFileName;
    }
    // }}}

    // {{{
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
    // }}}

    // {{{ documentExists
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
        $sQuery = "SELECT * FROM $default->documents_table " ./*ok*/
              "WHERE filename = ? " .
              " AND folder_id = ?" .
              " AND status_id = ?";
        $aParams = array($sFileName, $iFolderID, LIVE);
        $sql->query(array($sQuery, $aParams));
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ getDocumentName
    /**
     * Lookup the document name for the document
     *
     * @param int the ID of the document to lookup the document name for
     * @return string the name of the document on success, false otherwise.
     */
    function getDocumentName($iDocumentID) {
        global $default, $lang_err_database, $lang_err_doc_not_exist;
        $sql = $default->db;

        if ($sql->query(array("SELECT name FROM $default->documents_table " ./*ok*/
                        "WHERE id = ?", $iDocumentID))) {
            if ($sql->next_record()) {
                return $sql->f("name");
            }
        }
        return false;
    }
    // }}}

    // {{{ getDocumentDisplayPath
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
    // }}}

    // {{{ documentIsAssignedDocTypeInFolder
    /**
     * Check if any documents are assigned the given
     * document type in a specified folder
     *
     */
    function documentIsAssignedDocTypeInFolder($iFolderID, $iFolderDocTypeID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * " . /*ok*/
                    "FROM $default->folder_doctypes_table AS FDL " .
                    "INNER JOIN $default->documents_table AS D ON D.document_type_id = FDL.document_type_id " .
                    "WHERE FDL.id = ? " .
                    "AND D.folder_id = ?", array($iFolderDocTypeID, $iFolderID)));
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ removeInvalidDocumentTypeEntries
    /**
     * On a document type change, delete all document field entries for the
     * old document type
     */
    function removeInvalidDocumentTypeEntries() {
        global $default;
        $sQuery = array("SELECT field_id FROM $default->document_type_fields_table DTFL " . /*ok*/
              "INNER JOIN $default->document_fields_table AS DF ON DF.id = DTFL.field_id " .
              "WHERE DTFL.document_type_id = ? " .
              "AND DF.is_generic = ?", array($this->iDocumentTypeID, false));
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
    // }}}

    // {{{ cleanupDocumentData
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
    // }}}
}
?>

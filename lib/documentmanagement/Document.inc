<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Represents a document as per the documents database table.
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement
 */

// document statuses
DEFINE("LIVE", 1);
DEFINE("PUBLISHED", 2);
DEFINE("DELETED", 3);
DEFINE("ARCHIVED", 4);
DEFINE("STATUS_INCOMPLETE", 5);

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentcore.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentmetadataversion.inc.php');

class Document {
    var $iId;
    
    var $_oDocumentCore = null;
    var $_oDocumentContentVersion = null;
    var $_oDocumentMetadataVersion = null;
    
    var $iCurrentMetadataVersionId = null;

    // {{{ getters/setters

    // locally stored info.
    
    function getId() { return $this->iId; }
    
    // Document Core
    
    function getFolderID() { return $this->_oDocumentCore->getFolderId(); }
    function setFolderID($iNewValue) { $this->_oDocumentCore->setFolderId($iNewValue); }
    
    function getCreatorID() { return $this->_oDocumentCore->getCreatorId(); }
    function setCreatorID($iNewValue) { $this->_oDocumentCore->setCreatorId($iNewValue); }
    
    function getLastModifiedDate() { return $this->_oDocumentCore->getLastModifiedDate(); }
    function setLastModifiedDate($dNewValue) { $this->_oDocumentCore->setLastModifiedDate($dNewValue); }
    
    function getCreatedDateTime() { return $this->_oDocumentCore->getCreatedDateTime(); }
    
    function getIsCheckedOut() { return $this->_oDocumentCore->getIsCheckedOut(); }
    function setIsCheckedOut($bNewValue) { $this->_oDocumentCore->setIsCheckedOut(KTUtil::anyToBool($bNewValue)); }
    
    function getCheckedOutUserID() { return $this->_oDocumentCore->getCheckedOutUserId(); }
    function setCheckedOutUserID($iNewValue) { $this->_oDocumentCore->setCheckedOutUserId($iNewValue); }
    
    function getPermissionObjectID() { return $this->_oDocumentCore->getPermissionObjectId(); }
    function setPermissionObjectID($iNewValue) { $this->_oDocumentCore->setPermissionObjectId($iNewValue); }
    
    function getPermissionLookupID() { return $this->_oDocumentCore->getPermissionLookupId(); }
    function setPermissionLookupID($iNewValue) { $this->_oDocumentCore->setPermissionLookupId($iNewValue); }
        
    function getModifiedUserId() { return $this->_oDocumentCore->getModifiedUserId(); }
    function setModifiedUserId($iNewValue) { $this->_oDocumentCore->setModifiedUserId($iNewValue); }
    
    // Document Metadata Items
    
    function getDocumentTypeID() { return $this->_oDocumentMetadataVersion->getDocumentTypeId(); }
    function setDocumentTypeID($sNewValue) { $this->_oDocumentMetadataVersion->setDocumentTypeId($sNewValue); }
    
    function getName() { return $this->_oDocumentMetadataVersion->getName(); }
    function setName($sNewValue) { $this->_oDocumentMetadataVersion->setName($sNewValue); }

    function getDescription() { return $this->_oDocumentMetadataVersion->getDescription(); }
    function setDescription($sNewValue) { $this->_oDocumentMetadataVersion->setDescription($sNewValue); }
    
    function getStatusID() { return $this->_oDocumentCore->getStatusId(); }
    function setStatusID($iNewValue) { $this->_oDocumentMetadataVersion->setStatusId($iNewValue); $this->_oDocumentCore->setStatusId($iNewValue); }
    
    function getMetadataVersion() { return $this->_oDocumentMetadataVersion->getMetadataVersion(); }
    function setMetadataVersion($iNewValue) { $this->_oDocumentMetadataVersion->getMetadataVersion($iNewValue); }

    function getMetadataVersionId() { return $this->_oDocumentCore->getMetadataVersionId(); }
    function setMetadataVersionId($iNewValue) { $this->_oDocumentCore->setMetadataVersionId($iNewValue); }

    function getContentVersionId() { return $this->_oDocumentMetadataVersion->getContentVersionId(); }
    function setContentVersionId($iNewValue) { $this->_oDocumentMetadataVersion->setContentVersionId($iNewValue); }
    
    function getVersionCreated() { return $this->_oDocumentMetadataVersion->getVersionCreated(); }
    function getVersionCreatorId() { return $this->_oDocumentMetadataVersion->getVersionCreatorId(); }

    // Document Content Version   
    
    function getFileName() { return $this->_oDocumentContentVersion->getFileName(); }
    function setFileName($sNewValue) { $this->_oDocumentContentVersion->setFileName($sNewValue); }
    
    function getFileSize() { return $this->_oDocumentContentVersion->getSize(); }
    function setFileSize($iNewValue) { $this->_oDocumentContentVersion->setSize($iNewValue); }
    
    function getSize() { return $this->_oDocumentContentVersion->getSize(); }
    function setSize($iNewValue) { $this->_oDocumentContentVersion->setSize($iNewValue); }
    
    function getMimeTypeID() { return $this->_oDocumentContentVersion->getMimeTypeId(); }
    function setMimeTypeID($iNewValue) { $this->_oDocumentContentVersion->setMimeTypeId($iNewValue); }
    
    function getMajorVersionNumber() { return $this->_oDocumentContentVersion->getMajorVersionNumber(); }
    function setMajorVersionNumber($iNewValue) { $this->_oDocumentContentVersion->setMajorVersionNumber($iNewValue); }
    
    function getMinorVersionNumber() { return $this->_oDocumentContentVersion->getMinorVersionNumber(); }
    function setMinorVersionNumber($iNewValue) { $this->_oDocumentContentVersion->setMinorVersionNumber($iNewValue); }

    function getStoragePath() { return $this->_oDocumentContentVersion->getStoragePath(); }
    function setStoragePath($sNewValue) { $this->_oDocumentContentVersion->setStoragePath($sNewValue); }
    
    // }}}

    // {{{ getParentID
    /**
     * Allows documents to be treated like folders in terms of finding
     * their parent objects.
     */
    function getParentID() {
        return $this->getFolderID();
    }
    // }}}

    // {{{ getVersion
    /** returns the complete version number as a string */
    function getVersion() {
        return $this->getMajorVersion() . "." . $this->getMinorVersion();
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


    // {{{ update
    function update($bPathMove = false) {
        $res = $this->_oDocumentCore->update($bPathMove);
        if (PEAR::isError($res)) { var_dump($res); return $res; }
        
        $res = $this->_oDocumentContentVersion->update($bPathMove);
        if (PEAR::isError($res)) { var_dump($res); return $res; }
        
        $res = $this->_oDocumentMetadataVersion->update($bPathMove);
        if (PEAR::isError($res)) { var_dump($res); return $res; }

        return $res;
    }
    // }}}

    // {{{ get
    function &get($iId, $iMetadataVersion=null) {
        $oDocument = new Document();
        if (!is_numeric($iId)) { print "<pre>Document.inc: 162: "; var_dump($iId); print "</pre>"; }
        $res = $oDocument->load($iId, $iMetadataVersion);
        if (PEAR::isError($res)) { 
            return $res;
        }
        return $oDocument;
    }
    // }}}

    function load($iId, $iMetadataVersionId = null) {
        $this->iId = $iId;
        $this->_oDocumentCore = KTDocumentCore::get($iId);
        if (PEAR::isError($this->_oDocumentCore)) { return $this->_oDocumentCore; }
        
        // FIXME add error $res if MDV > $_oDC->getMDV
        if (is_null($iMetadataVersionId)) { 
            $this->_oDocumentMetadataVersion = KTDocumentMetadataVersion::get($this->_oDocumentCore->getMetadataVersionId()); 
            $this->iCurrentMetadataVersionId = $this->_oDocumentCore->getMetadataVersionId();
        } else {
            $this->_oDocumentMetadataVersion = KTDocumentMetadataVersion::get($iMetadataVersionId);
            $this->iCurrentMetadataVersionId = $iMetadataVersionId;
        }
        if (PEAR::isError($this->_oDocumentMetadataVersion)) { var_dump($this->_oDocumentMetadataVersion); return $this->_oDocumentMetadataVersion; }
        
        $this->_oDocumentContentVersion = KTDocumentContentVersion::get($this->_oDocumentMetadataVersion->getContentVersionId());
        if (PEAR::isError($this->_oDocumentContentVersion)) { return $this->_oDocumentContentVersion; }
    }

    function getCurrentMetadataVersionId() {
        return $this->iCurrentMetadataVersionId;
    }

    // {{{ getList
    /**
     * Static function
     * Get a list of Documents - CAN ONLY USE KTDOCUMENTCORE QUERIES
     *
     * @param  String  Where clause (not required)
     *
     * @return Array array of Documents objects, false otherwise.
     */
    function getList($sWhereClause = null) {
        $aOptions = array('ids' => true);
        $aIds = KTDocumentCore::getList($sWhereClause, $aOptions);
        $aList = array();
        foreach($aIds as $iId) {
            $aList[] = Document::get($iId);
        };
        return $aList;

    }
    // }}}

    // {{{ getDisplayPath
    /**
     * Get the path for a document that will be displayed to the user
     *
     * @return string full path to document
     */
    function getDisplayPath($bDisplayIcon = false) {
        $sFolderPath = Folder::getFolderDisplayPath($this->getFolderID());
        // #3425 for consistency
        return ($bDisplayIcon ? $this->getIcon() : "") .
           ($sFolderPath == "" ? "Deleted Folder" : $sFolderPath) . " &raquo; " . $this->getName();
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
        return false;
    
    
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

    // {{{ getDocumentDisplayPath
    /**
     * Static function.
     * Get the path for a document that will be displayed to the user
     *
     * @param integer primary key of document to generate path for
     * @return string full path to document
     */
    function getDocumentDisplayPath($iDocumentID) {
        $oDocument = & Document::get($iDocumentID);
        return $oDocument->getDisplayPath();
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

    // {{{ getByFolderIDAndLookupID
    function &getByFolderIDAndLookupID($iParentID, $iLookupID, $aOptions = null) {
        $aIds = KTEntityUtil::getByDict('KTDocumentCore', array(
            'folder_id' => $iParentID,
            'permission_lookup_id' => $iLookupID,
            'status_id' => LIVE,
        ), array('multi' => true, 'ids' => true));
        
        $aList = array();
        foreach ($aIds as $iId) {
            $aList[] = Document::get($iId);
        }
    }
    // }}}

    // STATIC
    function &createFromArray($aOptions) {
        if (KTUtil::arrayGet($aOptions, "size") === null) {
            $aOptions['size'] = 0;
        }
        if (KTUtil::arrayGet($aOptions, "mimetypeid") === null) {
            $aOptions['mimetypeid'] = 0;
        }
        /*
        if (KTUtil::arrayGet($aOptions, "statusid") === null) {
            $aOptions['statusid'] = LIVE;
        }
        */
        $oDocument = new Document();
        $aOptions = array_change_key_case($aOptions);
        
        $aCoreKeys = array(
            "CreatorId",
            "Created",
            "ModifiedUserId",
            "Modified",
            "FolderId", 
            "StatusId",
        );
        
        $aCore = array();
        foreach ($aCoreKeys as $sKey) {
            $sKey = strtolower($sKey);
            $sValue = KTUtil::arrayGet($aOptions, $sKey);
            if (!is_null($sValue)) {
                $aCore[$sKey] = $sValue;
            }
        }
        
        $aMetadataVersionKeys = array(
            "MetadataVersion",
            "ContentVersionId",
            "DocumentTypeId",
            "Name",
            "Description",
            "StatusId",
            "VersionCreated",
            "VersionCreatorId",
        );
        
        $aMetadataVersion = array();
        foreach ($aMetadataVersionKeys as $sKey) {
            $sKey = strtolower($sKey);
            $sValue = KTUtil::arrayGet($aOptions, $sKey);
            if (!is_null($sValue)) {
                $aMetadataVersion[$sKey] = $sValue;
            }
        }
        $aMetadataVersion['VersionCreatorId'] = $aCore['creatorid'];
        
        $aContentKeys = array(
            "Filename",
            "Size",
            "MimeId",
            "MajorVersion",
            "MinorVersion",
            "StoragePath",
        );
        
        $aContentVersion = array();
        foreach ($aContentKeys as $sKey) {
            $sKey = strtolower($sKey);
            $sValue = KTUtil::arrayGet($aOptions, $sKey);
            if (!is_null($sValue)) {
                $aContentVersion[$sKey] = $sValue;
            }
        }        
        
        $oDocument->_oDocumentCore = KTDocumentCore::createFromArray($aCore);
        if (PEAR::isError($oDocument->_oDocumentCore)) {
            return $oDocument->_oDocumentCore;
        }
        $iId = $oDocument->_oDocumentCore->getId();
        $aContentVersion["documentid"] = $iId;
        $oDocument->_oDocumentContentVersion = KTDocumentContentVersion::createFromArray($aContentVersion);
        if (PEAR::isError($oDocument->_oDocumentContentVersion)) { return $oDocument->_oDocumentContentVersion; }
        $aMetadataVersion["documentid"] = $iId;
        $aMetadataVersion["contentversionid"] = $oDocument->_oDocumentContentVersion->getId();
        $oDocument->_oDocumentMetadataVersion = KTDocumentMetadataVersion::createFromArray($aMetadataVersion);
        if (PEAR::isError($oDocument->_oDocumentMetadataVersion)) { return $oDocument->_oDocumentMetadataVersion; }
        $oDocument->_oDocumentCore->setMetadataVersionId($oDocument->_oDocumentMetadataVersion->getId());
        $res = $oDocument->_oDocumentCore->update();
        if (PEAR::isError($res)) {
            return $res;
        }
        $oDocument->iId = $iId;
        return $oDocument;
    }

    function startNewMetadataVersion($oUser) {
        $iUserId = KTUtil::getId($oUser);
        $this->_oDocumentMetadataVersion->iId = -1;
        $this->_oDocumentMetadataVersion->iMetadataVersion++;
        $this->_oDocumentMetadataVersion->dVersionCreated = getCurrentDateTime();
        $this->_oDocumentMetadataVersion->iVersionCreatorId = $iUserId;
        $this->_oDocumentMetadataVersion->create();
        $this->_oDocumentCore->iMetadataVersion = $this->_oDocumentMetadataVersion->iMetadataVersion;
        $this->_oDocumentCore->iMetadataVersionId = $this->_oDocumentMetadataVersion->iId;
    }

    function startNewContentVersion($oUser) {
        $iUserId = KTUtil::getId($oUser);
        $this->_oDocumentContentVersion->iId = -1;
        $res = $this->_oDocumentContentVersion->create();
        if (PEAR::isError($res)) {
            var_dump($res);
            return $res;
        }
        $this->_oDocumentMetadataVersion->iId = -1;
        $this->_oDocumentMetadataVersion->iMetadataVersion++;
        $this->_oDocumentMetadataVersion->dVersionCreated = getCurrentDateTime();
        $this->_oDocumentMetadataVersion->iVersionCreatorId = $iUserId;
        $this->_oDocumentMetadataVersion->iContentVersionId = $this->_oDocumentContentVersion->getId();
        $res = $this->_oDocumentMetadataVersion->create();
        if (PEAR::isError($res)) {
            var_dump($res);
            return $res;
        }
        $this->_oDocumentCore->iMetadataVersion = $this->_oDocumentMetadataVersion->iMetadataVersion;
        $this->_oDocumentCore->iMetadataVersionId = $this->_oDocumentMetadataVersion->iId;
    }

    function delete() {
        $this->_oDocumentCore->iMetadataVersionId = null;
        $this->_oDocumentCore->update();
        $aMetadataVersions = KTDocumentMetadataVersion::getByDocument($this);
        foreach ($aMetadataVersions as $oVersion) {
            $oVersion->delete();
        }
        $aContentVersions = KTDocumentContentVersion::getByDocument($this);
        foreach ($aContentVersions as $oVersion) {
            $oVersion->delete();
        }
        $this->_oDocumentCore->delete();
        return true;
    }
}
?>

<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * -------------------------------------------------------------------------
 *
 * Represents a document as per the documents database table.
 *
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
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

    function getFullPath() { return $this->_oDocumentCore->getFullPath(); }
    
    function getCreatorID() { return $this->_oDocumentCore->getCreatorId(); }
    function setCreatorID($iNewValue) { $this->_oDocumentCore->setCreatorId($iNewValue); }
    
    function getOwnerID() { return $this->_oDocumentCore->getOwnerId(); }
    function setOwnerID($iNewValue) { $this->_oDocumentCore->setOwnerId($iNewValue); }
    
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

    function getImmutable() { return $this->_oDocumentCore->getImmutable(); }
    function setImmutable($mValue) { $this->_oDocumentCore->setImmutable($mValue); }
    
    function getRestoreFolderId() { return $this->_oDocumentCore->getRestoreFolderId(); }
    function setRestoreFolderId($iValue) { $this->_oDocumentCore->setRestoreFolderId($iValue); }    

    function getRestoreFolderPath() { return $this->_oDocumentCore->getRestoreFolderPath(); }
    function setRestoreFolderPath($sValue) { $this->_oDocumentCore->setRestoreFolderPath($sValue); }    
    
    
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

    function getWorkflowId() { return $this->_oDocumentMetadataVersion->getWorkflowId(); }
    function setWorkflowId($mValue) { $this->_oDocumentMetadataVersion->setWorkflowId($mValue); }
    function getWorkflowStateId() { return $this->_oDocumentMetadataVersion->getWorkflowStateId(); }
    function setWorkflowStateId($mValue) { $this->_oDocumentMetadataVersion->setWorkflowStateId($mValue); }

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
        return $this->getMajorVersionNumber() . "." . $this->getMinorVersionNumber();
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
        if (!is_numeric($iId)) {
            return PEAR::raiseError(_kt('Non-numeric identifier'));
        }
        $iId = (int)$iId;
        $oDocument = new Document();
        $res = $oDocument->load($iId, $iMetadataVersion);
        if (PEAR::isError($res)) { 
            return $res;
        }
        return $oDocument;
    }
    // }}}

    // {{{ load
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
    // }}}

    // {{{ getCurrentMetadataVersionId
    function getCurrentMetadataVersionId() {
        return $this->iCurrentMetadataVersionId;
    }
    // }}}

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

    // {{{ fileExists
    /**
     * Static function.
     * Check if a document with a given filename currently exists
     *
     * @param String  File name of document
     * @param int  Primary key of folder to which document is assigned
     *
     * @return boolean true if document exists, false otherwise.
     */
    function fileExists($sFileName, $iFolderID) {
        $sD = KTUtil::getTableName('documents');
        $sDM = KTUtil::getTableName('document_metadata_version');
        $sDC = KTUtil::getTableName('document_content_version');
        $sQuery = "SELECT D.id AS id FROM $sD AS D
            LEFT JOIN $sDM AS DM ON D.metadata_version_id = DM.id
            LEFT JOIN $sDC AS DC ON DM.content_version_id = DC.id
            WHERE DC.filename = ? AND D.folder_id = ? AND D.status_id=1";
        $aParams = array($sFileName, $iFolderID);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (empty($id)) {
            return false;
        }
        return true;
    }
    // }}}
    
    function &getByFilenameAndFolder($sFileName, $iFolderID) {
        $sD = KTUtil::getTableName('documents');
        $sDM = KTUtil::getTableName('document_metadata_version');
        $sDC = KTUtil::getTableName('document_content_version');
        $sQuery = "SELECT D.id AS id FROM $sD AS D
            LEFT JOIN $sDM AS DM ON D.metadata_version_id = DM.id
            LEFT JOIN $sDC AS DC ON DM.content_version_id = DC.id
            WHERE DC.filename = ? AND D.folder_id = ?";
        $aParams = array($sFileName, $iFolderID);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        return Document::get($id);   
    }

    // {{{ nameExists
    /**
     * Static function.
     * Check if a document with a given filename currently exists
     *
     * @param String  File name of document
     * @param int  Primary key of folder to which document is assigned
     *
     * @return boolean true if document exists, false otherwise.
     */
    function nameExists($sName, $iFolderID) {
        $sD = KTUtil::getTableName('documents');
        $sDM = KTUtil::getTableName('document_metadata_version');
        $sDC = KTUtil::getTableName('document_content_version');
        $sQuery = "SELECT D.id AS id FROM $sD AS D
            LEFT JOIN $sDM AS DM ON D.metadata_version_id = DM.id
            LEFT JOIN $sDC AS DC ON DM.content_version_id = DC.id
            WHERE DM.name = ? AND D.folder_id = ? AND D.status_id=1";
        $aParams = array($sName, $iFolderID);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (empty($id)) {
            return false;
        }
        return true;
    }
    // }}}

    function &getByNameAndFolder($sName, $iFolderID) {
        $sD = KTUtil::getTableName('documents');
        $sDM = KTUtil::getTableName('document_metadata_version');
        $sDC = KTUtil::getTableName('document_content_version');
        $sQuery = "SELECT D.id AS id FROM $sD AS D
            LEFT JOIN $sDM AS DM ON D.metadata_version_id = DM.id
            LEFT JOIN $sDC AS DC ON DM.content_version_id = DC.id
            WHERE DM.name = ? AND D.folder_id = ?";
        $aParams = array($sName, $iFolderID);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        return Document::get($id);   
    }

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
        // FIXME this appears to be deprecated, or at least should be
        $sTable = KTUtil::getTableName('document_text');
        $sQuery = "DELETE FROM $sTable WHERE document_id = ?";
        $aParams = array($iDocumentID);        
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        return $res;
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

    // {{{ getByState
    function &getByState($oState) {
        $iStateId = KTUtil::getId($oState);

        $sDocumentTable = KTUtil::getTableName('documents');
        $sDocumentMetadataTable = KTUtil::getTableName('document_metadata_version');

        $sQuery = sprintf("SELECT D.id AS document_id FROM %s AS D
                           LEFT JOIN %s AS DM ON D.metadata_version_id = DM.id
                           WHERE DM.workflow_state_id = ?", $sDocumentTable, $sDocumentMetadataTable);

        $aParams = array($iStateId);
        $aIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'document_id');
        
        $aList = array();
        foreach ($aIds as $iId) {
            $aList[] = Document::get($iId);
        }
        return $aList;
    }
    // }}}

    // {{{
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
            "RestoreFolderId",            
            "RestoreFolderPath",
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

        // Grab a copy that uses proxies...
        $oDocument =& Document::get($iId);

        KTPermissionUtil::updatePermissionLookup($oDocument);

        return $oDocument;
    }
    // }}}

    // {{{ startNewMetadataVersion
    function startNewMetadataVersion($oUser) {
        $iUserId = KTUtil::getId($oUser);
        $this->_oDocumentMetadataVersion =& $this->_oDocumentMetadataVersion->newCopy();
        $this->_oDocumentMetadataVersion->bumpMetadataVersion();
        $this->_oDocumentMetadataVersion->setVersionCreated(getCurrentDateTime());
        $this->_oDocumentMetadataVersion->setVersionCreatorId($iUserId);
        $this->_oDocumentMetadataVersion->update();
        $this->_oDocumentCore->setMetadataVersion($this->_oDocumentMetadataVersion->getMetadataVersion());
        $this->_oDocumentCore->setMetadataVersionId($this->_oDocumentMetadataVersion->iId);
    }
    // }}}

    // {{{ startNewContentVersion
    function startNewContentVersion($oUser) {
        if ($this->getImmutable()) {
            return PEAR::raiseError(_kt('Cannot create new version of document: Document is immutable'));
        }
        $iUserId = KTUtil::getId($oUser);
        $this->_oDocumentContentVersion =& $this->_oDocumentContentVersion->newCopy();
        $this->_oDocumentMetadataVersion =& $this->_oDocumentMetadataVersion->newCopy();
        $this->_oDocumentMetadataVersion->bumpMetadataVersion();
        $this->_oDocumentMetadataVersion->setVersionCreated(getCurrentDateTime());
        $this->_oDocumentMetadataVersion->setVersionCreatorId($iUserId);
        $this->_oDocumentMetadataVersion->setContentVersionId($this->_oDocumentContentVersion->getId());
        $res = $this->_oDocumentMetadataVersion->update();
        if (PEAR::isError($res)) {
            var_dump($res);
            return $res;
        }
        $this->_oDocumentCore->setMetadataVersion($this->_oDocumentMetadataVersion->getMetadataVersion());
        $this->_oDocumentCore->setMetadataVersionId($this->_oDocumentMetadataVersion->getId());
    }
    // }}}

    // {{{ delete
    function delete() {
        $this->_oDocumentCore->setMetadataVersionId(null);
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
    // }}}

    function clearAllCaches() {
        
        KTEntityUtil::clearAllCaches('KTDocumentCore');
        KTEntityUtil::clearAllCaches('KTDocumentContentVersion');
        KTEntityUtil::clearAllCaches('KTDocumentMetadataVersion');
        
        return KTEntityUtil::clearAllCaches('Document');
    }


    function getLastTransactionComment($sTransactionNamespace) {
        $sDocumentTransactionTable = KTUtil::getTableName('document_transactions');

        $sQuery = sprintf("SELECT comment FROM %s
                           WHERE transaction_namespace = ? AND document_id = ?
                           ORDER BY datetime DESC", 
			  $sDocumentTransactionTable, $sDocumentMetadataTable);

	$aParams = array($sTransactionNamespace, $this->getId());
	$sComment = DBUtil::getOneResultKey(array($sQuery, $aParams), 'comment');

	if(PEAR::isError($sComment)) {
	    return false;
	}

	return $sComment;
    }

    function getLastDeletionComment() {
	$sComment = $this->getLastTransactionComment('ktcore.transactions.delete');
	if(!$sComment) {
	    return $sComment;
	}

	$aComment = explode(':', $sComment);
	return trim($aComment[1]);
    }
    



	
}
?>

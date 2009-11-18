<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

// document statuses
DEFINE("LIVE", 1);
DEFINE("PUBLISHED", 2);
DEFINE("DELETED", 3);
DEFINE("ARCHIVED", 4);
DEFINE("STATUS_INCOMPLETE", 5);
DEFINE("VERSION_DELETED", 6);

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

	function getCheckedOutDate() { return $this->_oDocumentCore->getCheckedOutDate(); }
    function setCheckedOutDate($dNewValue) { $this->_oDocumentCore->setCheckedOutDate($dNewValue); }

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

    function getOemNo() { return $this->_oDocumentCore->getOemNo(); }

	function getLinkedDocumentId(){ return $this->_oDocumentCore->getLinkedDocumentId();}
    function setLinkedDocumentId($iNewValue){ $this->_oDocumentCore->setLinkedDocumentId($iNewValue);}

    // Document Metadata Items

    function getDocumentTypeID() { return $this->_oDocumentMetadataVersion->getDocumentTypeId(); }
    function setDocumentTypeID($sNewValue) { $this->_oDocumentMetadataVersion->setDocumentTypeId($sNewValue); }

    function getName() { return $this->_oDocumentMetadataVersion->getName(); }
    function setName($sNewValue) { $this->_oDocumentMetadataVersion->setName($sNewValue); }

    function getDescription() { return $this->_oDocumentMetadataVersion->getDescription(); }
    function setDescription($sNewValue) { $this->_oDocumentMetadataVersion->setDescription($sNewValue); }

    function getStatusID() { return $this->_oDocumentCore->getStatusId(); }
    function setStatusID($iNewValue) { $this->_oDocumentMetadataVersion->setStatusId($iNewValue); $this->_oDocumentCore->setStatusId($iNewValue); }

    function getMetadataStatusID() { return $this->_oDocumentMetadataVersion->getStatusId(); }
    function setMetadataStatusID($iNewValue) { $this->_oDocumentMetadataVersion->setStatusId($iNewValue); }

    function getMetadataVersion() { return $this->_oDocumentMetadataVersion->getMetadataVersion(); }
    function setMetadataVersion($iNewValue) { $this->_oDocumentMetadataVersion->setMetadataVersion($iNewValue); }

    function getMetadataVersionId() { return $this->_oDocumentMetadataVersion->getId(); } //_oDocumentCore->getMetadataVersionId(); }
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
    


    /**
     * Returns the symlink document instance
     *
     * @return Document the real document
     */
    function getRealDocument()
    {
        return $this->_oDocumentCore->getRealDocument();
    }

    /**
     * Checks if this document is a symbolic link to an other doc.
     *
     * @return boolean
     */
    function isSymbolicLink()
    {
        $documentCore = KTDocumentCore::get($this->getId());
        return $documentCore->isSymbolicLink();
    }

    /**
     * Switches the core of this document to the core of the real doc.
     *
     */
    function switchToRealCore()
    {
        if ($this->isSymbolicLink())
        {
             $this->_oDocumentCore = KTDocumentCore::get($this->getId());
        }
    }

    /**
     * Retrieves the ID of the real document
     *
     * @return int the ID
     */
    function getRealDocumentId()
    {
         $document = $this->getRealDocument();
         if (PEAR::isError($document)) { return $document; }
         return $document->getId();
    }

    /**
     * Switches this documents core to the core of the document this document is linking to.
     *
     */
    function switchToLinkedCore()
    {
    	if ($this->isSymbolicLink())
        {
            $document = $this->getRealDocument();
            if (PEAR::isError($document)) { return $document; }
            $this->_oDocumentCore = $document->_oDocumentCore;
            //also load metadata
            $this->_oDocumentMetadataVersion = $document->_oDocumentMetadataVersion;
        }
    }

    /**
     * Retrieves all symbolic links linking to this document
     *
     */
    function getSymbolicLinks(){
    	$sQuery = 'SELECT * FROM documents ' .
				'WHERE documents.linked_document_id =  '.$this->getId();
    	return DButil::getResultArray($sQuery);
    }


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

    	//Automatically load the information of the document this document links to, if any.
        $res = $this->switchToLinkedCore();
        if (PEAR::isError($res))
        {
            return $res;
        }

        // FIXME add error $res if MDV > $_oDC->getMDV
        if (is_null($iMetadataVersionId)) {
            $this->_oDocumentMetadataVersion = KTDocumentMetadataVersion::get($this->_oDocumentCore->getMetadataVersionId());
            $this->iCurrentMetadataVersionId = $this->_oDocumentCore->getMetadataVersionId();
        } else {
            $this->_oDocumentMetadataVersion = KTDocumentMetadataVersion::get($iMetadataVersionId);
            $this->iCurrentMetadataVersionId = $iMetadataVersionId;
        }
        if (PEAR::isError($this->_oDocumentMetadataVersion))
        {
        //	var_dump($this->_oDocumentMetadataVersion);
        	return $this->_oDocumentMetadataVersion;
        }

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
           ($sFolderPath == "" ? "Deleted Folder" : $sFolderPath) . " &raquo; " . sanitizeForHTML($this->getName());
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
        // NEW SEARCH

        return;

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
        // ensure metadata version is up to date
        $this->_oDocumentMetadataVersion = KTDocumentMetadataVersion::get($this->_oDocumentMetadataVersion->iId);
        $this->_oDocumentMetadataVersion =& $this->_oDocumentMetadataVersion->newCopy();
        if (PEAR::isError($this->_oDocumentMetadataVersion)) {
		    return $this->_oDocumentMetadataVersion;
        }
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
        if (PEAR::isError($this->_oDocumentContentVersion)) {
		    return $this->_oDocumentContentVersion;
        }
        if (PEAR::isError($this->_oDocumentMetadataVersion)) {
		    return $this->_oDocumentMetadataVersion;
        }
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

    	$GLOBALS["_OBJECTCACHE"]['Document'] = array();
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

	static function getStatusString($statusid)
	{
		$statuses = array(
			1=>_kt('Live'),
			2=>_kt('Published'),
			3=>_kt('Deleted'),
			4=>_kt('Archived'),
			5=>_kt('Incomplete'),
			6=>_kt('Version Deleted')
		);

		if (array_key_exists($statusid, $statuses))
		{
			return $statuses[$statusid];
		}

		return _kt('Unknown State');
	}





}
?>

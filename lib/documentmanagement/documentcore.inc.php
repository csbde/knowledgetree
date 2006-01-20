<?php

require_once(KT_LIB_DIR . '/ktentity.inc');

class KTDocumentCore extends KTEntity {
    var $_bUsePearError = true;

    /** The original creator of the document */
    var $iCreatorId;

    /** The creation time of the document */
    var $dCreated;

    /** The user that last modified the document */
    var $iModifiedUserId;

    /** The time of the last modification to the document */
    var $dModified;

    /** The parent folder of the document */
    var $iFolderId;

    /** List of folder from the root to the document */
    var $sParentFolderIds;

    /** Fully qualified path of the document */
    var $sFullPath;

    /** Status of the document (live, deleted, archived, &c.) */
    var $iStatusId;

    /** Where the document receives its permission information from */
    var $iPermissionObjectId;
    /** The fully looked-up permission information for this document */
    var $iPermissionLookupId;
    
    /** The most recent metadata version for the object */
    var $iMetadataVersionId;

    var $iMetadataVersion;

    var $bIsCheckedOut;
    var $iCheckedOutUserId;

    var $_aFieldToSelect = array(
        "iId" => "id",

        // transaction-related
        "iCreatorId" => 'creator_id',
        "dCreated" => 'created',
        "iModifiedUserId" => 'modified_user_id',
        "dModified" => 'modified',
        "iMetadataVersionId" => 'metadata_version_id',
        "iMetadataVersion" => 'metadata_version',

        // location-related
        "iFolderId" => 'folder_id',
        "sParentFolderIds" => 'parent_folder_ids',
        "sFullPath" => 'full_path',

        // status
        "iStatusId" => 'status_id',
        "bIsCheckedOut" => 'is_checked_out',
        "iCheckedOutUserId" => 'checked_out_user_id',

        // permission-related
        "iPermissionObjectId" => 'permission_object_id',
        "iPermissionLookupId" => 'permission_lookup_id',
    );

    function KTDocument() {
    }

    // {{{ getters/setters
    function getCreatorId() { return $this->iCreatorId; }
    function setCreatorId($iNewValue) { $this->iCreatorId = $iNewValue; }
    function getCreatedDateTime() { return $this->dCreated; }
    function getModifiedUserId() { return $this->iModifiedUserId; }
    function setModifiedUserId($iNewValue) { $this->iModifiedUserId = $iNewValue; }
    function getLastModifiedDate() { return $this->dModified; }
    function setLastModifiedDate($dNewValue) { $this->dModified = $dNewValue; }

    function getFolderId() { return $this->iFolderId; }
    function setFolderId($iNewValue) { $this->iFolderId = $iNewValue; }

    function getStatusId() { return $this->iStatusId; }
    function setStatusId($iNewValue) { $this->iStatusId = $iNewValue; }
    function getIsCheckedOut() { return $this->bIsCheckedOut; }
    function setIsCheckedOut($bNewValue) { $this->bIsCheckedOut = KTUtil::anyToBool($bNewValue); }
    function getCheckedOutUserId() { return $this->iCheckedOutUserId; }
    function setCheckedOutUserId($iNewValue) { $this->iCheckedOutUserId = $iNewValue; }

    function getPermissionObjectId() { return $this->iPermissionObjectId; }
    function setPermissionObjectId($iNewValue) { $this->iPermissionObjectId = $iNewValue; }
    function getPermissionLookupId() { return $this->iPermissionLookupId; }
    function setPermissionLookupId($iNewValue) { $this->iPermissionLookupId = $iNewValue; }
    
    function getMetadataVersionId() { return $this->iMetadataVersionId; }
    function setMetadataVersionId($iNewValue) { $this->iMetadataVersionId = $iNewValue; }
    
    function getMetadataVersion() { return $this->iMetadataVersion; }
    function setMetadataVersion($iNewValue) { $this->iMetadataVersion = $iNewValue; }
    
    // }}}

    // {{{ getParentId
    /**
     * Allows documents to be treated like folders in terms of finding
     * their parent objects.
     */
    function getParentId() {
        return $this->getFolderId();
    }
    // }}}

    // {{{ ktentity requirements
    function _fieldValues () {
        $this->sFullPath = KTDocumentCore::_generateFolderPath($this->iFolderId);
        $this->sParentFolderIds = KTDocumentCore::_generateFolderIds($this->iFolderId);
        return parent::_fieldValues();
    }

    /**
     * Recursive function to generate a comma delimited string containing
     * the parent folder ids
     *
     * @return String   comma delimited string containing the parent folder ids
     */
    function _generateParentFolderIds($iFolderId) {
        $sTable = KTUtil::getTableName('folders');
        if (empty($iFolderId)) {
            return;
        }

        $sQuery = sprintf('SELECT parent_id FROM %s WHERE id = ?', $sTable);
        $aParams = array($iFolderId);
        $iParentId = DBUtil::getOneResultKey(array($sQuery, $aParams), 'parent_id');
        return KTDocumentCore::_generateParentFolderIds($iParentId) . ",$iFolderId";
    }

    /**
     * Returns a comma delimited string containing the parent folder ids, strips leading /
     *
     * @return String   comma delimited string containing the parent folder ids
     */
    function _generateFolderIds($iFolderId) {
        $sFolderIds = KTDocumentCore::_generateParentFolderIds($iFolderId);
        return substr($sFolderIds, 1, strlen($sFolderIds));
    }

    /**
     * Recursively generates forward slash deliminated string giving full path of document
     * from file system root url
     */
    function _generateFullFolderPath($iFolderId) {
        //if the folder is not the root folder
        if (empty($iFolderId)) {
            return;
        }
        $sTable = KTUtil::getTableName('folders');
        $sQuery = sprintf("SELECT name, parent_id FROM %s WHERE Id = ?", $sTable);
        $aParams = array($iFolderId);
        $aRow = DBUtil::getOneResult(array($sQuery, $aParams));
        return KTDocumentCore::_generateFullFolderPath($aRow["parent_id"]) . "/" . $aRow["name"];
    }

    /**
     * Returns a forward slash deliminated string giving full path of document, strips leading /
     */
    function _generateFolderPath($iFolderId) {
        $sPath = KTDocumentCore::_generateFullFolderPath($iFolderId);
        $sPath = substr($sPath, 1, strlen($sPath));
        return $sPath;
    }
    // }}}

    // {{{ create
    function create() {
        if (empty($this->dCreated)) {
            $this->dCreated = getCurrentDateTime();
        }
        if (empty($this->dModified)) {
            $this->dModified = getCurrentDateTime();
        }
        if (empty($this->iModifiedUserId)) {
            $this->iModifiedUserId = $this->iCreatorId;
        }
        if (empty($this->iMetadataVersion)) {
            $this->iMetadataVersion = 0;
        }
        if (empty($this->bIsCheckedOut)) {
            $this->bIsCheckedOut = false;
        }
        $oFolder = Folder::get($this->getFolderId());
        $this->iPermissionObjectId = $oFolder->getPermissionObjectId();
        $res = parent::create();

        if ($res === true) {
            KTPermissionUtil::updatePermissionLookup($this);
        }

        return $res;
    }
    // }}}

    // {{{ update
    function update($bPathMove = false) {
        $res = parent::update();
        if (($res === true) && ($bPathMove === true)) {
            KTPermissionUtil::updatePermissionLookup($this);
        }
        return $res;
    }
    // }}}

    // {{{ get
    function &get($iId) {
        return KTEntityUtil::get('KTDocumentCore', $iId);
    }
    // }}}

    // {{{ getList
    function &getList($sWhere = null, $aOptions) {
        return KTEntityUtil::getList2('KTDocumentCore', $sWhere, $aOptions);
    }
    // }}}

    // {{{ _table
    function _table() {
        return KTUtil::getTableName('documents');
    }
    // }}}

    // {{{ getPath
    /**
     * Get the full path for a document
     *
     * @return string full path of document
     */
    function getPath() {
        return Folder::getFolderPath($this->iFolderId) . $this->sFileName;
    }
    // }}}

    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTDocumentCore', $aOptions);
    }
}

?>

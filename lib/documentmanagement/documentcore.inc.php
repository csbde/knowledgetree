<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

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
    
    var $iRestoreFolderId;
    var $sRestoreFolderPath;

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
        "bImmutable" => 'immutable',

        // permission-related
        "iPermissionObjectId" => 'permission_object_id',
        "iPermissionLookupId" => 'permission_lookup_id',
        "iOwnerId" => 'owner_id',
        
        // restore-related
        'iRestoreFolderId' => 'restore_folder_id',
        'sRestoreFolderPath' => 'restore_folder_path',
    );

    function KTDocument() {
    }

    // {{{ getters/setters
    function getCreatorId() { return $this->iCreatorId; }
    function setCreatorId($iNewValue) { $this->iCreatorId = $iNewValue; }
    function getOwnerId() { return $this->iOwnerId; }
    function setOwnerId($iNewValue) { $this->iOwnerId = $iNewValue; }    
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
    
    function getFullPath() { return $this->sFullPath; }

    function getImmutable() { return $this->bImmutable; }
    function setImmutable($mValue) { $this->bImmutable = $mValue; }
    
    function getRestoreFolderId() { return $this->iRestoreFolderId; }
    function setRestoreFolderId($iValue) { $this->iRestoreFolderId = $iValue; }    

    function getRestoreFolderPath() { return $this->sRestoreFolderPath; }
    function setRestoreFolderPath($sValue) { $this->sRestoreFolderPath = $sValue; }    
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
        $sNewFullPath = Folder::generateFolderPath($this->iFolderId);
        if (!PEAR::isError($sNewFullPath)) {
            $this->sFullPath = $sNewFullPath;
        }
        $sNewParentFolderIds = Folder::generateFolderIds($this->iFolderId);
        if (!PEAR::isError($sNewParentFolderIds)) {
            $this->sParentFolderIds = $sNewParentFolderIds;
        }
        return parent::_fieldValues();
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
        return Folder::generateFolderPath($iFolderId);
/*
        $sPath = KTDocumentCore::_generateFullFolderPath($iFolderId);
        $sPath = substr($sPath, 1, strlen($sPath));
        return $sPath;
*/
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
        if (empty($this->iOwnerId)) {
            $this->iOwnerId = $this->iCreatorId;
        }
        if (empty($this->iMetadataVersion)) {
            $this->iMetadataVersion = 0;
        }
        if (empty($this->bIsCheckedOut)) {
            $this->bIsCheckedOut = false;
        }
        if (empty($this->bImmutable)) {
            $this->bImmutable = false;
        }
        $oFolder = Folder::get($this->getFolderId());
        $this->iPermissionObjectId = $oFolder->getPermissionObjectId();
        $res = parent::create();
        
        return $res;
    }
    // }}}

    // {{{ update
    function update($bPathMove = false) {
        //var_dump($this); exit(0);    
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

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

    var $dCheckedOut;

    var $sOemNo;
    
    /** ID of the document this document links to(if any) */
    var $iLinkedDocumentId;

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

        'dCheckedOut'=>'checkedout',
        'sOemNo'=>'oem_no',
    	'iLinkedDocumentId' => 'linked_document_id'
    );

    function KTDocumentCore() {
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
    function getCheckedOutDate() { return $this->dCheckedOut; }
    function setCheckedOutDate($dNewValue) { $this->dCheckedOut = $dNewValue; }

    function getOemNo() { return $this->sOemNo; }

    function getFolderId() { return $this->iFolderId; }
    function setFolderId($iNewValue) { $this->iFolderId = $iNewValue; }

    function getStatusId() { return $this->iStatusId; }
    function setStatusId($iNewValue) { $this->iStatusId = $iNewValue; }
    function getIsCheckedOut() { return $this->bIsCheckedOut; }
    function setIsCheckedOut($bNewValue) { $this->bIsCheckedOut = KTUtil::anyToBool($bNewValue);
    	$date = $bNewValue?date('Y-m-d H:i:s'):null;
   		$this->setCheckedOutDate($date);
     }
    function getCheckedOutUserId() { return $this->iCheckedOutUserId; }
    function setCheckedOutUserId($iNewValue) { if ($iNewValue < 0) $iNewValue = null; $this->iCheckedOutUserId = $iNewValue; }

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
    
    function getLinkedDocumentId(){ return $this->iLinkedDocumentId;}
    function setLinkedDocumentId($iNewValue){ $this->iLinkedDocumentId = $iNewValue;}
    
    /**
     * Returns the ID of the real document.
     *
     * @return int the ID
     */
    function getRealDocumentId(){ 
    	$realDocument = $this->getRealDocument();
    	return $realDocument->getId();
    }
    
    /**
     * Retrieves the real document (which is a shortcut that links to the linked document)
     *
     */
    function getRealDocument()
    {
        if (is_null($this->getLinkedDocumentId()))
        {
            return Document::get($this->getId());
        }

        $document = Document::get($this->getLinkedDocumentId());
        return $document->getRealDocument();
    }

    /**
     * Checks if this is a shortcut
     *
     * @return boolean
     */
    function isSymbolicLink()
    {
        return !is_null($this->getLinkedDocumentId());
    }
    
    
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
        static $lastFolder = null;
		if (is_null($lastFolder) || $lastFolder->getID() !== $this->iFolderId) {
		  $lastFolder = Folder::get($this->iFolderId);
		  if (PEAR::isError($lastFolder)) {
			$lastFolder = null;
		  }
		}
        if (!is_null($lastFolder)) {
        	$this->sFullPath = 'pending';
        	if (!is_null($this->getMetadataVersionId()))
        	{
	       		$metadata = KTDocumentMetadataVersion::get($this->getMetadataVersionId());
				$name =$metadata->getName();
        		if ($lastFolder->getId() == 1)
        		{
        			$this->sFullPath = $name;
        		}
        		else
        		{
					$this->sFullPath = $lastFolder->getFullPath() . '/' . $name;
        		}
        	}
			$this->sParentFolderIds = $lastFolder->getParentFolderIDs();
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
        if (PEAR::isError($oFolder) || ($oFolder === false) || empty($oFolder) ) {
            return false;
        }
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

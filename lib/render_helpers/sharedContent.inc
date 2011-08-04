<?php

/**
 * $Id$
 *
 * Shared content database object
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_PLUGIN_DIR . '/comments/comments.php');

class SharedContent extends KTEntity {

    public $iUserId; // user id of owner
    public $iObjectId; // object id of document or folder
    public $sType; // object type if folder or document
    public $iPermissions; // permissions on object
    public $iParentObjectId; // parent object id if one exists

    public $_aFieldToSelect = array(
        'iUserId' => 'user_id',
        'iSystemUserId' => 'system_user_id',
        'iObjectId' => 'object_id',
        'sType' => 'type',
        'iPermissions' => 'permissions',
        'iParentObjectId' => 'parent_id',
    );

    public $_bUsePearError = true;

    public function __construct($userId, $systemUserId, $objectId, $type, $permissions, $parentObjectId = null)
    {
        $this->iUserId = $userId;
        $this->iSystemUserId = $systemUserId;
        $this->iObjectId = $objectId;
        $this->sType = $type;
        $this->iPermissions = $permissions;
        $this->iParentObjectId = $parentObjectId;
    }

    public function create()
    {
        // Check if parent is shared
        if ($this->sType == 'folder')
        {
            // Get folder
            $folder = Folder::get($this->iObjectId);
            $parentFolderId = $folder->getParentID();
            // Update parent id if shared parent exists
            if ($this->exists($this->iUserId, $parentFolderId, 'folder'))
            {
                $this->iParentObjectId = $parentFolderId;
            }
        }
        else if ($this->sType == 'document')
        {
            // Get document
            $document = Document::get($this->iObjectId);
            $parentFolderId = $document->getParentID();
            // Do not create a shared entry if document is in a shared folder
            if ($this->exists($this->iUserId, $parentFolderId, 'folder'))
            {
                return true;
            }
        }

        // Create shared content object
        $res = parent::create();
        if ($res)
        {
            // If this was a folder object delete all shared documents in that folder,
            // As they will inherit permissions from the parent folder
            if ($this->sType == 'folder')
            {
                $aDocumentsInFolder = explode(',', Folder::getDocumentIDs($this->iObjectId));
                if (!PEAR::isError($aDocumentsInFolder))
                {
                    foreach ($aDocumentsInFolder as $doc)
                    {
                        $this->deleteDocument($doc);
                    }
                }
            }
        }

        return $res;
    }

    public function delete()
    {
        $sql = "DELETE FROM shared_content WHERE user_id = '{$this->iUserId}' AND object_id = '{$this->iObjectId}' AND type = '{$this->sType}' LIMIT 1";
        $res = DBUtil::runQuery($sql);
        if (PEAR::isError($res)) { return false; }

        return (count($res) > 0);
    }

    public function deleteDocument($id)
    {
        $sql = "DELETE FROM shared_content WHERE user_id = '{$this->iUserId}' AND object_id = '{$id}' AND type = 'document'";
        $res = DBUtil::runQuery($sql);
        if (PEAR::isError($res)) { return false; }

        return (count($res) > 0);
    }

    /**
    * Retrieve shared content objects database table name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    public function _table() { return KTUtil::getTableName('shared_content'); }

    // ---------------
    // Getters/setters
    // ---------------
    /**
    * Retrieve a list of SharedContent objects
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $sWhereClause - string
    * @param $aOptions - array
    * @return SharedContent objects - array
    */
    public function getList($sWhereClause = null, $aOptions = null)
    {
        if (is_null($aOptions)) { $aOptions = array(); }
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','name');
        return KTEntityUtil::getList2('SharedContent', $sWhereClause, $aOptions);
    }

    /**
    * Retrieve a list of SharedContent objects for a user
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $sWhereClause - string
    * @param $aOptions - array
    * @return SharedContent objects - array
    */
    public function getUsersSharedContents($userId = null, $folderId, $aOptions = null)
    {
        return array('documents' => $this->getSharedDocuments($userId, $folderId), 'folders' => $this->getSharedFolders($userId, $folderId));
    }

    /**
     * Retrieve a list of Folders
     *
     * @param int $userId
     * @param int $folderId
     * @return array $folders
     */
    public function getSharedFolders($userId, $folderId)
    {
        $sharedContent = 'sc.object_id as id, sc.permissions AS object_permissions, sc.user_id AS user_id, sc.parent_id AS parent_id';
        $folders = 'f.name AS title, cu.name as created_by, f.full_path AS full_path, f.linked_folder_id AS linked_folder_id';
        if ($folderId == 1)
        {
            $sql = "SELECT $sharedContent, $folders
                    FROM `shared_content` as sc,
                    folders AS f,
                    users AS cu
                    WHERE (sc.type = 'folder' AND sc.user_id = '$userId' AND f.id = sc.object_id AND f.creator_id = cu.id AND sc.parent_id is NULL)
                    ";
        }
        else
        {
            $sql = "SELECT $sharedContent, $folders
                    FROM `shared_content` as sc,
                    folders AS f,
                    users AS cu
                    WHERE sc.type = 'folder' AND sc.object_id = f.id AND f.parent_id = '$folderId' AND f.creator_id = cu.id AND sc.parent_id = '$folderId'";
        }

        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return false; }

        return $res;
    }

    /**
     * Retrieve a list of Documents
     *
     * @param int $userId
     * @param int $folderId
     * @return array $documents
     */
    // NOTE This function seems to get duplicated documents, which are filtered later (no duplicates show up in the view.)
    //      If possible, get the query to not return duplicates.
    public function getSharedDocuments($userId, $folderId)
    {
        // Fields to select
        $sharedContent = 'sc.object_id as id, sc.permissions AS object_permissions, sc.parent_id AS parent_id';
        $documentMetadataVersion = 'dmv.name AS title';
        $documentTypesLookup = 'dtl.name AS document_type';
        $documentContentVersion = 'dcv.filename AS filename, dcv.storage_path AS storage_path, dcv.has_rendition AS has_rendition, dcv.size AS filesize, dcv.major_version as major_version, dcv.minor_version AS minor_version';
        $users = 'cu.name as created_by, cu.name AS owned_by, mu.name as modified_by';
        $documents = 'd.created AS created_date, d.modified AS modified_date, d.immutable AS is_immutable, d.is_checked_out AS is_checked_out, d.checked_out_user_id AS checked_out_by_id, d.checkedout AS checked_out_date, d.linked_document_id as linked_document_id';
        $mimeTypes = 'mt.id AS mimetypeid, mt.mimetypes AS mime_type, mt.icon_path AS mime_icon_path';

        if ($folderId == 1)
        {
            $sql = "SELECT $sharedContent, $documentMetadataVersion, $documentTypesLookup, $documentContentVersion, $users, $documents, $mimeTypes
                    FROM shared_content AS sc, documents AS d, document_metadata_version AS dmv, document_types_lookup AS dtl, document_content_version AS dcv, users AS cu, users AS mu, mime_types AS mt
                    WHERE sc.type = 'document' AND d.id = sc.object_id AND sc.user_id='$userId' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id AND sc.parent_id is NULL";
        }
        else
        {
            // Check if folder has been shared, before retrieving document listing
            if (!$this->exists($userId, $folderId, 'folder'))
            {
                return false;
            }

            $sql = "SELECT d.id AS id, sc.permissions AS object_permissions, sc.parent_id AS parent_id, $documentMetadataVersion, $documentTypesLookup, $documentContentVersion, $users, $documents, $mimeTypes
                    FROM documents AS d, document_metadata_version AS dmv, document_types_lookup AS dtl, document_content_version AS dcv, users AS cu, users AS mu, mime_types AS mt, shared_content sc
                    WHERE sc.type = 'folder' AND sc.user_id='$userId' AND sc.object_id = '$folderId' AND d.folder_id = '$folderId' AND d.status_id = '1' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id";
        }

        $sql .= ' AND d.status_id = ' . LIVE;

        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return false; }

        return $res;
    }

    /**
     * Check if shared object exists
     *
     * @param int $userId
     * @param int $objectId
     * @param string $type
     * @return boolean
     */
    public function exists($userId = null, $objectId = null, $type = null)
    {
        $userId = is_null($userId) ? $this->iUserId : $userId;
        $objectId = is_null($objectId) ? $this->iObjectId : $objectId;
        $type = is_null($type) ? $this->sType : $type;
        $sql = "SELECT user_id FROM shared_content WHERE user_id = '{$userId}' AND object_id = '{$objectId}' AND type = '{$type}' LIMIT 1";
        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return false; }

        return (count($res) > 0);
    }

    // ------------------------
    // Shared content utilities
    // ------------------------
    /**
     * Check shared user permissions on a document
     *
     * @param int $userId
     * @param int $documentId
     * @param int $folderId [optional]
     * @param int $permissions [optional]
     * @return boolean
     */
    static public function canAccessDocument($userId, $documentId, $folderId = null, $permissions = null)
    {
        // Check if document has been shared with user
        $sql = "SELECT user_id FROM shared_content as sc WHERE user_id = '{$userId}' AND object_id = '{$documentId}' AND type = 'document'";
        if (!is_null($permissions)) {
            $sql .= " AND sc.permissions = $permissions";
        }

        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return false; }

        if (count($res) > 0)
        {
            return true;
        }
        else
        {
            // Check if documents parent folder has been shared with user
            // If folder id was submitted, use, else query for the parent folder
            if (empty($folderId))
            {
                $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$userId}' AND sc.object_id = (SELECT d.folder_id FROM documents d WHERE d.id = '{$documentId}') AND sc.type = 'folder')";
            }
            else {
                $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$userId}' AND sc.object_id = '{$folderId}' AND sc.type = 'folder')";
            }

            if (!is_null($permissions)) {
                $sql .= " AND sc.permissions = $permissions";
            }

            $res = DBUtil::getResultArray($sql);
            if (PEAR::isError($res)) { return false; }
        }

        return (count($res) > 0);
    }

    /**
     * Check shared user permissions on a folder
     *
     * @param int $userId
     * @param int $documentId
     * @param int $folderId [optional]
     * @param int $permissions [optional]
     * @return boolean
     */
    static public function canAccessFolder($userId, $folderId)
    {
        $sql = "SELECT user_id FROM shared_content as sc WHERE (user_id = '{$userId}' AND object_id = '{$folderId}' AND type = 'folder')";
        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return false; }

        return (count($res) > 0);
    }

    // TODO : Get only one document metadata version from database

    /**
     * Retreive the permission of an object
     *
     * @param int $userId
     * @param int $objectId
     * @param int $parentId
     * @param string $type
     * @return mixed - false if no permissions on object, and the permission value if one exists
     */
    static public function getPermissions($userId, $objectId = null, $parentId = null, $type = 'document')
    {
        if ($type == 'document')
        {
            return SharedContent::getDocumentPermissions($userId, $objectId, $parentId);
        }
        else if ($type == 'folder')
        {
            return SharedContent::getFolderPermissions($userId, $objectId, $parentId);
        }
    }

    /**
     * Retrieve the permission on a document.
     *
     * @param int $userId
     * @param int $objectId
     * @param int $parentId
     * @return mixed - false if no permissions on document, and the permission value if one exists
     */
    static public function getDocumentPermissions($userId, $objectId = null, $parentId = null)
    {
        // TODO improve this, perhaps with a combined query?
        if (empty($parentId)) {
            $sql = "SELECT sc.permissions FROM shared_content as sc WHERE (user_id = '{$userId}' AND object_id = '{$objectId}' AND type = 'document')";
            $result = self::getPermissionQueryResult($sql);
            if ($result !== false) {
                return $result;
            }

            $sql = "SELECT sc.permissions FROM shared_content sc WHERE (sc.user_id = '{$userId}' AND sc.object_id = (SELECT d.folder_id FROM documents d WHERE d.id = '{$objectId}') AND sc.type = 'folder')";
        }
        else {
            $sql = "SELECT sc.permissions FROM shared_content sc WHERE (sc.user_id = '{$userId}' AND sc.object_id = '{$parentId}' AND sc.type = 'folder')";
        }

        return self::getPermissionQueryResult($sql);
    }

    /**
     * Retrieve the permission on a folder.
     *
     * @param int $userId
     * @param int $objectId
     * @param int $parentId
     * @return mixed - false if no permissions on folder, and the permission value if one exists
     */
    static public function getFolderPermissions($userId, $objectId = null, $parentId = null)
    {
        $sql = "SELECT sc.permissions FROM shared_content as sc "
            . "WHERE sc.user_id = '{$userId}' "
            . "AND sc.object_id = '{$objectId}' "
            . "AND sc.type = 'folder'";

        return self::getPermissionQueryResult($sql);
    }

    static private function getPermissionQueryResult($sql)
    {
        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res) || (count($res) == 0)) {
            return false;
        }

        return $res[0]['permissions'];
    }

    static public function getDocumentIds($userId)
    {
        if (!isset($userId)) {
            return array();
        }

        $ids = array();
        $sql = "SELECT user_id, object_id, type from shared_content where user_id = '$userId' and type = 'document'";
        $results = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) { return $ids; }
        if (count($results) > 0)
        {
            foreach ($results as $result)
            {
                $ids[] = $result['object_id'];
            }
        }

        return $ids;
    }
}

?>

<?php
/**
 * $Id$
 *
 * Represents a user as per the users table in the database.
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
    
	public function __construct($iUserId, $iSystemUserId, $iObjectId, $sType, $iPermissions, $iParentObjectId = null)
    {
		$this->iUserId = $iUserId;
		$this->iSystemUserId = $iSystemUserId;
		$this->iObjectId = $iObjectId;
		$this->sType = $sType;
		$this->iPermissions = $iPermissions;
		$this->iParentObjectId = $iParentObjectId;
    }
    
    public function create()
    {
    	// Check if parent is shared
    	if($this->sType == 'folder')
    	{
    		// Get folder
    		$oFolder = Folder::get($this->iObjectId);
    		$iParentFolderId = $oFolder->getParentID();
	    	// Update parent id if shared parent exists
    		if($this->exists($this->iUserId, $iParentFolderId, 'folder'))
    		{
    			$this->iParentObjectId = $iParentFolderId;
    		}
    	} 
    	else if($this->sType == 'document')
    	{
    		// Get document
    		$oDocument = Document::get($this->iObjectId);
    		$iParentFolderId = $oDocument->getParentID();
	    	// Do not create a shared entry if document is in a shared folder
    		if($this->exists($this->iUserId, $iParentFolderId, 'folder'))
    		{
    			return true;
    		}
    	}
    	// Create shared content object
    	$res = parent::create();
    	if($res)
    	{
    		// If this was a folder object delete all shared documents in that folder,
    		// As they will inherit permissions from the parent folder
    		if($this->sType == 'folder')
    		{
    			$aDocumentsInFolder = explode(',', Folder::getDocumentIDs($this->iObjectId));
    			if(!PEAR::isError($aDocumentsInFolder))
    			{
    				foreach ($aDocumentsInFolder as $doc)
    				{
    					$this->deleteDocument($doc);
    				}
    			}
    		}
    		else if($this->sType == 'document')
    		{
    			// Add a comment
    			$oFromUser = User::get($this->iSystemUserId);
    			$oWithUser = User::get($this->iUserId);
    			$username = $oFromUser->getUserName();
    			$sharedname = $oWithUser->getUserName();
    			Comments::add_comment($this->iObjectId, "$username has shared this document with $sharedname");
    		}
    	}
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
    public function _table () { return KTUtil::getTableName('shared_content'); }
    
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
    public function getUsersSharedContents($iUserId = null, $iFolderId, $aOptions = null) 
    {
    	return array('documents' => $this->getSharedDocuments($iUserId, $iFolderId), 'folders' => $this->getSharedFolders($iUserId, $iFolderId));
    }

    /**
     * Retrieve a list of Folders
     *
     * @param int $iUserId
     * @param int $iFolderId
     * @return array $folders
     */
    public function getSharedFolders($iUserId, $iFolderId)
    {
    	$shared_content = 'sc.object_id as id, sc.permissions AS object_permissions, sc.user_id AS user_id, sc.parent_id AS parent_id';
    	$folders = 'f.name AS title, cu.name as created_by, f.full_path AS full_path';
    	if ($iFolderId == 1)
    	{
	    	$sql = "SELECT $shared_content, $folders
	    			FROM `shared_content` as sc, 
					folders AS f,
					users AS cu
					WHERE (sc.type = 'folder' AND sc.user_id = '$iUserId' AND f.id = sc.object_id AND f.creator_id = cu.id AND sc.parent_id is NULL)
					";
    	}
    	else 
    	{
			$sql = "SELECT $shared_content, $folders
	    			FROM `shared_content` as sc, 
					folders AS f,
					users AS cu
					WHERE sc.type = 'folder' AND sc.object_id = f.id AND f.parent_id = '$iFolderId' AND f.creator_id = cu.id AND sc.parent_id = '$iFolderId'";
    	}
    	
    	$res = DBUtil::getResultArray($sql);
    	if (PEAR::isError($res)) { return false; }
    	
    	return $res;
    }
    
    /**
     * Retrieve a list of Documents
     *
     * @param int $iUserId
     * @param int $iFolderId
     * @return array $documents
     */
    public function getSharedDocuments($iUserId, $iFolderId)
    {
    	// Fields to select
		$shared_content = 'sc.object_id as id, sc.permissions AS object_permissions, sc.parent_id AS parent_id';
		$document_metadata_version = 'dmv.name AS title';
		$document_types_lookup = 'dtl.name AS document_type';
		$document_content_version = 'dcv.filename AS filename, dcv.storage_path AS storage_path, dcv.has_rendition AS has_rendition, dcv.size AS filesize, dcv.major_version as major_version, dcv.minor_version AS minor_version';
		$users = 'cu.name as created_by, cu.name AS owned_by, mu.name as modified_by';
		$documents = 'd.created AS created_date, d.modified AS modified_date, d.immutable AS is_immutable, d.is_checked_out AS is_checked_out, d.checked_out_user_id AS checked_out_by, d.checkedout AS checked_out_date, d.linked_document_id as linked_document_id';
		$mime_types = 'mt.id AS mimetypeid, mt.mimetypes AS mime_type, mt.icon_path AS mime_icon_path';    	
		
    	if ($iFolderId == 1)
    	{
	    	$sql = "SELECT $shared_content, $document_metadata_version, $document_types_lookup, $document_content_version, $users, $documents, $mime_types 
	    			FROM shared_content AS sc, documents AS d, document_metadata_version AS dmv, document_types_lookup AS dtl, document_content_version AS dcv, users AS cu, users AS mu, mime_types AS mt 
	    			WHERE sc.type = 'document' AND d.id = sc.object_id AND sc.user_id='$iUserId' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id AND sc.parent_id is NULL";
    	}
    	else 
    	{
    		// Check if folder has been shared, before retrieving document listing
    		if (!$this->exists($iUserId, $iFolderId, 'folder')) 
    		{
    			return false;
    		}
    		
    		$sql = "SELECT d.id AS id, sc.permissions AS object_permissions, sc.parent_id AS parent_id, $document_metadata_version, $document_types_lookup, $document_content_version, $users, $documents, $mime_types 
    				FROM documents AS d, document_metadata_version AS dmv, document_types_lookup AS dtl, document_content_version AS dcv, users AS cu, users AS mu, mime_types AS mt, shared_content sc 
    				WHERE sc.type = 'folder' AND sc.user_id='$iUserId' AND sc.object_id = '$iFolderId' AND d.folder_id = '$iFolderId' AND d.status_id = '1' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id";
    	}
    	
    	$res = DBUtil::getResultArray($sql);
    	if (PEAR::isError($res)) { return false; }
    	
    	return $res;
    }
    
    /**
     * Check if shared object exists
     *
     * @param int $iUserId
     * @param int $iObjectId
     * @param string $sType
     * @return boolean
     */
    public function exists($iUserId = null, $iObjectId = null, $sType = null)
    {
    	$iUserId = is_null($iUserId) ? $this->iUserId : $iUserId;
    	$iObjectId = is_null($iObjectId) ? $this->iObjectId : $iObjectId;
    	$sType = is_null($sType) ? $this->sType : $sType;
    	$sql = "SELECT user_id FROM shared_content WHERE user_id = '{$iUserId}' AND object_id = '{$iObjectId}' AND type = '{$sType}' LIMIT 1";
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
     * @param int $iUserId
     * @param int $iDocumentId
     * @param int $iFolderId [optional]
     * @param int $permissions [optional]
     * @return boolean
     */
    static public function canAccessDocument($iUserId, $iDocumentId, $iFolderId = null, $permissions = null)
    {
    	// Check if document has been shared with user
    	$sql = "SELECT user_id FROM shared_content as sc WHERE user_id = '{$iUserId}' AND object_id = '{$iDocumentId}' AND type = 'document'";
    	if(!is_null($permissions))
    		$sql .= " AND sc.permissions = $permissions";
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
    		if (empty($iFolderId)) 
    		{
    		    $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = (SELECT d.folder_id FROM documents d WHERE d.id = '{$iDocumentId}') AND sc.type = 'folder')";
    		}
    		else {
    		    $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = '{$iFolderId}' AND sc.type = 'folder')";
    		}
    		if(!is_null($permissions))
    			$sql .= " AND sc.permissions = $permissions";
    		
    		$res = DBUtil::getResultArray($sql);
    		if (PEAR::isError($res)) { return false; }
    	}
    	
    	return (count($res) > 0);
    }
    
    /**
     * Check shared user permissions on a folder
     *
     * @param int $iUserId
     * @param int $iDocumentId
     * @param int $iFolderId [optional]
     * @param int $permissions [optional]
     * @return boolean
     */
    static public function canAccessFolder($iUserId, $iFolderId)
    {
    	$sql = "SELECT user_id FROM shared_content as sc WHERE (user_id = '{$iUserId}' AND object_id = '{$iFolderId}' AND type = 'folder')";
    	$res = DBUtil::getResultArray($sql);
    	if (PEAR::isError($res)) { return false; }
    	
    	return (count($res) > 0);
    }
    
    // TODO : Establish if a link has been created between shared folders
    // TODO : Remove shared documents if a folder is shared
    // TODO : Get only one document metadata version from database
    
    /**
     * Retreive the permission of a document
     *
     * @param int $iUserId
     * @param int $iObjectId
     * @param int $iFolderId
     * @param string $sType
     * @return mixed
     */
    static public function getPermissions($iUserId, $iObjectId = null, $iFolderId = null, $sType = 'document')
    {
    	// Check if shared object exists
    	$sql = "SELECT sc.permissions FROM shared_content as sc WHERE (user_id = '{$iUserId}' AND object_id = '{$iObjectId}' AND type = '{$sType}')";
    	$res = DBUtil::getResultArray($sql);
    	if (PEAR::isError($res)) { return false; }
    	if(count($res) > 0)
    	{
    		return $res[0]['permissions'];
    	}
    	else 
    	{
			if($sType == 'document')
			{
				return SharedContent::getDocumentPermissions($iUserId, $iObjectId, $iFolderId);
			}
			else if($sType == 'folder')
			{
				return SharedContent::getFolderPermissions($iUserId, $iObjectId, $iFolderId);
			}
    	}
    	
    	return false;
    }
    
	static private function getDocumentPermissions($iUserId, $iObjectId = null, $iFolderId = null)
	{
		// Check if documents parent folder has been shared with user
		if (empty($iFolderId)) 
		{
		    $sql = "SELECT sc.permissions FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = (SELECT d.folder_id FROM documents d WHERE d.id = '{$iObjectId}') AND sc.type = 'folder')";
		}
		// If folder id was submitted, use, else query for the parent folder
		else {
		    $sql = "SELECT sc.permissions FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = '{$iFolderId}' AND sc.type = 'folder')";
		}
		$res = DBUtil::getResultArray($sql);
		if (PEAR::isError($res)) { return false; }
		if(count($res) > 0)
		{
			return $res[0]['permissions'];
		}
		
		return false;
	}
	
	static private function getFolderPermissions($iUserId, $iObjectId = null, $iFolderId = null)
	{
		// Check if documents parent folder has been shared with user
		// If folder id was submitted, use, else query for the parent folder
		if (empty($iFolderId)) 
		{
		    $sql = "SELECT sc.permissions FROM shared_content as sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = '{$iObjectId}' AND sc.type = 'folder'";
		}
		else {
		    $sql = "SELECT sc.permissions FROM shared_content as sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = '{$iFolderId}' AND sc.type = 'folder')";
		}
		$res = DBUtil::getResultArray($sql);
		if (PEAR::isError($res)) { return false; }
		if(count($res) > 0)
		{
			return $res[0]['permissions'];
		}
		
		return false;
	}
}


?>
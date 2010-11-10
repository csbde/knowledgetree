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
require_once(KT_LIB_DIR . "/util/sanitize.inc");


class SharedContent extends KTEntity {
    
    public $iUserId; // user id of owner
    public $iObjectId; // object id of document or folder
    public $sType; // object type if folder or document
    public $iPermissions; // permissions on object

    public $_aFieldToSelect = array(
        "iUserId" => "user_id",
        "iObjectId" => "object_id",
        "sType" => "type",
        "iPermissions" => "permissions",
    );
    
    public $_bUsePearError = true;
    
	public function __construct($iUserId, $iObjectId, $sType, $iPermissions) 
    {
		$this->iUserId = $iUserId;
		$this->iObjectId = $iObjectId;
		$this->sType = $sType;
		$this->iPermissions = $iPermissions;
    }
    
    public function create()
    {
    	// Create shared content object
    	parent::create();
    	// Add user permissions for the actual content
    	
    }
    
    public function delete()
    {
		$sql = "DELETE FROM shared_content WHERE user_id = '{$this->iUserId}' AND object_id = '{$this->iObjectId}' AND type = '{$this->sType}' AND permissions = '{$this->iPermissions}' LIMIT 1";
    	$res = DBUtil::runQuery($sql);
    	
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
    public function getUsersObjects($iUserId = null, $aOptions = null) 
    {
        if (is_null($aOptions)) { $aOptions = array(); }
        $sWhereClause = "user_id = '$iUserId'";
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','type');
        return KTEntityUtil::getList2('SharedContent', $sWhereClause, $aOptions);
    }
    
    /**
    * Retrieve a list of SharedContent objects
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
    	$shared_content = 'sc.object_id as id, sc.permissions AS object_permissions, sc.user_id AS user_id';
    	$folders = 'f.name AS title, cu.name as created_by, f.full_path AS full_path';
    	if ($iFolderId == 1)
    	{
	    	$sql = "SELECT $shared_content, $folders
	    			FROM `shared_content` as sc, 
					folders AS f,
					users AS cu
					WHERE (sc.type = 'folder' AND sc.user_id = '$iUserId' AND f.id = sc.object_id AND f.creator_id = cu.id)
					";
    	}
    	else 
    	{
    		// Check if folder has been shared, before retrieving sub folder listing
    		if (!$this->exists($iUserId, $iFolderId, 'folder')) {
    			return false;
    		}
    		
			$sql = "SELECT $shared_content, $folders
	    			FROM `shared_content` as sc, 
					folders AS f,
					users AS cu
					WHERE sc.type = 'folder' AND sc.object_id = f.id AND f.parent_id = '$iFolderId' AND f.creator_id = cu.id";
    	}
    	
    	$results = DBUtil::getResultArray($sql);
    	if (PEAR::isError($results)) {
    		return false;
    	}
    	
    	return $results;
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
		$shared_content = 'sc.object_id as id, sc.permissions AS object_permissions';
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
	    			WHERE sc.type = 'document' AND d.id = sc.object_id AND sc.user_id='$iUserId' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id";
    	}
    	else 
    	{
    		// Check if folder has been shared, before retrieving document listing
    		if (!$this->exists($iUserId, $iFolderId, 'folder')) {
    			return false;
    		}
    		
    		$sql = "SELECT d.id AS id, $shared_content, $document_metadata_version, $document_types_lookup, $document_content_version, $users, $documents, $mime_types 
    				FROM documents AS d, document_metadata_version AS dmv, document_types_lookup AS dtl, document_content_version AS dcv, users AS cu, users AS mu, mime_types AS mt, shared_content sc 
    				WHERE sc.type = 'folder' AND sc.user_id='$iUserId' AND sc.object_id = '$iFolderId' AND d.folder_id = '$iFolderId' AND d.status_id = '1' AND d.metadata_version_id = dmv.id AND dmv.document_type_id = dtl.id AND d.id = dcv.document_id AND d.creator_id = cu.id AND d.modified_user_id = mu.id AND dcv.mime_id = mt.id";
    	}
    	
    	$results = DBUtil::getResultArray($sql);
    	if (PEAR::isError($results)) {
    		return false;
    	}
    	
    	return $results;
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
    	
    	return (count($res) > 0);
    }
    
    /**
     * Check shared user permissions on a document
     *
     * @param int $iUserId
     * @param int $iDocumentId
     * @param int $iFolderId [optional]
     * @return boolean
     */
    static public function canViewDocument($iUserId, $iDocumentId, $iFolderId = null)
    {
    	// TODO : Create one query
    	// Check if document has been shared with user
    	$sql = "SELECT user_id FROM shared_content as sc WHERE (user_id = '{$iUserId}' AND object_id = '{$iDocumentId}' AND type = 'document')";
    	$res = DBUtil::getResultArray($sql);
    	if (count($res) > 0)
    	{
    		return true;
    	}
    	else 
    	{
    		// Check if documents parent folder has been shared with user
    		// If folder id was submitted, use, else query for the parent folder
    		if (empty($iFolderId)) {
    		    $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = (SELECT folder_id FROM documents d WHERE d.id = '{$iDocumentId}') AND sc.type = 'folder')";
    		}
    		else {
    		    $sql = "SELECT user_id FROM shared_content as sc WHERE (user_id = '{$iUserId}' AND object_id = '{$iFolderId}' AND type = 'folder')";
    		}
    		
    		$res = DBUtil::getResultArray($sql);
    	}
    	
    	return (count($res) > 0);
    }
    
    /**
     * Check shared user permissions on a document
     *
     * @param int $iUserId
     * @param int $iDocumentId
     * @param int $iFolderId [optional]
     * @param int $permissions [optional]
     * @return boolean
     */
    static public function canAccessDocument($iUserId, $iDocumentId, $iFolderId = null, $permissions = 0)
    {
    	// TODO : Create one query
    	// Check if document has been shared with user
    	$sql = "SELECT user_id FROM shared_content as sc WHERE (user_id = '{$iUserId}' AND object_id = '{$iDocumentId}' AND type = 'document' AND sc.permissions = $permissions)";
    	$res = DBUtil::getResultArray($sql);
    	if (count($res) > 0)
    	{
    		return true;
    	}
    	else 
    	{
    		// Check if documents parent folder has been shared with user
    		// If folder id was submitted, use, else query for the parent folder
    		if (empty($iFolderId)) {
    		    $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = (SELECT d.folder_id FROM documents d WHERE d.id = '{$iDocumentId}') AND sc.type = 'folder')";
    		}
    		else {
    		    $sql = "SELECT sc.user_id FROM shared_content sc WHERE (sc.user_id = '{$iUserId}' AND sc.object_id = '{$iFolderId}' AND sc.type = 'folder')";
    		}
    		
    		$sql .= " AND sc.permissions = $permissions";
    		
    		$res = DBUtil::getResultArray($sql);
    	}
    	
    	return (count($res) > 0);
    }
    
}


?>
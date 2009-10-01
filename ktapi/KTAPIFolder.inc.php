<?php
/**
 * Folder API for KnowledgeTree
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
*/


require_once(KT_DIR . '/ktwebservice/KTUploadManager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');

/**
 * This class handles folder related operations
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 *
*/
class KTAPI_Folder extends KTAPI_FolderItem
{
	/**
	 * This is a reference to a base Folder object.
	 *
	 * @access private
	 * @var Folder
	 */
	var $folder;

	/**
	 * This is the id of the folder on the database.
	 *
	 * @access private
	 * @var int
	 */
	var $folderid;

	/**
	 * This is used to get a folder based on a folder id.
	 * @author KnowledgeTree Team
	 * @access private
	 * @param KTAPI $ktapi
	 * @param int $folderid
	 * @return KTAPI_Folder
	 */
	function get(&$ktapi, $folderid)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi, 'KTAPI'));
		assert(is_numeric($folderid));

		$folderid += 0;

		$folder = &Folder::get($folderid);
		if (is_null($folder) || PEAR::isError($folder))
		{
			return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID,$folder);
		}

		// A special case. We ignore permission checking on the root folder.
		if ($folderid != 1)
		{
		    $user = $ktapi->can_user_access_object_requiring_permission($folder, KTAPI_PERMISSION_READ);

		    if (is_null($user) || PEAR::isError($user))
		    {
		        $user = $ktapi->can_user_access_object_requiring_permission($folder, KTAPI_PERMISSION_VIEW_FOLDER);
		        if (is_null($user) || PEAR::isError($user))
		        {
		            return $user;
		        }
		    }
		}

		return new KTAPI_Folder($ktapi, $folder);
	}

	/**
	 * Checks if the folder is a shortcut
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return boolean
	 */
	function is_shortcut()
	{
		return $this->folder->isSymbolicLink();
	}

	/**
	 * Retrieves the shortcuts linking to this folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return array
	 */
	function get_shortcuts()
	{
		return $this->folder->getSymbolicLinks();
	}

	/**
	 * This is the constructor for the KTAPI_Folder.
	 *
	 * @author KnowledgeTree Team
	 * @access private
	 * @param KTAPI $ktapi
	 * @param Folder $folder
	 * @return KTAPI_Folder
	 */
	function KTAPI_Folder(&$ktapi, &$folder)
	{
		$this->ktapi = &$ktapi;
		$this->folder = &$folder;
		$this->folderid = $folder->getId();
	}

	/**
	 * This returns a reference to the internal folder object.
	 *
	 * @author KnowledgeTree Team
	 * @access protected
	 * @return Folder
	 */
	function get_folder()
	{
		return $this->folder;
	}

	/**
	 * This returns detailed information on the folder object.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return array
	 */
	function get_detail()
	{
		$this->clearCache();

		$config = KTConfig::getSingleton();
		$wsversion = $config->get('webservice/version', LATEST_WEBSERVICE_VERSION);

		$detail = array(
			'id'=>(int) $this->folderid,
			'folder_name'=>$this->get_folder_name(),
			'parent_id'=>(int) $this->get_parent_folder_id(),
			'full_path'=>$this->get_full_path(),
			'linked_folder_id'=>$this->folder->getLinkedFolderId(),
			'permissions' => KTAPI_Folder::get_permission_string($this->folder),
		);

		if($wsversion<3){
			unset($detail['linked_folder_id']);
		}

        $folder = $this->folder;

        // get the creator
		$userid = $folder->getCreatorID();
		$username='n/a';
		if (is_numeric($userid))
		{
			$username = '* unknown *';
			$user = User::get($userid);
			if (!is_null($user) && !PEAR::isError($user))
			{
				$username = $user->getName();
			}
		}
		$detail['created_by'] = $username;

        // get the creation date
		$detail['created_date'] = $folder->getCreatedDateTime();

        // get the modified user
		$userid = $folder->getModifiedUserId();
		$username='n/a';
		if (is_numeric($userid))
		{
			$username = '* unknown *';
			$user = User::get($userid);
			if (!is_null($user) && !PEAR::isError($user))
			{
				$username = $user->getName();
			}
		}
		$detail['modified_by'] = $detail['updated_by'] = $username;

		// get the modified date
		$detail['updated_date'] = $detail['modified_date'] = $folder->getLastModifiedDate();

		return $detail;
	}
    
	/**
	 * This clears the global object cache of the folder class.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 *
	 */

	function clearCache()
	{
		// TODO: we should only clear the cache for the document we are working on
		// this is a quick fix but not optimal!!

		$GLOBALS["_OBJECTCACHE"]['Folder'] = array();

		$this->folder = &Folder::get($this->folderid);
	}

	/**
	 *
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return unknown
	 */
	function get_parent_folder_id()
	{
		return (int) $this->folder->getParentID();
	}

	/**
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return unknown
	 */
	function get_folder_name()
	{
		return $this->folder->getFolderName($this->folderid);
	}


	/**
	 * This returns the folderid.
	 * @author KnowledgeTree Team
	 * @access public
	 * @return int
	 */
	function get_folderid()
	{
		return (int) $this->folderid;
	}

	/**
	 * This function will return a folder by it's name (not ID)
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi
	 * @param string $foldername
	 * @param int $folderid
	 * @return KTAPI_Folder
	 */
	function _get_folder_by_name($ktapi, $foldername, $folderid)
	{
		$foldername=trim($foldername);
		if (empty($foldername))
		{
			return new PEAR_Error('A valid folder name must be specified.');
		}

		$split = explode('/', $foldername);

		foreach($split as $foldername)
		{
			if (empty($foldername))
			{
				continue;
			}
			$foldername = KTUtil::replaceInvalidCharacters($foldername);
			$foldername = sanitizeForSQL($foldername);
			$sql = "SELECT id FROM folders WHERE
					(name='$foldername' and parent_id=$folderid) OR
					(name='$foldername' and parent_id is null and $folderid=1)";
			$row = DBUtil::getOneResult($sql);
			if (is_null($row) || PEAR::isError($row))
			{
				return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID,$row);
			}
			$folderid = $row['id'];
		}

		return KTAPI_Folder::get($ktapi, $folderid);
	}


	/**
	 * This can resolve a folder relative to the current directy by name
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $foldername
	 * @return KTAPI_Folder
	 */
	function get_folder_by_name($foldername)
	{
		return KTAPI_Folder::_get_folder_by_name($this->ktapi, $foldername, $this->folderid);
	}

	/**
	 * This will return the full path string of the current folder object
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return string
	 */
	function get_full_path()
	{
		$path = $this->folder->getFullPath();
		if (empty($path)) $path = '/';

		return $path;
	}

	/**
	 * This gets a document by filename or name.
	 *
	 * @author KnowledgeTree Team
	 * @access private
	 * @param string $documentname
	 * @param string $function
	 * @return KTAPI_Document
	 */
	function _get_document_by_name($documentname, $function='getByNameAndFolder')
	{
		$documentname=trim($documentname);
		if (empty($documentname))
		{
			return new PEAR_Error('A valid document name must be specified.');
		}

		$foldername = dirname($documentname);
		$documentname = basename($documentname);
		$documentname = KTUtil::replaceInvalidCharacters($documentname);

		$ktapi_folder = $this;

		if (!empty($foldername) && ($foldername != '.'))
		{
			$ktapi_folder = $this->get_folder_by_name($foldername);
		}

		$currentFolderName = $this->get_folder_name();

		if (PEAR::isError($ktapi_folder) && substr($foldername, 0, strlen($currentFolderName)) == $currentFolderName)
		{
			if ($currentFolderName == $foldername)
			{
				$ktapi_folder = $this;
			}
			else
			{
				$foldername = substr($foldername, strlen($currentFolderName)+1);
				$ktapi_folder = $this->get_folder_by_name($foldername);
			}
		}

		if (is_null($ktapi_folder) || PEAR::isError($ktapi_folder))
		{
			return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID, $ktapi_folder);
		}

		//$folder = $ktapi_folder->get_folder();
		$folderid = $ktapi_folder->folderid;

		$document = Document::$function($documentname, $folderid);
		if (is_null($document) || PEAR::isError($document))
		{
			return new KTAPI_Error(KTAPI_ERROR_DOCUMENT_INVALID, $document);
		}

		$user = $this->can_user_access_object_requiring_permission($document, KTAPI_PERMISSION_READ);
		if (PEAR::isError($user))
		{
			return $user;
		}

		return new KTAPI_Document($this->ktapi, $ktapi_folder, $document);
	}

	/**
	 * This can resolve a document relative to the current directy by name.
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $documentname
	 * @return KTAPI_Document
	 */
	function get_document_by_name($documentname)
	{
		return $this->_get_document_by_name($documentname,'getByNameAndFolder');
	}

	/**
	 * This can resolve a document relative to the current directy by filename .
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $documentname
	 * @return KTAPI_Document
	 */
	function get_document_by_filename($documentname)
	{
		return $this->_get_document_by_name($documentname,'getByFilenameAndFolder');
	}

	/**
	 * Gets a User class based on the user id
	 *
	 * @author KnowledgeTree Team
	 * @param int $userid
	 * @return User
	 */
	function _resolve_user($userid)
	{
		$user=null;

		if (!is_null($userid))
		{
			$user=User::get($userid);
			if (is_null($user) || PEAR::isError($user))
			{
				$user=null;
			}
		}
		return $user;
	}

	/**
	 * Get's a permission string for a folder eg: 'RW' or 'RWA'
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param Folder $folder
	 * @return string
	 */
	function get_permission_string($folder)
	{
		$perms = '';
		if (Permission::userHasFolderReadPermission($folder))
		{
			$perms .= 'R';
		}
		if (Permission::userHasFolderWritePermission($folder))
		{
			$perms .= 'W';
		}
		if (Permission::userHasAddFolderPermission($folder))
		{
			$perms .= 'A';
		}

		// root folder cannot be renamed or deleted.
        if ($folder->iId != 1) {
            if (Permission::userHasRenameFolderPermission($folder))
            {
                $perms .= 'N';
            }
            if (Permission::userHasDeleteFolderPermission($folder))
            {
                $perms .= 'D';
            }
        }
		return $perms;
	}

	/**
	 * Get's a folder listing, recursing to the given depth
	 *
	 * <code>
	 * $root = $this->ktapi->get_root_folder();
	 * $listing = $root->get_listing();
	 * foreach($listing as $val) {
	 * 	if($val['item_type'] == 'F') {
	 *   // It's a folder
	 *   echo $val['title'];
	 *  }
	 * }
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $depth
	 * @param string $what
	 * @return array
	 */
	function get_listing($depth=1, $what='DFS')
	{
		if ($depth < 1)
		{
			return array();
		}

		$what = strtoupper($what);
		$read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
		$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);

		$config = KTConfig::getSingleton();

		$wsversion = $config->get('webservice/version', LATEST_WEBSERVICE_VERSION);

		$user = $this->ktapi->get_user();

		$contents = array();

		if (strpos($what,'F') !== false)
		{

			$folder_children = Folder::getList(array('parent_id = ?', $this->folderid));

			foreach ($folder_children as $folder)
			{
				if(KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $folder) ||
				    KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $folder))
				{
					if ($depth-1 > 0)
					{
						$sub_folder = &$this->ktapi->get_folder_by_id($folder->getId());
						$items = $sub_folder->get_listing($depth-1, $what);
					}
					else
					{
						$items=array();
					}

					$creator=$this->_resolve_user($folder->getCreatorID());


					if ($wsversion >= 2)
					{
						$array =  array(
							'id' => (int) $folder->getId(),
							'item_type' => 'F',

							'custom_document_no'=>'n/a',
							'oem_document_no'=>'n/a',

							'title' => $folder->getName(),
							'document_type' => 'n/a',
							'filename' => $folder->getName(),
							'filesize' => 'n/a',

							'created_by' => is_null($creator)?'n/a':$creator->getName(),
							'created_date' => 'n/a',

							'checked_out_by' => 'n/a',
							'checked_out_date' => 'n/a',

							'modified_by' => 'n/a',
							'modified_date' => 'n/a',

							'owned_by' => 'n/a',

							'version' => 'n/a',

							'is_immutable'=> 'n/a',
							'permissions' => KTAPI_Folder::get_permission_string($folder),

							'workflow'=>'n/a',
							'workflow_state'=>'n/a',

							'mime_type' => 'folder',
							'mime_icon_path' => 'folder',
							'mime_display' => 'Folder',

							'storage_path' => 'n/a',


					);

						if($wsversion>=3){
							$array['linked_folder_id'] = $folder->getLinkedFolderId();
							if($folder->isSymbolicLink()){
								$array['item_type'] = "S";
							}
						}
						$array['items']=$items;
						if($wsversion<3 || (strpos($what,'F') !== false && !$folder->isSymbolicLink()) || ($folder->isSymbolicLink() && strpos($what,'S') !== false)){
							$contents[] = $array;
						}
					}
					else
					{

					$contents[] = array(
						'id' => (int) $folder->getId(),
						'item_type'=>'F',
						'title'=>$folder->getName(),
						'creator'=>is_null($creator)?'n/a':$creator->getName(),
						'checkedoutby'=>'n/a',
						'modifiedby'=>'n/a',
						'filename'=>$folder->getName(),
						'size'=>'n/a',
						'major_version'=>'n/a',
						'minor_version'=>'n/a',
						'storage_path'=>'n/a',
						'mime_type'=>'folder',
						'mime_icon_path'=>'folder',
						'mime_display'=>'Folder',
						'items'=>$items,
						'workflow'=>'n/a',
						'workflow_state'=>'n/a'
					);
					}

				}
			}

		}

		if (strpos($what,'D') !== false)
		{
			$document_children = Document::getList(array('folder_id = ? AND status_id = 1',  $this->folderid));

			// I hate that KT doesn't cache things nicely...
			$mime_cache=array();

			foreach ($document_children as $document)
			{
				if (KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $document))
				{
					$created_by=$this->_resolve_user($document->getCreatorID());
					$created_date = $document->getCreatedDateTime();
					if (empty($created_date)) $created_date = 'n/a';

					$checked_out_by=$this->_resolve_user($document->getCheckedOutUserID());
					$checked_out_date = $document->getCheckedOutDate();
					if (empty($checked_out_date)) $checked_out_date = 'n/a';

					$modified_by=$this->_resolve_user($document->getCreatorID());
					$modified_date = $document->getLastModifiedDate();
					if (empty($modified_date)) $modified_date = 'n/a';

					$owned_by =$this->_resolve_user($document->getOwnerID());

					$mimetypeid=$document->getMimeTypeID();
					if (!array_key_exists($mimetypeid, $mime_cache))
					{

						$type=KTMime::getMimeTypeName($mimetypeid);
						$icon=KTMime::getIconPath($mimetypeid);
						$display=KTMime::getFriendlyNameForString($type);
						$mime_cache[$mimetypeid] = array(
							'type'=>$type,
							'icon'=>$icon,
							'display'=>$display

						);
					}
					$mimeinfo=$mime_cache[$mimetypeid];

					$workflow='n/a';
					$state='n/a';

					$wf = KTWorkflowUtil::getWorkflowForDocument($document);

					if (!is_null($wf) && !PEAR::isError($wf))
					{
						$workflow=$wf->getHumanName();

						$ws=KTWorkflowUtil::getWorkflowStateForDocument($document);
						if (!is_null($ws) && !PEAR::isError($ws))
						{
							$state=$ws->getHumanName();
						}
					}

					if ($wsversion >= 2)
					{
						$docTypeId = $document->getDocumentTypeID();
						$documentType = DocumentType::get($docTypeId);

						$oemDocumentNo = $document->getOemNo();
						if (empty($oemDocumentNo)) $oemDocumentNo = 'n/a';


						$array = array(
							'id' => (int) $document->getId(),
							'item_type' => 'D',

							'custom_document_no'=>'n/a',
							'oem_document_no'=>$oemDocumentNo,

							'title' => $document->getName(),
							'document_type'=>$documentType->getName(),
							'filename' => $document->getFileName(),
							'filesize' => $document->getFileSize(),

							'created_by' => is_null($created_by)?'n/a':$created_by->getName(),
							'created_date' => $created_date,

							'checked_out_by' => is_null($checked_out_by)?'n/a':$checked_out_by->getName(),
							'checked_out_date' => $checked_out_date,

							'modified_by' => is_null($modified_by)?'n/a':$modified_by->getName(),
							'modified_date' => $modified_date,

							'owned_by' => is_null($owned_by)?'n/a':$owned_by->getName(),

							'version' =>  $document->getMajorVersionNumber() . '.' . $document->getMinorVersionNumber(),
                            'content_id' => $document->getContentVersionId(),

							'is_immutable'=> $document->getImmutable()?'true':'false',
							'permissions' => KTAPI_Document::get_permission_string($document),

							'workflow'=> $workflow,
							'workflow_state'=> $state,

							'mime_type' => $mime_cache[$mimetypeid]['type'],
							'mime_icon_path' => $mime_cache[$mimetypeid]['icon'],
							'mime_display' => $mime_cache[$mimetypeid]['display'],

							'storage_path' => $document->getStoragePath(),
					);
							if($wsversion>=3){
								$document->switchToRealCore();
								$array['linked_document_id'] = $document->getLinkedDocumentId();
								$document->switchToLinkedCore();
								if($document->isSymbolicLink()){
									$array['item_type'] = "S";
								}
							}

							$array['items']=array();


							if($wsversion<3 || (strpos($what,'D') !== false && !$document->isSymbolicLink()) || ($document->isSymbolicLink() && strpos($what,'S') !== false)){
								$contents[] = $array;
							}
					}
					else
					{


					$contents[] = array(
						'id' => (int) $document->getId(),
						'item_type'=>'D',
						'title'=>$document->getName(),
						'creator'=>is_null($created_by)?'n/a':$created_by->getName(),
						'checkedoutby'=>is_null($checked_out_by)?'n/a':$checked_out_by->getName(),
						'modifiedby'=>is_null($modified_by)?'n/a':$modified_by->getName(),
						'filename'=>$document->getFileName(),
						'size'=>$document->getFileSize(),
						'major_version'=>$document->getMajorVersionNumber(),
						'minor_version'=>$document->getMinorVersionNumber(),
						'storage_path'=>$document->getStoragePath(),
						'mime_type'=>$mime_cache[$mimetypeid]['type'],
						'mime_icon_path'=>$mime_cache[$mimetypeid]['icon'],
						'mime_display'=>$mime_cache[$mimetypeid]['display'],
						'items'=>array(),
						'workflow'=>$workflow,
						'workflow_state'=>$state
					);

					}
				}
			}
		}

		return $contents;
	}
    
    /**
	 * Get's a folder listing, recursing to the maximum depth.
	 * Derived from the get_listing function.
	 *
	 * <code>
	 * $root = $this->ktapi->get_root_folder();
	 * $listing = $root->get_full_listing();
	 * foreach($listing as $val) {
	 * 	if($val['item_type'] == 'F') {
	 *   // It's a folder
	 *   echo $val['title'];
	 *  }
	 * }
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $what
	 * @return array
	 */
	function get_full_listing($what='DFS')
	{
		$what = strtoupper($what);
		$read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
		$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);

		$config = KTConfig::getSingleton();

		$wsversion = $config->get('webservice/version', LATEST_WEBSERVICE_VERSION);

		$user = $this->ktapi->get_user();

		$contents = array();

		if (strpos($what,'F') !== false)
		{

			$folder_children = Folder::getList(array('parent_id = ?', $this->folderid));

			foreach ($folder_children as $folder)
			{
				if(KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $folder) ||
				   KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $folder))
				{
					$sub_folder = &$this->ktapi->get_folder_by_id($folder->getId());
					if (!PEAR::isError($sub_folder))
					{
						$items = $sub_folder->get_full_listing($what);
					}
					else
					{
						$items = array();
					}

					$creator = $this->_resolve_user($folder->getCreatorID());


					if ($wsversion >= 2)
					{
						$array =  array(
							'id' => (int) $folder->getId(),
							'item_type' => 'F',

							'custom_document_no'=>'n/a',
							'oem_document_no'=>'n/a',

							'title' => $folder->getName(),
							'document_type' => 'n/a',
							'filename' => $folder->getName(),
							'filesize' => 'n/a',

							'created_by' => is_null($creator)?'n/a':$creator->getName(),
							'created_date' => 'n/a',

							'checked_out_by' => 'n/a',
							'checked_out_date' => 'n/a',

							'modified_by' => 'n/a',
							'modified_date' => 'n/a',

							'owned_by' => 'n/a',

							'version' => 'n/a',

							'is_immutable'=> 'n/a',
							'permissions' => KTAPI_Folder::get_permission_string($folder),

							'workflow'=>'n/a',
							'workflow_state'=>'n/a',

							'mime_type' => 'folder',
							'mime_icon_path' => 'folder',
							'mime_display' => 'Folder',

							'storage_path' => 'n/a',
					    );

						if($wsversion>=3)
                        {
							$array['linked_folder_id'] = $folder->getLinkedFolderId();
							if($folder->isSymbolicLink()) {
								$array['item_type'] = "S";
							}
						}
                        
						$array['items']=$items;
						if($wsversion<3 || (strpos($what,'F') !== false && !$folder->isSymbolicLink()) || 
                           ($folder->isSymbolicLink() && strpos($what,'S') !== false)) {
							$contents[] = $array;
						}
					}
					else
					{
    					$contents[] = array(
    						'id' => (int) $folder->getId(),
    						'item_type'=>'F',
    						'title'=>$folder->getName(),
    						'creator'=>is_null($creator)?'n/a':$creator->getName(),
    						'checkedoutby'=>'n/a',
    						'modifiedby'=>'n/a',
    						'filename'=>$folder->getName(),
    						'size'=>'n/a',
    						'major_version'=>'n/a',
    						'minor_version'=>'n/a',
    						'storage_path'=>'n/a',
    						'mime_type'=>'folder',
    						'mime_icon_path'=>'folder',
    						'mime_display'=>'Folder',
    						'items'=>$items,
    						'workflow'=>'n/a',
    						'workflow_state'=>'n/a'
    					);
					}

				}
			}

		}

		if (strpos($what,'D') !== false)
		{
			$document_children = Document::getList(array('folder_id = ? AND status_id = 1',  $this->folderid));

			// I hate that KT doesn't cache things nicely...
			$mime_cache = array();

			foreach ($document_children as $document)
			{
				if (KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $document))
				{
					$created_by=$this->_resolve_user($document->getCreatorID());
					$created_date = $document->getCreatedDateTime();
					if (empty($created_date)) $created_date = 'n/a';

					$checked_out_by=$this->_resolve_user($document->getCheckedOutUserID());
					$checked_out_date = $document->getCheckedOutDate();
					if (empty($checked_out_date)) $checked_out_date = 'n/a';

					$modified_by=$this->_resolve_user($document->getCreatorID());
					$modified_date = $document->getLastModifiedDate();
					if (empty($modified_date)) $modified_date = 'n/a';

					$owned_by =$this->_resolve_user($document->getOwnerID());

					$mimetypeid=$document->getMimeTypeID();
					if (!array_key_exists($mimetypeid, $mime_cache))
					{

						$type=KTMime::getMimeTypeName($mimetypeid);
						$icon=KTMime::getIconPath($mimetypeid);
						$display=KTMime::getFriendlyNameForString($type);
						$mime_cache[$mimetypeid] = array(
							'type'=>$type,
							'icon'=>$icon,
							'display'=>$display

						);
					}
					$mimeinfo=$mime_cache[$mimetypeid];

					$workflow='n/a';
					$state='n/a';

					$wf = KTWorkflowUtil::getWorkflowForDocument($document);

					if (!is_null($wf) && !PEAR::isError($wf))
					{
						$workflow=$wf->getHumanName();

						$ws=KTWorkflowUtil::getWorkflowStateForDocument($document);
						if (!is_null($ws) && !PEAR::isError($ws))
						{
							$state=$ws->getHumanName();
						}
					}

					if ($wsversion >= 2)
					{
						$docTypeId = $document->getDocumentTypeID();
						$documentType = DocumentType::get($docTypeId);

						$oemDocumentNo = $document->getOemNo();
						if (empty($oemDocumentNo)) $oemDocumentNo = 'n/a';


						$array = array(
							'id' => (int) $document->getId(),
							'item_type' => 'D',

							'custom_document_no'=>'n/a',
							'oem_document_no'=>$oemDocumentNo,

							'title' => $document->getName(),
							'document_type'=>$documentType->getName(),
							'filename' => $document->getFileName(),
							'filesize' => $document->getFileSize(),

							'created_by' => is_null($created_by)?'n/a':$created_by->getName(),
							'created_date' => $created_date,

							'checked_out_by' => is_null($checked_out_by)?'n/a':$checked_out_by->getName(),
							'checked_out_date' => $checked_out_date,

							'modified_by' => is_null($modified_by)?'n/a':$modified_by->getName(),
							'modified_date' => $modified_date,

							'owned_by' => is_null($owned_by)?'n/a':$owned_by->getName(),

							'version' =>  $document->getMajorVersionNumber() . '.' . $document->getMinorVersionNumber(),
                            'content_id' => $document->getContentVersionId(),

							'is_immutable'=> $document->getImmutable()?'true':'false',
							'permissions' => KTAPI_Document::get_permission_string($document),

							'workflow'=> $workflow,
							'workflow_state'=> $state,

							'mime_type' => $mime_cache[$mimetypeid]['type'],
							'mime_icon_path' => $mime_cache[$mimetypeid]['icon'],
							'mime_display' => $mime_cache[$mimetypeid]['display'],

							'storage_path' => $document->getStoragePath(),
					    );
						if($wsversion>=3){
							$document->switchToRealCore();
							$array['linked_document_id'] = $document->getLinkedDocumentId();
							$document->switchToLinkedCore();
							if($document->isSymbolicLink()){
								$array['item_type'] = "S";
							}
						}

						$array['items']=array();


						if($wsversion<3 || (strpos($what,'D') !== false && !$document->isSymbolicLink()) || ($document->isSymbolicLink() && strpos($what,'S') !== false)){
							$contents[] = $array;
						}
					}
					else
					{
    					$contents[] = array(
    						'id' => (int) $document->getId(),
    						'item_type'=>'D',
    						'title'=>$document->getName(),
    						'creator'=>is_null($created_by)?'n/a':$created_by->getName(),
    						'checkedoutby'=>is_null($checked_out_by)?'n/a':$checked_out_by->getName(),
    						'modifiedby'=>is_null($modified_by)?'n/a':$modified_by->getName(),
    						'filename'=>$document->getFileName(),
    						'size'=>$document->getFileSize(),
    						'major_version'=>$document->getMajorVersionNumber(),
    						'minor_version'=>$document->getMinorVersionNumber(),
    						'storage_path'=>$document->getStoragePath(),
    						'mime_type'=>$mime_cache[$mimetypeid]['type'],
    						'mime_icon_path'=>$mime_cache[$mimetypeid]['icon'],
    						'mime_display'=>$mime_cache[$mimetypeid]['display'],
    						'items'=>array(),
    						'workflow'=>$workflow,
    						'workflow_state'=>$state
    					);
					}
				}
			}
		}

		return $contents;
	}

	/**
	 * This adds a shortcut to an existing document to the current folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id The ID of the document to create a shortcut to
	 * @return KTAPI_Document
	 *
	 */
	function add_document_shortcut($document_id){
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_WRITE);
		if (PEAR::isError($user))
		{
			return $user;
		}
		$oDocument = Document::get($document_id);
		if(PEAR::isError($oDocument)){
			return $oDocument;
		}

		$user = $this->can_user_access_object_requiring_permission($oDocument, KTAPI_PERMISSION_READ);
		if (PEAR::isError($user))
		{
			return $user;
		}
		$document = KTDocumentUtil::createSymbolicLink($document_id,$this->folder,$user);
		if (PEAR::isError($document))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $document->getMessage());
		}

		return new KTAPI_Document($this->ktapi,$this,$document);
	}

	/**
	 * This adds a shortcut pointing to an existing folder to the current folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $folder_id The ID of the folder to create a shortcut to
	 * @return KTAPI_Folder
	 */
	function add_folder_shortcut($folder_id){
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_WRITE);
		if (PEAR::isError($user))
		{
			return $user;
		}
		$oFolder = Folder::get($folder_id);
		if(PEAR::isError($oFolder)){
			return $oFolder;
		}

		$user = $this->can_user_access_object_requiring_permission($oFolder, KTAPI_PERMISSION_READ);
		if (PEAR::isError($user))
		{
			return $user;
		}
		$folder = & KTFolderUtil::createSymbolicLink($folder_id,$this->folder,$user);
		if (PEAR::isError($folder))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $folder->getMessage());
		}

		return new KTAPI_Folder($this->ktapi,$folder);
	}

	/**
	 * This adds a document to the current folder.
	 *
	 * <code>
	 * $kt = new KTAPI();
	 * $kt->start_session("admin", "admin");
	 * $folder = $kt->get_folder_by_name("My New folder");
	 * $res = $folder->add_document("Test Document", "test.txt", "Default", $tmpfname);
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $title This is the title for the file in the repository.
	 * @param string $filename This is the filename in the system for the file.
	 * @param string $documenttype This is the name or id of the document type. It first looks by name, then by id.
	 * @param string $tempfilename This is a reference to the file that is accessible locally on the file system.
	 * @return KTAPI_Document
	 */
	function add_document($title, $filename, $documenttype, $tempfilename)
	{
		if (!is_file($tempfilename))
		{
			return new PEAR_Error('File does not exist.');
		}

		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_WRITE);
		if (PEAR::isError($user))
		{
			return $user;
		}

		//KTS-4016: removed the replacing of special characters from the title as they should be allowed there
		//$title = KTUtil::replaceInvalidCharacters($title);
		$filename = basename($filename);
		$filename = KTUtil::replaceInvalidCharacters($filename);
		$documenttypeid = KTAPI::get_documenttypeid($documenttype);
		if (PEAR::isError($documenttypeid))
		{
			$config = KTCache::getSingleton();
			$defaultToDefaultDocType = $config->get('webservice/useDefaultDocumentTypeIfInvalid',true);
			if ($defaultToDefaultDocType)
			{
				$documenttypeid = KTAPI::get_documenttypeid('Default');
			}
			else
			{
		    	return new KTAPI_DocumentTypeError('The document type could not be resolved or is disabled: ' . $documenttype);
			}
		}


		$options = array(
			'contents' => new KTFSFileLike($tempfilename),
			'temp_file' => $tempfilename,
			'novalidate' => true,
			'documenttype' => DocumentType::get($documenttypeid),
			'description' => $title,
			'metadata'=>array(),
			'cleanup_initial_file' => true
		);

		DBUtil::startTransaction();
		$document =& KTDocumentUtil::add($this->folder, $filename, $user, $options);

		if (PEAR::isError($document))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $document->getMessage());
		}
		DBUtil::commit();

		KTUploadManager::temporary_file_imported($tempfilename);

		return new KTAPI_Document($this->ktapi, $this, $document);
	}

	/**
	 * This adds a subfolder folder to the current folder.
	 *
	 * <code>
	 * <?php
	 * $kt = new KTAPI();
	 * $kt->start_session("admin", "admin");
	 * $root = $kt->get_root_folder();
	 * $root->add_folder("My New folder");
	 * ?>
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $foldername
	 * @return KTAPI_Folder
	 */
	function add_folder($foldername)
	{
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_ADD_FOLDER);

		if (PEAR::isError($user))
		{
			return $user;
		}
		$foldername = KTUtil::replaceInvalidCharacters($foldername);

		DBUtil::startTransaction();
		$result = KTFolderUtil::add($this->folder, $foldername, $user);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}
		DBUtil::commit();
		$folderid = $result->getId();

		return $this->ktapi->get_folder_by_id($folderid);
	}

	/**
	 * This deletes the current folder.
	 *
	 * <code>
	 * $kt = new KTAPI();
	 * $kt->start_session("admin", "admin");
	 * $folder = $kt->get_folder_by_name("My New folder");
	 * $folder->delete("It was getting old!");
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $reason
	 */
	function delete($reason)
	{
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_DELETE);
		if (PEAR::isError($user))
		{
			return $user;
		}

		if ($this->folderid == 1)
		{
			return new PEAR_Error('Cannot delete root folder!');
		}

		DBUtil::startTransaction();
		$result = KTFolderUtil::delete($this->folder, $user, $reason);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}
		DBUtil::commit();
	}

	/**
	 * This renames the folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $newname
	 */
	function rename($newname)
	{
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_RENAME_FOLDER);
		if (PEAR::isError($user))
		{
			return $user;
		}
		$newname = KTUtil::replaceInvalidCharacters($newname);

		DBUtil::startTransaction();
		$result = KTFolderUtil::rename($this->folder, $newname, $user);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}
		DBUtil::commit();
	}

	/**
	 * This moves the folder to another location.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 */
	function move($ktapi_target_folder, $reason='')
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));

		$user = $this->ktapi->get_user();

		$target_folder = $ktapi_target_folder->get_folder();

		$result = $this->can_user_access_object_requiring_permission($target_folder, KTAPI_PERMISSION_WRITE);
		if (PEAR::isError($result))
		{
			return $result;
		}

		DBUtil::startTransaction();
		$result = KTFolderUtil::move($this->folder, $target_folder, $user, $reason);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}

        // regenerate internal folder object
        $res = $this->updateObject();

        if (PEAR::isError($res))
        {
            DBUtil::rollback();
            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
        }
		DBUtil::commit();
	}

	/**
	 * This copies a folder to another location.
	 *
	 * <code>
	 * $root = $this->ktapi->get_root_folder();
	 * $folder = $root->add_folder("Test folder");
	 * $new_folder = $root->add_folder("New test folder");
	 * $res = $folder->copy($new_folder, "Test copy");
	 * </code>
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 */
	function copy($ktapi_target_folder, $reason='')
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));

		$user = $this->ktapi->get_user();

		$target_folder = $ktapi_target_folder->get_folder();

		$result =$this->can_user_access_object_requiring_permission($target_folder, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}

		DBUtil::startTransaction();
		$result = KTFolderUtil::copy($this->folder, $target_folder, $user, $reason);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}
		DBUtil::commit();
	}

	/**
	 * This returns all permissions linked to the folder.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return array
	 */
	function get_permissions()
	{
		return new PEAR_Error('TODO');
	}


	/**
	 * This returns the transaction history for the document.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return array The list of transactions | a PEAR_Error on failure
	 */
	function get_transaction_history()
	{
        $sQuery = 'SELECT DTT.name AS transaction_name, U.name AS username, DT.comment AS comment, DT.datetime AS datetime ' .
            'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('users') . ' AS U ON DT.user_id = U.id ' .
            'INNER JOIN ' . KTUtil::getTableName('transaction_types') . ' AS DTT ON DTT.namespace = DT.transaction_namespace ' .
            'WHERE DT.folder_id = ? ORDER BY DT.datetime DESC';
        $aParams = array($this->folderid);

        $transactions = DBUtil::getResultArray(array($sQuery, $aParams));
        if (is_null($transactions) || PEAR::isError($transactions))
        {
        	return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $transactions  );
        }

        $config = KTConfig::getSingleton();
		$wsversion = $config->get('webservice/version', LATEST_WEBSERVICE_VERSION);
		foreach($transactions as $key=>$transaction)
		{
			$transactions[$key]['version'] = (float) $transaction['version'];
		}

        return $transactions;
	}

	/**
	 * Gets the KTAPI_Folder object of this instance
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return KTAPI_Folder
	 */
	public function getObject()
	{
	    return $this->folder;
	}

    /**
     * Updates the Folder object
     */
    private function updateObject()
    {
        $folder = &Folder::get($this->folderid);
        if (is_null($folder) || PEAR::isError($folder))
        {
            return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID, $folder);
        }

        $this->folder = $folder;
    }

	/**
	 * Get the role allocation for the folder
	 *
	 * @return KTAPI_RoleAllocation Instance of the role allocation object
	 */
	public function getRoleAllocation()
	{
	    $allocation = KTAPI_RoleAllocation::getAllocation($this->ktapi, $this);

	    return $allocation;
	}

	/**
	 * Get the permission allocation for the folder
	 *
	 * @return KTAPI_PermissionAllocation Instance of the permission allocation object
	 */
	public function getPermissionAllocation()
	{
	    $allocation = KTAPI_PermissionAllocation::getAllocation($this->ktapi, $this);

	    return $allocation;
	}

	/**
	 * Determines whether the currently logged on user is subscribed
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return boolean
	 */
	public function isSubscribed()
	{
        $subscriptionType = SubscriptionEvent::subTypes('Folder');
        $user = $this->ktapi->get_user();
        $folder = $this->folder;

        $result = Subscription::exists($user->getId(), $folder->getId(), $subscriptionType);
        return $result;
	}

	/**
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 *
	 */
	public function unsubscribe()
	{
        if (!$this->isSubscribed())
        {
            return TRUE;
        }

        $subscriptionType = SubscriptionEvent::subTypes('Folder');
        $user = $this->ktapi->get_user();
        $folder = $this->folder;

        $subscription = & Subscription::getByIDs($user->getId(), $folder->getId(), $subscriptionType);
        $result = $subscription->delete();

        if(PEAR::isError($result)){
            return $result->getMessage();
        }
        if($result){
            return $result;
        }

        return $_SESSION['errorMessage'];
	}

	/**
	 * Subscribes the currently logged in KTAPI user to the folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 *
	 */
	public function subscribe()
	{
        if ($this->isSubscribed())
        {
            return TRUE;
        }
        $subscriptionType = SubscriptionEvent::subTypes('Folder');
        $user = $this->ktapi->get_user();
        $folder = $this->folder;

        $subscription = new Subscription($user->getId(), $folder->getId(), $subscriptionType);
        $result = $subscription->create();

        if(PEAR::isError($result)){
            return $result->getMessage();
        }
        if($result){
            return $result;
        }

        return $_SESSION['errorMessage'];
	}
}

?>

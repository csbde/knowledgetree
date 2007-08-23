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
	 *
	 * @access private
	 * @param KTAPI $ktapi
	 * @param int $folderid
	 * @return KTAPI_Folder
	 */
	function &get(&$ktapi, $folderid)
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

		$user = $ktapi->can_user_access_object_requiring_permission($folder, KTAPI_PERMISSION_READ);

		if (is_null($user) || PEAR::isError($user))
		{
			return $user;
		}

		return new KTAPI_Folder($ktapi, $folder);
	}

	/**
	 * This is the constructor for the KTAPI_Folder.
	 *
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
	 * @access protected
	 * @return Folder
	 */
	function &get_folder()
	{
		return $this->folder;
	}


	/**
	 * This returns detailed information on the document.
	 *
	 * @return array
	 */
	function get_detail()
	{
		$detail = array(
			'id'=>(int) $this->folderid,
			'folder_name'=>$this->get_folder_name(),
			'parent_id'=>(int) $this->get_parent_folder_id(),
			'full_path'=>$this->get_full_path(),
		);

		return $detail;
	}

	function get_parent_folder_id()
	{
		return (int) $this->folder->getParentID();
	}

	function get_folder_name()
	{
		return $this->folder->getFolderName($this->folderid);
	}


	/**
	 * This returns the folderid.
	 *
	 * @return int
	 */
	function get_folderid()
	{
		return (int) $this->folderid;
	}

	/**
	 * This can resolve a folder relative to the current directy by name
	 *
	 * @access public
	 * @param string $foldername
	 * @return KTAPI_Folder
	 */
	function &get_folder_by_name($foldername)
	{
		$foldername=trim($foldername);
		if (empty($foldername))
		{
			return new PEAR_Error('A valid folder name must be specified.');
		}

		$split = explode('/', $foldername);

		$folderid=$this->folderid;
		foreach($split as $foldername)
		{
			if (empty($foldername))
			{
				continue;
			}
			$sql = "SELECT id FROM folders WHERE name='$foldername' and parent_id=$folderid";
			$row = DBUtil::getOneResult($sql);
			if (is_null($row) || PEAR::isError($row))
			{
				return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID,$row);
			}
			$folderid = $row['id'];
		}

		return KTAPI_Folder::get($this->ktapi, $folderid);
	}

	function get_full_path()
	{
		$path = $this->folder->getFullPath() . '/' . $this->folder->getName();

		return $path;
	}

	/**
	 * This gets a document by filename or name.
	 *
	 * @access private
	 * @param string $documentname
	 * @param string $function
	 * @return KTAPI_Document
	 */
	function &_get_document_by_name($documentname, $function='getByNameAndFolder')
	{
		$documentname=trim($documentname);
		if (empty($documentname))
		{
			return new PEAR_Error('A valid document name must be specified.');
		}

		$foldername = dirname($documentname);
		$documentname = basename($documentname);

		$ktapi_folder = $this;

		if (!empty($foldername) && ($foldername != '.'))
		{
			$ktapi_folder = $this->get_folder_by_name($foldername);
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
	 *
	 * @access public
	 * @param string $documentname
	 * @return KTAPI_Document
	 */
	function &get_document_by_name($documentname)
	{
		return $this->_get_document_by_name($documentname,'getByNameAndFolder');
	}

	/**
	 * This can resolve a document relative to the current directy by filename .
	 *
	 * @access public
	 * @param string $documentname
	 * @return KTAPI_Document
	 */
	function &get_document_by_filename($documentname)
	{
		return $this->_get_document_by_name($documentname,'getByFilenameAndFolder');
	}

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


	function get_listing($depth=1, $what='DF')
	{
		if ($depth < 1)
		{
			return array();
		}

		$what = strtoupper($what);
		$read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
		$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);


		$user = $this->ktapi->get_user();

		$contents = array();

		if (strpos($what,'F') !== false)
		{
			$folder_children = Folder::getList(array('parent_id = ?', $this->folderid));


			foreach ($folder_children as $folder)
			{
				if(KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $folder))
				{
					$creator=$this->_resolve_user($folder->getCreatorID());

					if ($depth-1 > 0)
					{
						$sub_folder = &$this->ktapi->get_folder_by_id($folder->getId());
						$items = $sub_folder->get_listing($depth-1);
					}
					else
					{
						$items=array();
					}


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
		if (strpos($what,'D') !== false)
		{
			$document_children = Document::getList(array('folder_id = ? AND status_id = 1',  $this->folderid));

			// I hate that KT doesn't cache things nicely...
			$mime_cache=array();

			foreach ($document_children as $document)
			{
				if (KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $document))
				{
					$creator=$this->_resolve_user($document->getCreatorID());
					$checkedoutby=$this->_resolve_user($document->getCheckedOutUserID());
					$modifiedby=$this->_resolve_user($document->getCreatorID());

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

					$workflow = KTWorkflowUtil::getWorkflowForDocument($document);

					if (!is_null($workflow) && !PEAR::isError($workflow))
					{
						$workflow=$workflow->getHumanName();

						$state=KTWorkflowUtil::getWorkflowStateForDocument($document);
						if (!is_null($state) && !PEAR::isError($state))
						{
							$state=$state->getHumanName();
						}
						else
						{
							$state='n/a';
						}
					}
					else
					{
						$workflow='n/a';
						$state='n/a';
					}


					$contents[] = array(
						'id' => (int) $document->getId(),
						'item_type'=>'D',
						'title'=>$document->getName(),
						'creator'=>is_null($creator)?'n/a':$creator->getName(),
						'checkedoutby'=>is_null($checkedoutby)?'n/a':$checkedoutby->getName(),
						'modifiedby'=>is_null($modifiedby)?'n/a':$modifiedby->getName(),
						'filename'=>$document->getName(),
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

		return $contents;
	}

	/**
	 * This adds a document to the current folder.
	 *
	 * @access public
	 * @param string $title This is the title for the file in the repository.
	 * @param string $filename This is the filename in the system for the file.
	 * @param string $documenttype This is the name or id of the document type. It first looks by name, then by id.
	 * @param string $tempfilename This is a reference to the file that is accessible locally on the file system.
	 * @return KTAPI_Document
	 */
	function &add_document($title, $filename, $documenttype, $tempfilename)
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

		$filename = basename($filename);
		$documenttypeid = KTAPI::get_documenttypeid($documenttype);
		if (PEAR::isError($documenttypeid))
		{
		    return new PEAR_Error('The document type could not be resolved or is disabled: ' . $documenttype);
		}


		$options = array(
			'contents' => new KTFSFileLike($tempfilename),
			'novalidate' => true,
			'documenttype' => DocumentType::get($documenttypeid),
			'description' => $title,
			'metadata'=>array(),
			'cleanup_initial_file' => true
		);

		DBUtil::startTransaction();
		$document =& KTDocumentUtil::add($this->folder, $filename, $user, $options);

		if (!is_a($document,'Document'))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $document->getMessage());
		}
		DBUtil::commit();

		$tempfilename=addslashes($tempfilename);
		$sql = "DELETE FROM uploaded_files WHERE tempfilename='$tempfilename'";
		$result = DBUtil::runQuery($sql);
		if (PEAR::isError($result))
		{
			return $result;
		}

		return new KTAPI_Document($this->ktapi, $this, $document);
	}

	/**
	 * This adds a subfolder folder to the current folder.
	 *
	 * @access public
	 * @param string $foldername
	 * @return KTAPI_Folder
	 */
	function &add_folder($foldername)
	{
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_ADD_FOLDER);

		if (PEAR::isError($user))
		{
			return $user;
		}

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
	 * @param string $newname
	 */
	function rename($newname)
	{
		$user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_RENAME_FOLDER);
		if (PEAR::isError($user))
		{
			return $user;
		}

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
		$result = KTFolderUtil::copy($this->folder, $target_folder, $user, $reason);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
		}
		DBUtil::commit();
	}

	/**
	 * This copies a folder to another location.
	 *
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
	 * @access public
	 * @return array
	 */
	function get_permissions()
	{
		return new PEAR_Error('TODO');
	}

	/**
	 * This returns a transaction history listing.
	 *
	 * @access public
	 * @return array
	 */
	function get_transaction_history()
	{
		return new PEAR_Error('TODO');
	}
}

?>

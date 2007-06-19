<?


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
	
	function get_listing($depth=1, $what='DF')
	{
		if ($depth < 1) 
		{
			return array();
		}
		$permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
		$permissionid= $permission->getId();
		
		$user = $this->ktapi->get_user();
		$descriptors=KTPermissionUtil::getPermissionDescriptorsForUser($user);
		if (is_null($descriptors) || PEAR::isError($descriptors))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR . ': problem with descriptors for user', $descriptors);
		}
		if (count($descriptors == 0))
		{
			$descriptors=array(0);
		}
		
		$aPermissionDescriptors = implode(',',$descriptors);
		
		$sql = '';
		if (strpos($what,'D') !== false)
		{	
		$sql .= "SELECT 
					d.id, 
					'D' as item_type,
					dmv.name as title,
					ifnull(uc.name, 'n/a') AS creator, 
					ifnull(cou.name, 'n/a') AS checkedoutby, 
					ifnull(mu.name, 'n/a') AS modifiedby, 
					dcv.filename, 
					dcv.size, 
					dcv.major_version, 
					dcv.minor_version, 
					dcv.storage_path,
					ifnull(mt.mimetypes, 'unknown') as mime_type,
					ifnull(mt.icon_path, 'unknown') as mime_icon_path,
					ifnull(mt.friendly_name, 'unknown') as mime_display					
				FROM 
					documents d
					INNER JOIN permission_lookups AS PL ON d.permission_lookup_id = PL.id
            		INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = $permissionid
					INNER JOIN document_metadata_version AS dmv ON d.metadata_version_id=dmv.id 
					INNER JOIN document_content_version AS dcv ON dmv.content_version_id=dcv.id
					LEFT OUTER JOIN mime_types mt ON dcv.mime_id = mt.id
					LEFT OUTER JOIN users AS uc ON d.creator_id=uc.id
					LEFT OUTER JOIN users AS cou ON d.checked_out_user_id=cou.id
					LEFT OUTER JOIN users AS mu ON d.modified_user_id=mu.id					
				WHERE 
					d.folder_id=$this->folderid
					AND d.status_id = 1
					AND PLA.permission_descriptor_id IN ($aPermissionDescriptors)";
		}
			
		if (strpos($what,'F') !== false)
		{
			if (strpos($what,'D') !== false)
			{
				$sql .= ' UNION ';
			}
			
			$sql .= "
				SELECT 
					f.id, 
					'F' as item_type,
					f.name as title,
					ifnull(uc.name, 'n/a') AS creator, 
					'n/a' checkedoutby, 
					'n/a' AS modifiedby, 
					f.name as filename, 
					'n/a' as size, 
					'n/a' as major_version, 
					'n/a' as minor_version, 
					'n/a' as storage_path,
					'folder' as mime_type,
					'folder' as mime_icon_path,
					'Folder' as mime_display					
				FROM 
					folders f
					INNER JOIN permission_lookups AS PL ON f.permission_lookup_id = PL.id
            		INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = $permissionid
					LEFT OUTER JOIN users AS uc ON f.creator_id=uc.id					
					
				WHERE 
					f.parent_id=$this->folderid
					 
					AND PLA.permission_descriptor_id IN ($aPermissionDescriptors)	
			ORDER BY item_type DESC, title, filename								
		";
		}
		
		$contents = DBUtil::getResultArray($sql);
		if (is_null($contents) || PEAR::isError($contents))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR , $contents);
		}
		
		$num_items = count($contents);
		for($i=0;$i<$num_items;$i++)		
		{
			$contents[$i]['id'] = (int) $contents[$i]['id'];
			if ($contents[$i]['item_type'] == 'D')
			{
				$contents[$i]['items'] = array();
			}
			else 
			{
				if ($depth-1 > 0)
				{
					$folder = &$this->ktapi->get_folder_by_id($item['id']);
					$contents[$i]['items'] = $folder->get_listing($depth-1);					
				}
				else 
				{
					$contents[$i]['items'] = array();
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
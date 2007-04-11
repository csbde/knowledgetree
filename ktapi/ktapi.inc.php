<?

/**
 *
 * Implements a cleaner wrapper API for KnowledgeTree.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

// Generic error messages used in the API. There may be some others specific to functionality
// directly in the code.
// TODO: Check that they are all relevant. 
 
define('KTAPI_ERROR_SESSION_INVALID', 			'The session could not be resolved.');
define('KTAPI_ERROR_PERMISSION_INVALID', 		'The permission could not be resolved.');
define('KTAPI_ERROR_FOLDER_INVALID', 			'The folder could not be resolved.');
define('KTAPI_ERROR_DOCUMENT_INVALID', 			'The document could not be resolved.');
define('KTAPI_ERROR_USER_INVALID', 				'The user could not be resolved.');
define('KTAPI_ERROR_KTAPI_INVALID', 			'The ktapi could not be resolved.');
define('KTAPI_ERROR_INSUFFICIENT_PERMISSIONS', 	'The user does not have sufficient permissions to access the resource.');
define('KTAPI_ERROR_INTERNAL_ERROR', 			'An internal error occurred. Please review the logs.');
define('KTAPI_ERROR_DOCUMENT_TYPE_INVALID', 	'The document type could not be resolved.');
define('KTAPI_ERROR_DOCUMENT_CHECKED_OUT', 		'The document is checked out.');
define('KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT', 	'The document is not checked out.');
define('KTAPI_ERROR_WORKFLOW_INVALID', 			'The workflow could not be resolved.');
define('KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS', 	'The workflow is not in progress.');

// Mapping of permissions to actions.
// TODO: Check that they are all correct.
// Note, currently, all core actions have permissions that are defined in the plugins.
// As the permissions are currently associated with actions which are quite closely linked
// to the web interface, it is not the nicest way to do things. They should be associated at
// a lower level, such as in the api. probably, better, would be at some stage to assocate
// the permissions to the action/transaction in the database so administrators can really customise 
// as required.

define('KTAPI_PERMISSION_DELETE',			'ktcore.permissions.delete');
define('KTAPI_PERMISSION_READ',				'ktcore.permissions.read');
define('KTAPI_PERMISSION_WRITE',			'ktcore.permissions.write');
define('KTAPI_PERMISSION_ADD_FOLDER',		'ktcore.permissions.addFolder');
define('KTAPI_PERMISSION_RENAME_FOLDER',	'ktcore.permissions.folder_rename');
define('KTAPI_PERMISSION_CHANGE_OWNERSHIP',	'ktcore.permissions.security');
define('KTAPI_PERMISSION_DOCUMENT_MOVE',	'ktcore.permissions.write');
define('KTAPI_PERMISSION_WORKFLOW',			'ktcore.permissions.workflow');

//

class KTAPI_Session
{
	var $ktapi;
	var $user = null;
	var $session = '';
	var $sessionid = -1;
	var $ip = null;
	
	function KTAPI_Session(&$ktapi, &$user, $session, $sessionid, $ip)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi,'KTAPI'));
		assert(!is_null($user));
		assert(is_a($user,'User'));
				
		$this->ktapi 	= &$ktapi;
		$this->user 	= &$user;
		$this->session 	= $session;
		$this->sessionid = $sessionid;
		$this->ip 		= $ip;

		// TODO: get documenttransaction to not look at the session variable!
		$_SESSION["userID"] = $user->getId();
		$_SESSION["sessionID"] = $this->sessionid;			
	}
	
	/**
	 * This returns the session string
	 *
	 * @return string
	 */
	function get_session()
	{
		return $this->session;
	}
	
	/**
	 * This returns the sessionid in the database.
	 *
	 * @return int
	 */
	function get_sessionid()
	{
		return $this->sessionid;
	}
	
	/**
	 * This returns a user object for the use rassociated with the session. 
	 *
	 * @return User
	 */
	function &get_user()
	{
		 return $this->user;
	}
		
	/**
	 * This resolves the user's ip
	 *
	 * @access static
	 * @return string
	 */
	function resolveIP()
	{
		if (getenv("REMOTE_ADDR")) 
		{
        	$ip = getenv("REMOTE_ADDR");
        } 
        elseif (getenv("HTTP_X_FORWARDED_FOR")) 
        {
        	$forwardedip = getenv("HTTP_X_FORWARDED_FOR");
            list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
        } 
        elseif (getenv("HTTP_CLIENT_IP")) 
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        
        if ($ip == '')
        {
        	$ip = '127.0.0.1';
        }
        
        return $ip;
	}
	
	/**
	 * This returns a session object based on authentication credentials.
	 *
	 * @access static
	 * @param string $username
	 * @param string $password
	 * @return KTAPI_Session
	 */
	function &start_session(&$ktapi, $username, $password, $ip=null)
	{		
		
		if ( empty($username) ) 
		{
			return new PEAR_Error(_kt('The username is empty.'));
		}

		$user =& User::getByUsername($username);
        if (PEAR::isError($user) || ($user === false)) 
        {
           return new PEAR_Error(_kt("The user '$username' cound not be found."));      
        }
		
        if ($user->isAnonymous())
        {
        	$authenticated = true;
        	
        	$config = &KTConfig::getSingleton();	
    	    $allow_anonymous = $config->get('session/allowAnonymousLogin', false);
    	    
    	    if (!$allow_anonymous)
    	    {
    	    	return new PEAR_Error(_kt('Anonymous user not allowed'));
    	    }   
    	    
        }
        else
        {
        	
			if ( empty($password) ) 
			{
				return new PEAR_Error(_kt('The password is empty.'));
			}		
        	
        	$authenticated = KTAuthenticationUtil::checkPassword($user, $password);

        	if (PEAR::isError($authenticated) || $authenticated === false)
        	{
        		return new PEAR_Error(_kt("The password is invalid."));
        	}
        }
        
        
    
        
        if (is_null($ip))
        {
        	$ip = KTAPI_Session::resolveIP();
        }
        
        session_start();
        
        $user_id = $user->getId();
        
        $sql = "SELECT count(*) >= u.max_sessions as over_limit FROM active_sessions ass INNER JOIN users u ON ass.user_id=u.id WHERE ass.user_id = $user_id";
		$row = DBUtil::getOneResult($sql);
        if (PEAR::isError($row))
        {
        	return $row;
        }
        if (is_null($row))
        {
        	return new PEAR_Error('No record found for user?');
        }  
        if ($row['over_limit'] == 1)
        {
			return new PEAR_Error('Session limit exceeded. Logout of any active sessions.');
        }
        
        $session = session_id();
        
        $sessionid = DBUtil::autoInsert('active_sessions',
        	array(
        		'user_id' => $user_id,
        		'session_id' => session_id(),
        		'lastused' => date('Y-m-d H:i:s'),
        		'ip' => $ip
        	));
        if (PEAR::isError($sessionid) )
        {
        	return $sessionid;
        }
                
		$session = &new KTAPI_Session($ktapi, $user, $session, $sessionid, $ip);		
		
		return $session;
	}	
	
	/**
	 * This returns an active session.
	 *
	 * @param KTAPI $ktapi
	 * @param string $session
	 * @param string $ip
	 * @return KTAPI_Session
	 */
	function &get_active_session(&$ktapi, $session, $ip)
	{        		
		$sql = "SELECT id, user_id FROM active_sessions WHERE session_id='$session'";
		if (!empty($ip))
		{
			$sql .= " AND ip='$ip'";
		}		
		
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			return new PEAR_Error(KTAPI_ERROR_SESSION_INVALID);
		}
		
		$sessionid = $row['id'];
		$userid = $row['user_id'];
		
		$user = &User::get($userid);
		if (is_null($user) || PEAR::isError($user))
		{
			return new PEAR_Error(KTAPI_ERROR_USER_INVALID);
		}
		

		
        $now=date('Y-m-d H:i:s');
        $sql = "UPDATE active_sessions SET last_used='$now' WHERE id=$sessionid";
        DBUtil::runQuery($sql);
        
		$session = &new KTAPI_Session($ktapi, $user, $session, $sessionid, $ip);		
		return $session;
	}
	
	/**
	 * This closes the current session.
	 *
	 */
	function logout()
	{
		$sql = "DELETE FROM active_sessions WHERE id=$this->sessionid";
		$result = DBUtil::runQuery($sql);
		if (PEAR::isError($result))
		{
			return $result;
		}
			
		$this->user 		= null;
		$this->session 		= '';
		$this->sessionid 	= -1;
		return true;
	}
	
}

class KTAPI_FolderItem
{
	/**
	 * This is a reference to the core KTAPI controller
	 *
	 * @access protected
	 * @var KTAPI
	 */
	var $ktapi;	
	
	function &can_user_access_object_requiring_permission(&$object, &$permission)
	{	
		return $this->ktapi->can_user_access_object_requiring_permission($object, $permission);
	}
}


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
	 * @access static
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
			return new PEAR_Error(KTAPI_ERROR_FOLDER_INVALID);
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
			'id'=>$this->folderid,
			'folder_name'=>$this->get_folder_name(),
			'parent_id'=>$this->get_parent_folder_id(),
			'full_path'=>$this->get_full_path(),
		);
		
		return $detail;
	}
	
	function get_parent_folder_id()
	{
		return $this->folder->getParentID();
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
		return $this->folderid;
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
			$sql = "SELECT id FROM folders WHERE name='$foldername' and parent_id=$folderid";
			$row = DBUtil::getOneResult($sql);
			if (is_null($row) || PEAR::isError($row))
			{
				return new PEAR_Error(KTAPI_ERROR_FOLDER_INVALID);
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
			return new PEAR_Error(KTAPI_ERROR_FOLDER_INVALID);
		}
		
		//$folder = $ktapi_folder->get_folder();
		$folderid = $ktapi_folder->folderid;
		
		$document = Document::$function($documentname, $folderid);		
		if (is_null($document) || PEAR::isError($document))
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_INVALID);
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
		$aPermissionDescriptors = implode(',',KTPermissionUtil::getPermissionDescriptorsForUser($user));
		
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		
		$num_items = count($contents);
		for($i=0;$i<$num_items;$i++)		
		{
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();
		$folderid = $result->getId();
		
		return $this->ktapi->get_folder_by_id($folderid);
	}
	
	/**
	 * This deletes the current folder.
	 *
	 * @param string $reason
	 * @return true
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();

		return true;
	}

	/**
	 * This renames the folder
	 *
	 * @param string $newname
	 * @return true
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();

		return true;
	}
	
	/**
	 * This moves the folder to another location.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @return true
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();

		return true;
	}
	
	/**
	 * This copies a folder to another location.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @return true
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
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();

		return true;
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

class KTAPI_Document extends KTAPI_FolderItem 
{
	/**
	 * This is a reference to the internal document object.
	 *
	 * @var Document
	 */
	var $document;	
	/**
	 * This is the id of the document.
	 *
	 * @var int
	 */
	var $documentid;
	/**
	 * This is a reference to the parent folder.
	 *
	 * @var KTAPI_Folder
	 */
	var $ktapi_folder;
	
	/**
	 * This is used to get a document based on document id.
	 *
	 * @access static
	 * @param KTAPI $ktapi
	 * @param int $documentid
	 * @return KTAPI_Document
	 */
	function &get(&$ktapi, $documentid)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi, 'KTAPI'));
		assert(is_numeric($documentid));
		
		$documentid += 0;
		
		$document = &Document::get($documentid);
		if (is_null($document) || PEAR::isError($document))
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_INVALID);
		}
		
		$user = $ktapi->can_user_access_object_requiring_permission($document, KTAPI_PERMISSION_READ);
		
		if (is_null($user) || PEAR::isError($user))
		{
			return $user;
		}	
		
		$folderid = $document->getParentID();

		$ktapi_folder = &KTAPI_Folder::get($ktapi, $folderid);
		// We don't do any checks on this folder as it could possibly be deleted, and is not required right now.

		return new KTAPI_Document($ktapi, $ktapi_folder, $document);
	}	
	
	/**
	 * This is the constructor for the KTAPI_Folder.
	 *
	 * @access private
	 * @param KTAPI $ktapi
	 * @param Document $document
	 * @return KTAPI_Document
	 */	
	function KTAPI_Document(&$ktapi, &$ktapi_folder, &$document)
	{
		assert(is_a($ktapi,'KTAPI'));
		assert(is_a($ktapi_folder,'KTAPI_Folder'));
		
		$this->ktapi = &$ktapi;
		$this->ktapi_folder = &$ktapi_folder;
		$this->document = &$document;
		$this->documentid = $document->getId();
	}
	
	/**
	 * This checks a document into the repository
	 *
	 * @param string $filename
	 * @param string $reason
	 * @param string $tempfilename
	 * @param bool $major_update
	 * @return true
	 */	
	function checkin($filename, $reason, $tempfilename, $major_update=false)
	{	
		if (!is_file($tempfilename))
		{
			return new PEAR_Error('File does not exist.');
		}
		
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);
		
		if (PEAR::isError($user))
		{
			return $user;
		}
		
		if (!$this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT);
		}

		$options = array('major_update'=>$major_update);

		$currentfilename = $this->document->getFileName();
		if ($filename != $currentfilename)
		{
			$options['newfilename'] = $filename;
		}

		DBUtil::startTransaction();
		$result = KTDocumentUtil::checkin($this->document, $tempfilename, $reason, $user, $options);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();
		return true;    	
	}
	
	/**
	 * This reverses the checkout process.
	 *
	 * @param string $reason
	 * @return true
	 */
	function undo_checkout($reason)
	{
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);
		
		if (PEAR::isError($user))
		{
			return $user;
		}

		if (!$this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT);
		}

		DBUtil::startTransaction();

		$this->document->setIsCheckedOut(0);
		$this->document->setCheckedOutUserID(-1);
		$res = $this->document->update();
		if (($res === false) || PEAR::isError($res))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		
		$oDocumentTransaction = & new DocumentTransaction($this->document, $reason, 'ktcore.transactions.force_checkin');
				
		$res = $oDocumentTransaction->create();
		if (($res === false) || PEAR::isError($res)) {
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		DBUtil::commit();
		return true;
	}

	/**
	 * This returns a URL to the file that can be downloaded.
	 *
	 * @param string $reason
	 * @return true;
	 */
	function checkout($reason)
	{
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}
 
		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		DBUtil::startTransaction();
		$res = KTDocumentUtil::checkout($this->document, $reason, $user);
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}

		DBUtil::commit();

		return true;
	}
	
	/**
	 * This deletes a document from the folder.
	 *
	 * @param string $reason
	 * @return mixed This returns true or a PEAR::Error 
	 */
	function delete($reason)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_DELETE);

		if (PEAR::isError($user))
		{
			return $user;
		} 

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		DBUtil::startTransaction();
		$res = KTDocumentUtil::delete($this->document, $reason);
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}

		DBUtil::commit();

		return true;		
	}
	
	/**
	 * This changes the owner of the file.
	 *
	 * @param string $ktapi_newuser
	 * @return true
	 */	
	function change_owner($newusername, $reason='Changing of owner.')
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_CHANGE_OWNERSHIP);

		if (PEAR::isError($user))
		{
			return $user;
		} 		
		           
        DBUtil::startTransaction();
        
        $user = &User::getByUserName($newusername);
        if (is_null($user) || PEAR::isError($user))
        {
        	return new PEAR_Error('User could not be found');
        }
        
        $newuserid = $user->getId();
        
        $this->document->setOwnerID($newuserid);
        
        $res = $this->document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        
        $res = KTPermissionUtil::updatePermissionLookup($this->document);
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        
		$oDocumentTransaction = & new DocumentTransaction($this->document, $reason, 'ktcore.transactions.permissions_change');
				
		$res = $oDocumentTransaction->create();
		if (($res === false) || PEAR::isError($res)) {
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
        
		DBUtil::commit();
		return true;		
	}	
	
	/**
	 * This copies the document to another folder.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @param string $newname
	 * @param string $newfilename
	 * @return true
	 */
	function copy(&$ktapi_target_folder, $reason, $newname=null, $newfilename=null)
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));
		
		if (empty($newname))
		{
			$newname=null;
		}
		if (empty($newfilename))
		{
			$newfilename=null;
		}
		
		
		$user = $this->ktapi->get_user();

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		$target_folder = &$ktapi_target_folder->get_folder();
		
		$result = $this->can_user_access_object_requiring_permission(  $target_folder, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}

		$name = $this->document->getName();
		$clash = KTDocumentUtil::nameExists($target_folder, $name);        
        if ($clash && !is_null($newname)) 
        {        
        	$name = $newname;
        	$clash = KTDocumentUtil::nameExists($target_folder, $name);
        } 
        if ($clash) 
        {
        	return new PEAR_Error('A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the copied document.');
        }
        
        $filename=$this->document->getFilename();
        $clash = KTDocumentUtil::fileExists($target_folder, $filename);              

        if ($clash && !is_null($newname)) 
        {
			$filename = $newfilename;
            $clash = KTDocumentUtil::fileExists($target_folder, $filename);              
        } 
        if ($clash) 
        {
        	return new PEAR_Error('A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the copied document.');
        }
        		
		DBUtil::startTransaction();
                 
        $new_document = KTDocumentUtil::copy($this->document, $target_folder, $reason);
        if (PEAR::isError($new_document)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        
        $new_document->setName($name);
        $new_document->setFilename($filename);
                
        $res = $new_document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }

        DBUtil::commit();
            
        // FIXME do we need to refactor all trigger usage into the util function?
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('copyDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $new_document,
                'old_folder' => $this->folder->get_folder(),
                'new_folder' => $target_folder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }	
        
		return true;        
	}
	
	/**
	 * This moves the document to another folder.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @param string $newname
	 * @param string $newfilename
	 * @return true
	 */
	function move(&$ktapi_target_folder, $reason, $newname=null, $newfilename=null)
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));
		
		if (empty($newname))
		{
			$newname=null;
		}
		if (empty($newfilename))
		{
			$newfilename=null;
		}		
		
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_DOCUMENT_MOVE);

		if (PEAR::isError($user))
		{
			return $user;
		} 

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		$target_folder = $ktapi_target_folder->get_folder();
		
		$result=  $this->can_user_access_object_requiring_permission(  $target_folder, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}
		
		if (!KTDocumentUtil::canBeMoved($this->document))
		{
			return new PEAR_Error('Document cannot be moved.');
		}

		$name = $this->document->getName();
		$clash = KTDocumentUtil::nameExists($target_folder, $name);        
        if ($clash && !is_null($newname)) 
        {        
        	$name = $newname;
        	$clash = KTDocumentUtil::nameExists($target_folder, $name);
        } 
        if ($clash) 
        {
        	return new PEAR_Error('A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the moved document.');
        }
        
        $filename=$this->document->getFilename();
        $clash = KTDocumentUtil::fileExists($target_folder, $filename);              

        if ($clash && !is_null($newname)) 
        {
			$filename = $newfilename;
            $clash = KTDocumentUtil::fileExists($target_folder, $filename);              
        } 
        if ($clash) 
        {
        	return new PEAR_Error('A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the moved document.');
        }
        		
		DBUtil::startTransaction();
                 
        $res = KTDocumentUtil::move($this->document, $target_folder, $user, $reason);
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        
        $this->document->setName($name);
        $this->document->setFilename($filename);
                
        $res = $this->document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }

        DBUtil::commit();
                   
		return true; 	
	}	
	
	/**
	 * This changes the filename of the document.
	 *
	 * @param string $newname
	 * @return true
	 */
	function renameFile($newname)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		} 
		
		DBUtil::startTransaction();
		$res = KTDocumentUtil::rename($this->document, $newname, $user);
		if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        DBUtil::commit(); 
		 	
        return true;
	}
	
	/**
	 * This changes the document type of the document.
	 *
	 * @param string $newname
	 * @return true
	 */
	function change_document_type($documenttype)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		} 
		
		$doctypeid = KTAPI::get_documenttypeid($documenttype);		 
		 
		if ($this->document->getDocumentTypeId() != $doctypeid)
		{
			DBUtil::startTransaction();
			$this->document->setDocumentTypeId($doctypeid);
			$res = $this->document->update();

			if (PEAR::isError($res))
			{
				DBUtil::rollback();
				return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
			}
			DBUtil::commit();
		}
        return true;
	}	
		
	/**
	 * This changes the title of the document.
	 *
	 * @param string $newname
	 * @return true
	 */
	function rename($newname)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		} 
		
		if ($this->document->getName() != $newname)
		{

			DBUtil::startTransaction();
			$this->document->setName($newname);
			$res = $this->document->update();

			if (PEAR::isError($res))
			{
				DBUtil::rollback();
				return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
			}
			DBUtil::commit();
		}
			
        return true;
	}
	
	/**
	 * This flags the document as 'archived'.
	 *
	 * @param string $reason
	 * @return true
	 */
	function archive($reason)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}

		list($permission, $user) = $perm_and_user;	
		
		DBUtil::startTransaction();
		$this->document->setStatusID(ARCHIVED);
        $res = $this->document->update();
        if (($res === false) || PEAR::isError($res)) {
           DBUtil::rollback();
           return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }
        
        $oDocumentTransaction = & new DocumentTransaction($this->document, sprintf(_kt('Document archived: %s'), $reason), 'ktcore.transactions.update');
        $oDocumentTransaction->create();
        
        DBUtil::commit();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('archive', 'postValidate');
        foreach ($aTriggers as $aTrigger) 
        {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $this->document,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }
		 	
        return true;		
	}	
	
	/**
	 * This starts a workflow on a document.
	 *
	 * @param string $workflow
	 * @return true
	 */
	function start_workflow($workflow)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
		
		$workflowid = $this->document->getWorkflowId();
		
		if (!empty($workflowid))
		{
			return new PEAR_Error('A workflow is already defined.');
		}
		
		$workflow = KTWorkflow::getByName($workflow);
		if (is_null($workflow) || PEAR::isError($workflow))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::startWorkflowOnDocument($workflow, $this->document);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		DBUtil::commit();
		
		return true;		
	}
	
	/**
	 * This deletes the workflow on the document.
	 *
	 * @return true
	 */
	function delete_workflow()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
				
		$workflowid=$this->document->getWorkflowId();
		if (!empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}
				
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::startWorkflowOnDocument(null, $this->document);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		DBUtil::commit();
		
		return true;		
	}
	
	/**
	 * This performs a transition on the workflow
	 *
	 * @param string $transition
	 * @param string $reason
	 * @return true
	 */
	function perform_workflow_transition($transition, $reason)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
				
		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}	
		
		$transition = &KTWorkflowTransition::getByName($transition);
		if (is_null($transition) || PEAR::isError($transition))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
				
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::performTransitionOnDocument($transition, $this->document, $user, $reason);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		DBUtil::commit();
		
		return true;			
	}		
	
	
	
	/**
	 * This returns all metadata for the document.
	 *
	 * @return array
	 */
	function get_metadata()
	{
		 $doctypeid = $this->document->getDocumentTypeID();
		 $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->document, $doctypeid);
		 
		 $results = array();
		 
		 foreach ($fieldsets as $fieldset) 
		 {
		 	if ($fieldset->getIsConditional()) {	/* this is not implemented...*/	continue;	}
		 	
		 	$fields = $fieldset->getFields();            
		 	$result = array('fieldset' => $fieldset->getName(),
		 					'description' => $fieldset->getDescription());
		 	
		 	$fieldsresult = array();
		 	 
            foreach ($fields as $field) 
            {                
                $value = 'n/a';                
                 
				$fieldvalue = DocumentFieldLink::getByDocumentAndField($this->document, $field);
                if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue))) 
                {
                	$value = $fieldvalue->getValue();
                }
                
                $controltype = 'string';
                if ($field->getHasLookup()) 
                {
                	$controltype = 'lookup';
                    if ($field->getHasLookupTree())
                    {
                    	$controltype = 'tree';
                    }
                }  
                
                switch ($controltype)
                {
                	case 'lookup':
                		$selection = KTAPI::get_metadata_lookup($field->getId());                		
                		break;
                	case 'tree':
                		$selection = KTAPI::get_metadata_tree($field->getId());
                		break;
                	default:
                		$selection= array();
                }

               
                $fieldsresult[] = array(
                	'name' => $field->getName(),
                	'required' => $field->getIsMandatory(),
                	'value' => $value,
                    'description' => $field->getDescription(),
                    'control_type' => $controltype,
                    'selection' => $selection
                                  
                );
                
            }
            $result['fields'] = $fieldsresult;
            $results [] = $result;	
		 }		 
		 
		 return $results; 
	}
	
	/**
	 * This updates the metadata on the file. This includes the 'title'.
	 *
	 * @param array This is an array containing the metadata to be associated with the file.
	 * @return mixed This returns true or a PEAR::Error 
	 */
	function update_metadata($metadata)
	{		 
		 $packed = array();
		 
		 foreach($metadata as $fieldset_metadata)
		 {
		 	$fieldsetname=$fieldset_metadata['fieldset'];
		 	$fieldset = KTFieldset::getByName($fieldsetname);
		 	if (is_null($fieldset) || PEAR::isError($fieldset))
		 	{
		 		// exit graciously
		 		continue;
		 	}
		 	
		 	foreach($fieldset_metadata['fields'] as $fieldinfo)
		 	{
		 		$fieldname = $fieldinfo['name'];
		 		$field = DocumentField::getByFieldsetAndName($fieldset, $fieldname);
		 		if (is_null($field) || PEAR::isError($fieldset))
		 		{
		 			// exit graciously
		 			continue;
		 		}	 		
		 		$value = $fieldinfo['value'];
		 		
		 		$packed[] = array($field, $value);
		 	}		 	
		 }
		 
		 DBUtil::startTransaction();
		 $result = KTDocumentUtil::saveMetadata($this->document, $packed);
        
		 if (is_null($result))
		 {
		 	DBUtil::rollback();
		 	return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		 }
		 if (PEAR::isError($result)) 
		 {
		 	DBUtil::rollback();
		 	return new PEAR_Error(sprintf(_kt("Unexpected validation failure: %s."), $result->getMessage()));	
		 }
		 DBUtil::commit();
		 
		 return true; 		
	}
	

	/**
	 * This returns a workflow transition
	 *
	 * @return array
	 */
	function get_workflow_transitions()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}

		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}		
				
		$result = array();
		
		$transitions = KTWorkflowUtil::getTransitionsForDocumentUser($this->document, $user);
		if (is_null($transitions) || PEAR::isError($transitions))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		foreach($transitions as $transition)
		{
			$result[] = $transition->getName(); 
		}
		
		return $result;		 
	}
	
	/**
	 * This returns the current workflow state
	 *
	 * @return string
	 */
	function get_workflow_state()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}

		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}		
		
		$result = array();
		
		$state = KTWorkflowUtil::getWorkflowStateForDocument($this->document);
		if (is_null($state) || PEAR::isError($state))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		
		$statename = $state->getName();
		
		return $statename;			
		
	}
	
	/**
	 * This returns detailed information on the document.
	 *
	 * @return array
	 */	
	function get_detail()
	{
		$detail = array();
		$document = $this->document;

		$detail['title'] = $document->getName();

		$documenttypeid=$document->getDocumentTypeID();
		if (is_numeric($documenttypeid))
		{
			$documenttype = DocumentType::get($documenttypeid);

			$detail['document_type'] = $documenttype->getName();
		}

		$detail['version'] = $document->getVersion();
		$detail['filename'] = $document->getFilename();

		$detail['created_date'] = $document->getCreatedDateTime();

		$userid = $document->getCreatorID();
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();
			

			$detail['created_by'] = $username;
		}

		$detail['updated_date'] = $document->getLastModifiedDate();

		$userid = $document->getModifiedUserId();
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();
			
			$detail['updated_by'] = $username;
		}

		$detail['document_id'] = $document->getId();
		$detail['folder_id'] = $document->getFolderID();

		$workflowid = $document->getWorkflowId();
		if (is_numeric($workflowid))
		{
			$workflow = KTWorkflow::get($workflowid);
			$workflowname=(is_null($workflow) || PEAR::isError($workflow))?'* unknown *':$workflow->getName();
			$detail['workflow'] = $workflowname;
		}

		$stateid = $document->getWorkflowStateId();
		if (is_numeric($stateid))
		{
			$state = KTWorkflowState::get($stateid);
			$workflowstate=(is_null($state) || PEAR::isError($state))?'* unknown *':$state->getName();

			$detail['workflow_state'] = $workflowstate;
		}

		$userid = $document->getCheckedOutUserID();
		 
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();
			
			$detail['checkout_by'] = $username;
		}
		
		$detail['full_path'] = $this->ktapi_folder->get_full_path() . '/' . $this->get_title();
		
		return $detail;
	}
	
	function get_title()
	{
		return $this->document->getDescription();
	}
	
	/**
	 * This does a download of a version of the document.
	 *
	 * @param string $version
	 * @return true
	 */
	function download($version=null)
	{		
		$storage =& KTStorageManagerUtil::getSingleton();
        $options = array();    
		
		
        $oDocumentTransaction = & new DocumentTransaction($this->document, 'Document downloaded', 'ktcore.transactions.download', $aOptions);
        $oDocumentTransaction->create();
        
        return true;
	}
	
	/**
	 * This returns the transaction history for the document.
	 *
	 * @return array
	 */
	function get_transaction_history()
	{		
        $sQuery = 'SELECT DTT.name AS transaction_name, U.name AS username, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime ' .
            'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('users') . ' AS U ON DT.user_id = U.id ' .
            'INNER JOIN ' . KTUtil::getTableName('transaction_types') . ' AS DTT ON DTT.namespace = DT.transaction_namespace ' .
            'WHERE DT.document_id = ? ORDER BY DT.datetime DESC';
        $aParams = array($this->documentid);

        $transactions = DBUtil::getResultArray(array($sQuery, $aParams));
        if (is_null($transactions) || PEAR::isError($transactions)) 
        {
        	return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
        }

        return $transactions;
	}
	
	/**
	 * This returns the version history on the document.
	 *
	 * @return array
	 */
	function get_version_history()
	{
		$metadata_versions = KTDocumentMetadataVersion::getByDocument($this->document);
		
        $versions = array();
        foreach ($metadata_versions as $version) 
        {
        	$document = &Document::get($this->documentid, $version->getId());
        	
        	$version = array();
        	
        	$userid = $document->getModifiedUserId();			 
			$user = User::get($userid);		 
        	
        	$version['user'] = $user->getName();
        	$version['metadata_version'] = $document->getMetadataVersion(); 
        	$version['content_version'] = $document->getVersion();
        	
            $versions[] = $version;
        }
        return $versions;
	}

	/**
	 * This expunges a document from the system.
	 *
	 * @access public
	 * @return mixed This returns true or a PEAR::Error 
	 */
	function expunge()
	{
		if ($this->document->getStatusID())
		DBUtil::startTransaction();
		
		$transaction = & new DocumentTransaction($this->document, "Document expunged", 'ktcore.transactions.expunge');
		
        $transaction->create();
        
        $this->document->delete();
        
        $this->document->cleanupDocumentData($this->documentid);	
		
		$storage =& KTStorageManagerUtil::getSingleton();
		 
		$result= $storage->expunge($this->document);

		$this->commitTransaction();

		return true; 
	}
	
	/**
	 * This expunges a document from the system.
	 *
	 * @access public
	 * @return mixed This returns true or a PEAR::Error 
	 */
	function restore()
	{
		$this->startTransaction();
		
		$storage =& KTStorageManagerUtil::getSingleton();
		
		$folder = Folder::get($this->document->getRestoreFolderId());
		if (PEAR::isError($folder)) 
		{
			$this->document->setFolderId(1);
			$folder = Folder::get(1);
		} 
		else 
		{
			$this->document->setFolderId($this->document->getRestoreFolderId());
		}

		$storage->restore($this->document);
		 
		$this->document->setStatusId(LIVE);
		$this->document->setPermissionObjectId($folder->getPermissionObjectId());
		$res = $this->document->update();

		$res = KTPermissionUtil::updatePermissionLookup($this->document);
		
		$user = $this->ktapi->get_user();

		$oTransaction = new DocumentTransaction($this->document, 'Restored from deleted state by ' . $user->getName(), 'ktcore.transactions.update');
		$oTransaction->create();

		$this->commitTransaction();
	}
}

class KTAPI
{
	/**
	 * This is the current session.
	 *
	 * @access private
	 * @var KTAPI_Session
	 */
	var $session = null;
 	
 	/**
 	 * This returns the current session.
 	 *
 	 * @access public
 	 * @return KTAPI_Session
 	 */ 	
 	function &get_session()
 	{
 		return $this->session;
 	}
 	
 	/**
 	 * This returns the session user.
 	 *
 	 * @access public
 	 * @return User
 	 */
 	function & get_user()
 	{ 		
 		$ktapi_session = $this->get_session();
		if (is_null($ktapi_session) || PEAR::isError($ktapi_session))
		{
			return new PEAR_Error(KTAPI_ERROR_SESSION_INVALID);
		}
		
		$user = $ktapi_session->get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			return new PEAR_Error(KTAPI_ERROR_USER_INVALID);
		}		
		
		return $user;
 	}
 	
 	/**
 	 * This returns a permission.
 	 *
 	 * @access static
 	 * @param string $permission
 	 * @return KTPermission
 	 */
 	function &get_permission($permission)
 	{ 	 		
		$permission = & KTPermission::getByName($permission);
		if (is_null($permission) || PEAR::isError($permission))
		{
			return new PEAR_Error(KTAPI_ERROR_PERMISSION_INVALID);
		}
				
		return $permission;	
 	}
 	
 	/**
 	 * This checks if a user can access an object with a certain permission.
 	 *
 	 * @access public
 	 * @param object $object
 	 * @param string $permission
 	 * @return User
 	 */
 	function can_user_access_object_requiring_permission(&$object, &$permission)
 	{
		assert(!is_null($object));
 		assert(is_a($object,'DocumentProxy') || is_a($object,'FolderProxy') || is_a($object,'Document') || is_a($object,'Folder'));		
		
 		$permission = &KTAPI::get_permission($permission);
		if (is_null($permission) || PEAR::isError($permission))
		{
			return $permission;
		}
		
 		$user = &KTAPI::get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			return $user;
		}		

		if (!KTPermissionUtil::userHasPermissionOnItem($user, $permission, $object)) 
		{
			return new PEAR_Error(KTAPI_ERROR_INSUFFICIENT_PERMISSIONS);
		}
		
		return $user; 		
 	}
 	
	/**
	 * This returns a session object based on a session string.
	 *
	 * @access public
	 * @param string $session
	 * @return KTAPI_Session  
	 */
	function & get_active_session($session, $ip=null)
	{
		if (!is_null($this->session))
		{
			return new PEAR_Error('A session is currently active.');
		}
		
		$session = &KTAPI_Session::get_active_session($this, $session, $ip);
		
		if (is_null($session) || PEAR::isError($session))
		{
			return new PEAR_Error('Session is invalid');
		}
		
		$this->session = &$session;
		return $session;
	}
	
	/**
	 * This returns a session object based on authentication credentials.
	 *
	 * @access public
	 * @param string $username
	 * @param string $password
	 * @return KTAPI_Session 
	 */
	function & start_session($username, $password, $ip=null)
	{	 
		if (!is_null($this->session))
		{
			return new PEAR_Error('A session is currently active.');
		}
				
		$session = &KTAPI_Session::start_session($this, $username, $password, $ip);
		if (is_null($session) || PEAR::isError($session))
		{
			return new PEAR_Error('Session is invalid');
		}
		$this->session = &$session;
		
		return $session;
	}
	
	/**
	 * Starts an anonymous session.
	 *
	 * @param string $ip
	 * @return KTAPI_Session
	 */
	function &start_anonymous_session($ip=null)
	{
		return $this->start_session('anonymous','',$ip);
	}
	
	
	/**
	 * Obtains the root folder.
	 *
	 * @access public
	 * @return KTAPI_Folder
	 */
	function &get_root_folder()
	{
		return $this->get_folder_by_id(1);
	}
	
	/**
	 * Obtains the folder using a folder id.
	 *
	 * @access public
	 * @param int $folderid
	 * @return KTAPI_Folder
	 */
	function &get_folder_by_id($folderid)
	{
		if (is_null($this->session))
		{
			return new PEAR_Error('A session is not active');			
		}
				
		return KTAPI_Folder::get($this, $folderid);
	}
		
	/**
	 * This returns a refererence to a document based on document id.
	 *
	 * @access public
	 * @param int $documentid
	 * @return KTAPI_Document
	 */
	function &get_document_by_id($documentid)
	{		
		return KTAPI_Document::get($this, $documentid);
	}
	 
	/**
	 * This returns a document type id based on the name.
	 *
	 * @access static
	 * @param string $documenttype
	 * @return int
	 */
	function get_documenttypeid($documenttype)
	{
		$sql = "SELECT id FROM document_types_lookup WHERE name='$documenttype' and disabled=0";
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_TYPE_INVALID);
		}
		list($documenttypeid) = $row['id'];
		return $documenttypeid;
	}
	
	/**
	 * Returns an array of document types.
	 *
	 * @access static
	 * @return array
	 */
	function get_documenttypes()
	{
		$sql = "SELECT name FROM document_types_lookup WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		
		$result = array();
		foreach($rows as $row)
		{
			$result[] = $row['name'];
		}

		return $result;		
	}
	
	/**
	 * Returns an array of username/name combinations.
	 *
	 * @access static
	 * @return array
	 */
	function get_users()
	{
		$sql = "SELECT username, name FROM users WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		return $rows;
	}	
	
	/**
	 * This returns an array for a lookup.
	 *
	 * @access static
	 * @param int $fieldid
	 * @return array
	 */
	function get_metadata_lookup($fieldid)
	{
		$sql = "SELECT name FROM metadata_lookup WHERE disabled=0 AND document_field_id=$fieldid ORDER BY name";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		$results=array();
		foreach($rows as $row)
		{
			$results[] = $row['name'];
		}		
		return $results;
	}
	
	/**
	 * This returns a metadata tree.
	 *
	 * @access private
	 * @param int $fieldid
	 * @param int $parentid
	 * @return array
	 */
	function _load_metadata_tree($fieldid, $parentid=0)
		{
			$sql = "SELECT id, name FROM metadata_lookup_tree WHERE document_field_id=$fieldid AND metadata_lookup_tree_parent=$parentid";
			$rows = DBUtil::getResultArray($sql);
			if (is_null($rows) || PEAR::isError($rows))
			{
				return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
			}
			$results=array();
			foreach($rows as $row)
			{
				$result=array(
					'name' => $row['name'],
					'children' => load($fieldid, $row['id'])
				);
				$results[] = $result;
			}
			return $results;
		}
	
	/**
	 * This returns a metadata tree.
	 *
	 * @access static
	 * @param int $fieldid
	 * @return array
	 */
	function get_metadata_tree($fieldid)
	{
		return KTAPI::_load_metadata_tree($fieldid);		
	}	
	
	/**
	 * Returns a list of workflows that are active.
	 *
	 * @access static
	 * @return array
	 */
	function get_workflows()
	{
		$sql = "SELECT name FROM workflows WHERE enabled=1";
		$rows=DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR);
		}
		$results=array();
		foreach($rows as $row)
		{
			$results[] = $row['name'];
		}
		return $results;
	}
	
}

class KTIndexingManager
{
	/**
	 * This is the maximum number of documents to index in one session.
	 *
	 * @var int
	 */
	var $max_workload;
	
	function KTIndexingManager()
	{
		$this->max_workload = 5;
	}
	
	/**
	 * This starts and indexing session.
	 *
	 * @access public
	 * @return bool
	 */
	function start_indexing()
	{
		return new PEAR_Error('TODO');
	}
	
	/**
	 * This indexes a new document.
	 *
	 * @access private
	 * @return bool
	 */
	function process_file()
	{
		return new PEAR_Error('TODO');		
	}
	
	/**
	 * This returns a complete list of documents to be indexed. This is ideally used in reporting.
	 *
	 * @access public
	 * @return array
	 */
	function report_pending_indexing()
	{
		return new PEAR_Error('TODO');				
	}
	
	/**
	 * This returns a list of documents to be indexed. This is used by start_indexing().
	 *
	 * @access public
	 * @param int $workload
	 */
	function get_pending_indexing($workload=null)
	{
		if (is_null($workload))
		{
			$workload=$this->max_workload;
		}
		
		
		return new PEAR_Error('TODO');				
	}	
}
 
 
?>
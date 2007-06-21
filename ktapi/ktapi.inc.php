<?
/**
 * $Id$
 * 
 * Implements a cleaner wrapper API for KnowledgeTree.
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

session_start();
require_once(realpath(dirname(__FILE__) . '/../config/dmsDefaults.php'));
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

define('KTAPI_DIR',KT_DIR . '/ktapi');

require_once(KTAPI_DIR .'/KTAPIConstants.inc.php');
require_once(KTAPI_DIR .'/KTAPISession.inc.php');
require_once(KTAPI_DIR .'/KTAPIFolder.inc.php');
require_once(KTAPI_DIR .'/KTAPIDocument.inc.php');
		
class KTAPI_FolderItem
{
	/**
	 * This is a reference to the core KTAPI controller
	 *
	 * @access protected
	 * @var KTAPI
	 */
	var $ktapi;	
	
	function &can_user_access_object_requiring_permission(&$object, $permission)
	{	
		return $this->ktapi->can_user_access_object_requiring_permission($object, $permission);
	}
}

class KTAPI_Error extends PEAR_Error
{
	function KTAPI_Error($msg, $obj)
	{
		if (PEAR::isError($obj))
		{
			parent::PEAR_Error($msg . ' - ' . $obj->getMessage());
		}
		else 
		{
			parent::PEAR_Error($msg);
		}
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
 	 * @static
 	 * @access public
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
 	function can_user_access_object_requiring_permission(&$object, $permission)
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
		
		$session = &KTAPI_UserSession::get_active_session($this, $session, $ip);
		
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
				
		$session = &KTAPI_UserSession::start_session($this, $username, $password, $ip);
		if (is_null($session))
		{
			return new PEAR_Error('Session is null.');
		}
		if (PEAR::isError($session))
		{
			return new PEAR_Error('Session is invalid. ' . $session->getMessage());
		}
		$this->session = &$session;
		
		return $session;
	}
	
	
	function & start_system_session()
	{
		$user = User::get(1);
		
		$session = & new KTAPI_SystemSession($this, $user);
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
		if (!is_null($this->session))
		{
			return new PEAR_Error('A session is currently active.');
		}
				
		$session = &KTAPI_AnonymousSession::start_session($this, $ip);
		if (is_null($session))
		{
			return new PEAR_Error('Session is null.');
		}
		if (PEAR::isError($session))
		{
			return new PEAR_Error('Session is invalid. ' . $session->getMessage());
		}
		$this->session = &$session;
		
		return $session;
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
	 * @static
	 * @access public
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
	 * @static
	 * @access public
	 * @return array
	 */
	function get_documenttypes()
	{
		$sql = "SELECT name FROM document_types_lookup WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
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
	 * @static
	 * @access public
	 * @return array
	 */
	function get_users()
	{
		$sql = "SELECT username, name FROM users WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		return $rows;
	}	
	
	/**
	 * This returns an array for a lookup.
	 *
	 * @static
	 * @access public
	 * @param int $fieldid
	 * @return array
	 */
	function get_metadata_lookup($fieldid)
	{
		$sql = "SELECT name FROM metadata_lookup WHERE disabled=0 AND document_field_id=$fieldid ORDER BY name";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
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
			return KTAPI::get_metadata_lookup($fieldid);
			/*
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
			return $results;*/
		}
	
	/**
	 * This returns a metadata tree.
	 *
	 * @static
	 * @access public
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
	 * @static 
	 * @access public
	 * @return array
	 */
	function get_workflows()
	{
		$sql = "SELECT name FROM workflows WHERE enabled=1";
		$rows=DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		$results=array();
		foreach($rows as $row)
		{
			$results[] = $row['name'];
		}
		return $results;
	}
	
}

?>
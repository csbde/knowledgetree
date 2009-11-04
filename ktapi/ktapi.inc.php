<?php
/**
* Implements a cleaner wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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

$_session_id = session_id();
if (empty($_session_id)) session_start();
unset($_session_id);

require_once(realpath(dirname(__FILE__) . '/../config/dmsDefaults.php'));
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");

define('KTAPI_DIR', KT_DIR . '/ktapi');

require_once(KTAPI_DIR .'/KTAPIConstants.inc.php');
require_once(KTAPI_DIR .'/KTAPISession.inc.php');
require_once(KTAPI_DIR .'/KTAPIFolder.inc.php');
require_once(KTAPI_DIR .'/KTAPIDocument.inc.php');
require_once(KTAPI_DIR .'/KTAPIAcl.inc.php');
require_once(KTAPI_DIR .'/KTAPICollection.inc.php');
require_once(KTAPI_DIR .'/KTAPIBulkActions.inc.php');

/**
* This class defines functions that MUST exist in the inheriting class
*
* @abstract
* @author KnowledgeTree Team
* @package KTAPI
* @version Version 0.9
*/
abstract class KTAPI_FolderItem
{
	/**
	* This is a reference to the core KTAPI controller
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object $ktapi The KTAPI object
	*/
	protected $ktapi;

 	/**
 	* This checks if a user can access an object with a certain permission.
 	*
 	* @author KnowledgeTree Team
	* @access public
 	* @param object $object The object the user is trying to access
 	* @param string $permission The permissions string
 	* @return object $user The User object
 	*/
	public function &can_user_access_object_requiring_permission(&$object, $permission)
	{
		$user = $this->ktapi->can_user_access_object_requiring_permission($object, $permission);
		return $user;
	}

	public abstract function getObject();

	public abstract function getRoleAllocation();

	public abstract function getPermissionAllocation();

	public abstract function isSubscribed();

	public abstract function unsubscribe();

	public abstract function subscribe();

}

/**
* This class extends the PEAR_Error class for errors in the KTAPI class
*
* @author KnowledgeTree Team
* @package KTAPI
* @version Version 0.9
*/
class KTAPI_Error extends PEAR_Error
{
 	/**
 	* This method determines if there is an error in the object itself or just a common error
 	*
	* @author KnowledgeTree Team
 	* @access public
 	* @return VOID
 	*/
	public function KTAPI_Error($msg, $obj = null)
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

/**
* This class extends the KTAPI_Error class for errors in the KTAPI Document class
*
* @author KnowledgeTree Team
* @package KTAPI
* @version Version 0.9
*/
class KTAPI_DocumentTypeError extends KTAPI_Error
{

}

/**
* This is the main KTAPI class
*
* @author KnowledgeTree Team
* @package KTAPI
* @version Version 0.9
*/

class KTAPI
{
	/**
	* This is the current session.
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object $session The KTAPI_Session object
	*/
	protected $session = null;

	protected $version = 3;

    private $esig_enabled;

    public function KTAPI()
    {
        $this->esig_enabled = $this->electronic_sig_enabled();
    }

 	/**
 	* This returns the current session.
 	*
	* @author KnowledgeTree Team
 	* @access protected
 	* @return object $session The KTAPI_Session object
 	*/
 	public function &get_session()
 	{
 	    $session = $this->session;
 		return $session;
 	}

 	/**
	* This returns the session user object or an error object.
 	*
	* @author KnowledgeTree Team
 	* @access protected
 	* @return object $user SUCCESS - The User object | FAILURE - an error object
 	*/
 	public function & get_user()
 	{
 		$ktapi_session = $this->get_session();
 		if (is_null($ktapi_session) || PEAR::isError($ktapi_session))
		{
			$error = new PEAR_Error(KTAPI_ERROR_SESSION_INVALID);
			return $error;
		}

		$user = $ktapi_session->get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			$error =  new PEAR_Error(KTAPI_ERROR_USER_INVALID);
			return $error;
		}
		return $user;
 	}

 	/**
 	 * Get the available columns for the given view (browse | search)
 	 *
	 * @author KnowledgeTree Team
 	 * @access public
 	 * @param string $view The namespace for the view - ktcore.views.browse | ktcore.views.search
 	 * @return unknown
 	 */
 	function get_columns_for_view($view = 'ktcore.views.browse') {
 		$ktapi_session = $this->get_session();
		if (is_null($ktapi_session) || PEAR::isError($ktapi_session))
		{
			$error = new PEAR_Error(KTAPI_ERROR_SESSION_INVALID);
			return $error;
		}

 		$collection = new KTAPI_Collection();
 		return $collection->get_columns($view);
 	}

 	/**
 	* This returns a permission object or an error object.
 	*
	* @author KnowledgeTree Team
 	* @access protected
 	* @param string $permission The permissions string
 	* @return object $permissions SUCCESS - The KTPermission object | FAILURE - an error object
 	*/
 	public function &get_permission($permission)
 	{
		$permissions = & KTPermission::getByName($permission);
		if (is_null($permissions) || PEAR::isError($permissions))
		{
			$error =  new PEAR_Error(KTAPI_ERROR_PERMISSION_INVALID);
			return $error;
		}
		return $permissions;
 	}

	/**
	* Returns an associative array of permission namespaces and their names
	*
	* @author KnowledgeTree Team
	* @access public
	* @return array
	*/

	public function get_permission_types() {
		$types = array();
		$list = KTAPI_Permission::getList();
		foreach($list as $val) {
			$types[$val->getNameSpace()] = $val->getName();
		}
		return $types;
	}

	/**
	* Returns folder permissions
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @param int
	*
	*/
	public function get_folder_permissions($username, $folder_id) {
		if (is_null($this->session))
		{
			return array(
				"status_code" => 1,
				"message" => "Your session is not active"
			);
		}
		/* We need to create a new instance of KTAPI to get another user */
		$user_ktapi = new KTAPI();
		$user_ktapi->start_system_session($username);

		$folder = KTAPI_Folder::get($user_ktapi, $folder_id);

		$permissions = $folder->getPermissionAllocation();

		$user_ktapi->session_logout();

		return array(
			"status_code" => 0,
			"results" => $permissions->permissions
		);

	}

	/**
	 * Returns folder permissions
	 *
	 * @access public
	 * @param string
	 * @param int
	 *
	 */
	public function get_document_permissions($username, $document_id) {
		if (is_null($this->session))
		{
			return array(
				"status_code" => 1,
				"message" => "Your session is not active"
				);
		}
		/* We need to create a new instance of KTAPI to get another user */
		$user_ktapi = new KTAPI();
		$user_ktapi->start_system_session($username);

		$document = KTAPI_Document::get($user_ktapi, $document_id);

		if (get_class($document) == 'PEAR_Error') {
			return array(
				"status_code" => 0,
				"results" => null
			);
		}

		$permissions = $document->getPermissionAllocation();

		$user_ktapi->session_logout();

		return array(
			"status_code" => 0,
			"results" => $permissions->permissions
		);

	}

	/**
	* Add folder permission
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string
	* @param string
	* @param int
	*
	*/
	public function add_folder_user_permissions($username, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.permissions_change');
        if ($response['status_code'] == 1) return $response;

		if (is_null($this->session))
		{
			return array(
				"status_code" => 1,
				"message" => "Your session is not active"
			);
		}

		/* First check that user trying to add permission can actually do so */
		$folder = KTAPI_Folder::get($this, $folder_id);
		$permissions = $folder->getPermissionAllocation();
		$detail = $permissions->permissions;
		if(!in_array("Manage security", $detail)) {
			return array(
				"status_code" => 1,
				"message" => "User does not have permission to manage security"
			);
		}

		/* We need to create a new instance of KTAPI to get another user */
		$user_ktapi = new KTAPI();
		$user_ktapi->start_system_session($username);

		$folder = KTAPI_Folder::get($user_ktapi, $folder_id);
		if(PEAR::isError($folder))
		{
			$user_ktapi->session_logout();
			return array(
				"status_code" => 1,
				"message" => $folder->getMessage()
			);
		}

		$permission = KTAPI_Permission::getByNamespace($namespace);
		if(PEAR::isError($permission)) {
			$user_ktapi->session_logout();
			return array(
				"status_code" => 1,
				"message" => $permission->getMessage()
			);
		}


		$user = KTAPI_User::getByUsername($username);
		if(PEAR::isError($user)) {
			$user_ktapi->session_logout();
			return array(
				"status_code" => 1,
				"message" => $user->getMessage()
			);
		}

		$permissions = $folder->getPermissionAllocation();

		$permissions->add($user, $permission);
		$permissions->save();
	}

	/**
	* Add folder role permission
	*
	* @access public
	* @param string
	* @param string
	* @param int
	*
	*/
	public function add_folder_role_permissions($role, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.permissions_change');
        if ($response['status_code'] == 1) return $response;

		if (is_null($this->session))
		{
			return array(
				"status_code" => 1,
				"message" => "Your session is not active"
			);
		}

		/* First check that user trying to add permission can actually do so */
		$folder = KTAPI_Folder::get($this, $folder_id);
		$permissions = $folder->getPermissionAllocation();
		$detail = $permissions->permissions;
		if(!in_array("Manage security", $detail)) {
			return array(
				"status_code" => 1,
				"message" => "User does not have permission to manage security"
			);
		}

		$folder = KTAPI_Folder::get($this, $folder_id);
		if(PEAR::isError($folder))
		{
			return array(
				"status_code" => 1,
				"message" => $folder->getMessage()
			);
		}

		$permission = KTAPI_Permission::getByNamespace($namespace);
		if(PEAR::isError($permission)) {
			return array(
				"status_code" => 1,
				"message" => $permission->getMessage()
			);
		}


		$role = KTAPI_Role::getByName($role);
		if(PEAR::isError($role)) {
			return array(
				"status_code" => 1,
				"message" => $role->getMessage()
			);
		}

		$permissions = $folder->getPermissionAllocation();

		$permissions->add($role, $permission);
		$permissions->save();
	}

	/**
	* Add folder group permission
	*
	* @access public
	* @param string
	* @param string
	* @param int
	*
	*/
	public function add_folder_group_permissions($group, $folder_id, $namespace, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.permissions_change');
        if ($response['status_code'] == 1) return $response;

		if (is_null($this->session))
		{
			return array(
				"status_code" => 1,
				"message" => "Your session is not active"
			);
		}

		/* First check that user trying to add permission can actually do so */
		$folder = KTAPI_Folder::get($this, $folder_id);
		$permissions = $folder->getPermissionAllocation();
		$detail = $permissions->permissions;
		if(!in_array("Manage security", $detail)) {
			return array(
				"status_code" => 1,
				"message" => "User does not have permission to manage security"
			);
		}

		$folder = KTAPI_Folder::get($this, $folder_id);
		if(PEAR::isError($folder))
		{
			return array(
				"status_code" => 1,
				"message" => $folder->getMessage()
			);
		}

		$permission = KTAPI_Permission::getByNamespace($namespace);
		if(PEAR::isError($permission)) {
			return array(
				"status_code" => 1,
				"message" => $permission->getMessage()
			);
		}


		$group = KTAPI_Role::getByName($group);
		if(PEAR::isError($group)) {
			return array(
				"status_code" => 1,
				"message" => $group->getMessage()
			);
		}

		$permissions = $folder->getPermissionAllocation();

		$permissions->add($group, $permission);
		$permissions->save();
	}

	/**
 	* This checks if a user can access an object with a certain permission.
 	*
 	* @author KnowledgeTree Team
	* @access public
 	* @param object $object The internal document object or a folder object
 	* @param string $permission The permissions string
 	* @return object $user SUCCESS - The User object | FAILURE - an error object
 	*/
 	public function can_user_access_object_requiring_permission(&$object, $permission)
 	{
		assert(!is_null($object));
 		assert(is_a($object,'DocumentProxy') || is_a($object,'FolderProxy') || is_a($object,'Document') || is_a($object,'Folder'));
 		/*
        if(is_null($object) || PEAR::isError($object)){
            $error = $object;
            return $object;
        }

        if(!is_a($object,'DocumentProxy') && !is_a($object,'FolderProxy') && !is_a($object,'Document') && !is_a($object,'Folder')){
            $error = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
            return $error;
        }
        */

 		$permissions = &KTAPI::get_permission($permission);
		if (is_null($permissions) || PEAR::isError($permissions))
		{
			$error = $permissions;
			return $error;
		}

 		$user = &KTAPI::get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			$error = $user;
			return $error;
		}

		if (!KTPermissionUtil::userHasPermissionOnItem($user, $permission, $object))
		{
			$error = new PEAR_Error(KTAPI_ERROR_INSUFFICIENT_PERMISSIONS);
			return $error;
		}

		return $user;
 	}

 	/**
 	 * Returns the version id for the associated version number
 	 *
 	 * @param int $document_id
 	 * @param string $version_number
 	 * @return int
 	 */
 	function get_url_version_number($document_id, $version_number) {
 		$ktapi_session = $this->get_session();
		if (is_null($ktapi_session) || PEAR::isError($ktapi_session))
		{
			$error = new PEAR_Error(KTAPI_ERROR_SESSION_INVALID);
			return $error;
		}

		$document_id = sanitizeForSQL($document_id);
		$version_number = sanitizeForSQL($version_number);

		$pos = strpos($version_number, ".");
		$major = substr($version_number, 0, $pos);
		$minor = substr($version_number, ($pos+1));

 		$sql = "SELECT id FROM document_content_version WHERE document_id = {$document_id} AND major_version = '{$major}' AND minor_version = '{$minor}'";
 		$row = DBUtil::getOneResult($sql);
 		$row = (int)$row['id'];
 		if (is_null($row) || PEAR::isError($row))
		{
			$row = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $row);
		}
		return $row;
 	}

 	/**
 	* Search for documents matching the oem_no.
 	*
 	* Note that oem_no is associated with a document and not with version of file (document content).
 	* oem_no is set on a document using document::update_sysdata().
 	*
	* @author KnowledgeTree Team
	* @access public
 	* @param string $oem_no The oem number
 	* @param boolean $idsOnly Defaults to true
 	* @return array|object $results SUCCESS - the list of documents | FAILURE - and error object
 	*/
 	public function get_documents_by_oem_no($oem_no, $idsOnly=true)
 	{
		$sql = array("SELECT id FROM documents WHERE oem_no=?",$oem_no);
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$documentid = $row['id'];

				$results[] = $idsOnly ? $documentid : KTAPI_Document::get($this, $documentid);
			}
		}
 		return $results;
 	}

	/**
	* This returns a session object based on a session id.
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $session The sesssion id
	* @param string $ip The users ip address
	* @param string $app The originating application type Webservices|Webdav|Webapp
	* @return object $session_object SUCCESS - The KTAPI_Session object | FAILURE - an error object
	*/
	public function & get_active_session($session, $ip=null, $app='ws')
	{
		if (!is_null($this->session))
		{
			$error = new PEAR_Error('A session is currently active.');
			return $error;
		}

		$session_object = &KTAPI_UserSession::get_active_session($this, $session, $ip, $app);

		if (is_null($session_object) || PEAR::isError($session_object))
		{
			$error = new PEAR_Error('Session is invalid');
			return $error;
		}

		$this->session = &$session_object;
		return $session_object;
	}

	/**
    * Creates a session and returns the session object based on authentication credentials.
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $username The users username
	* @param string $password The password of the user
	* @param string $ip The users ip address
	* @param string $app The originating application type Webservices|Webdav|Webapp
	* @return object $session SUCCESS - The KTAPI_Session object | FAILURE - an error object
	*/
	public function & start_session($username, $password, $ip=null, $app='ws')
	{
		if (!is_null($this->session))
		{
			$error = new PEAR_Error('A session is currently active.');
			return $error;
		}

		$session = &KTAPI_UserSession::start_session($this, $username, $password, $ip, $app);
		if (is_null($session))
		{
			$error = new PEAR_Error('Session is null.');
			return $error;
		}
		if (PEAR::isError($session))
		{
			$error = new PEAR_Error('Session is invalid. ' . $session->getMessage());
			return $error;
		}

		$this->session = &$session;
		return $session;
	}

	/**
	* start a root session.
	*
	* @author KnowledgeTree Team
	* @access public
	* @return object $session The KTAPI_SystemSession
	*/
	public function & start_system_session($username = null)
	{
		if(is_null($username))
		{
			$user = User::get(1);
		} else {
			$user = User::getByUserName($username);
		}

		if(PEAR::isError($user)) {
			return new PEAR_Error('Username invalid');
		}

		$session = & new KTAPI_SystemSession($this, $user);
		$this->session = &$session;

		return $session;
	}

	/**
	* Starts an anonymous session.
 	*
	* @author KnowledgeTree Team
	* @param string $ip The users ip address
	* @return object $session SUCCESS - The KTAPI_Session object | FAILURE - an error object
	*/
	function &start_anonymous_session($ip=null)
	{
		if (!is_null($this->session))
		{
			$error = new PEAR_Error('A session is currently active.');
			return $error;
		}

		$session = &KTAPI_AnonymousSession::start_session($this, $ip);
		if (is_null($session))
		{
			$error = new PEAR_Error('Session is null.');
			return $error;
		}
		if (PEAR::isError($session))
		{
			$error = new PEAR_Error('Session is invalid. ' . $session->getMessage());
			return $error;
		}

		$this->session = &$session;
		return $session;
	}

	function session_logout()
	{
	    $this->session->logout();
	    $this->session = null;
	}

	/**
	* Gets the root folder.
	* Root folder id is always equal to '1'
	*
	* @author KnowledgeTree Team
	* @access public
	* @return object $folder The KTAPI_Folder object
	*/
	public function &get_root_folder()
	{
		$folder = $this->get_folder_by_id(1);
		return $folder;
	}

	/**
	* Obtains the folder using a folder id.
	*
	* @author KnowledgeTree Team
	* @access public
	* @param integer $folderid The id of the folder
	* @return object $session SUCCESS - The KTAPI_Folder object | FAILURE - an error object
	*/
	public function &get_folder_by_id($folderid)
	{
		if (is_null($this->session))
		{
			$error = new PEAR_Error('A session is not active');
			return $error;
		}

		$folder = KTAPI_Folder::get($this, $folderid);
		return $folder;
	}

    /**
    * Gets the the folder object based on the folder name
    *
    * @author KnowledgeTree Team
    * @access public
	* @param string $foldername The folder name
	* @return object $folder The KTAPI_Folder object
    */
	public function &get_folder_by_name($foldername, $parentId = 1)
	{
		$folder = KTAPI_Folder::_get_folder_by_name($this, $foldername, $parentId);
		return $folder;
	}

	/**
	* This returns a refererence to a document based on document id.
	*
    * @author KnowledgeTree Team
    * @access public
	* @param integer $documentid The document id
	* @return object $document The KTAPI_Document object
	*/
	public function &get_document_by_id($documentid)
	{
		$document = KTAPI_Document::get($this, $documentid);
		return $document;
	}

	/**
    * This returns a document type id based on the name or an error object.
	*
    * @author KnowledgeTree Team
    * @access public
	* @param string $documenttype The document type
	* @return integer|object $result SUCCESS - the document type id | FAILURE - an error object
	*/
	public function get_documenttypeid($documenttype)
	{
		$sql = array("SELECT id FROM document_types_lookup WHERE name=? and disabled=0", $documenttype);
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			$result = new KTAPI_DocumentTypeError(KTAPI_ERROR_DOCUMENT_TYPE_INVALID, $row);
		}
		else
		{
			$result = $row['id'];
		}
		return $result;
	}

	/**
    * Returns the id for a link type or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @param string $linktype The link type
	* @return integer|object $result SUCCESS - the link type id | FAILURE - an error object
	*/
	public function get_link_type_id($linktype)
	{
		$sql = array("SELECT id FROM document_link_types WHERE name=?",$linktype);
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			$result = new PEAR_Error(KTAPI_ERROR_DOCUMENT_LINK_TYPE_INVALID);
		}
		else
		{
			$result = $row['id'];
		}
		return $result;
	}

	/**
    * Returns an array of document types or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @return array|object $results SUCCESS - the array of document types | FAILURE - an error object
	*/
	public function get_documenttypes()
	{
		$sql = "SELECT name FROM document_types_lookup WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$results[] = $row['name'];
			}
		}
		return $results;
	}

	/**
    * Returns an array of document link types or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @return array|object $results SUCCESS - the array of document link types | FAILURE - an error object
	*/
	public function get_document_link_types()
	{
		$sql = "SELECT name FROM document_link_types order by name";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$response['status_code'] = 1;
			if(is_null($rows))
			{
				$response['message'] = "No types";
			} else {
				$response['message'] = $rows->getMessage();
			}

			return $response;
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$results[] = $row['name'];
			}
		}
		$response['status_code'] = 0;
		$response['results'] = $results;
		return $response;
	}

	/**
	* This should actually not be in ktapi, but in webservice
	* This method gets metadata fieldsets based on the document type
	*
    * @author KnowledgeTree Team
    * @access public
	* @param string $document_type The type of document
	* @return mixed Error object|SOAP object|Array of fieldsets
	*/
	public function get_document_type_metadata($document_type='Default')
	{
    	// now get document type specifc ids
    	$typeid =$this->get_documenttypeid($document_type);

    	if (is_a($typeid, 'KTAPI_DocumentTypeError'))
    	{
			return $typeid;
    	}

    	if (is_null($typeid) || PEAR::isError($typeid))
    	{
    		$response['message'] = $typeid->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $response);
    	}

    	$doctype_ids = KTFieldset::getForDocumentType($typeid, array('ids' => false));
    	if (is_null($doctype_ids) || PEAR::isError($doctype_ids))
    	{
    		$response['message'] = $generic_ids->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $response);
    	}

		// first get generic ids
    	$generic_ids = KTFieldset::getGenericFieldsets(array('ids' => false));
    	if (is_null($generic_ids) || PEAR::isError($generic_ids))
    	{
    		$response['message'] = $generic_ids->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $response);
    	}

        // lets merge the results
        $fieldsets = kt_array_merge($generic_ids, $doctype_ids);

		$results = array();

		foreach ($fieldsets as $fieldset)
		{
			if ($fieldset->getIsConditional()) {	/* this is not implemented...*/	continue;	}

			$fields = $fieldset->getFields();
			$result = array(
							'fieldset' => $fieldset->getName(),
							'description' => $fieldset->getDescription()
					  );

			$fieldsresult = array();

			foreach ($fields as $field)
			{
				$value = 'n/a';


				//$controltype = 'string';
                
                // Replace with true
				$controltype = strtolower($field->getDataType());
				if ($field->getHasLookup())
				{
					$controltype = 'lookup';
					if ($field->getHasLookupTree())
					{
						$controltype = 'tree';
					}
				}
                $options = array();
                
                if ($field->getInetLookupType() == 'multiwithcheckboxes' || $field->getInetLookupType() == 'multiwithlist') {
                    $controltype = 'multiselect';
                }
                
                
				switch ($controltype)
				{
					case 'lookup':
						$selection = KTAPI::get_metadata_lookup($field->getId());
						break;
					case 'tree':
						$selection = KTAPI::get_metadata_tree($field->getId());
						break;
                    case 'large text':
                        $options = array(
                                'ishtml' => $field->getIsHTML(),
                                'maxlength' => $field->getMaxLength()
                            );
                        $selection= array();
                        break;
                    case 'multiselect':
                        $selection = KTAPI::get_metadata_lookup($field->getId());
                        $options = array(
                                'type' => $field->getInetLookupType()
                            );
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
					'selection' => $selection,
					'options' => $options
				);
			}
			$result['fields'] = $fieldsresult;
			$results [] = $result;
		}

		return $results;
	}

	/**
    * Returns an array of username/name combinations or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @return array|object $results SUCCESS - the array of all username/name combinations | FAILURE - an error object
	*/
	public function get_users()
	{
		$sql = "SELECT username, name FROM users WHERE disabled=0";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = $rows;
		}
		return $results;
	}

	/**
	* This returns an array for a metadata tree lookup or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @param integer $fieldid The field id to get metadata for
	* @return array|object $results SUCCESS - the array of metedata for the field | FAILURE - an error object
	*/
	public function get_metadata_lookup($fieldid)
	{
		$sql = "SELECT name FROM metadata_lookup WHERE disabled=0 AND document_field_id=$fieldid ORDER BY name";
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$results[] = $row['name'];
			}
		}
		return $results;
	}

	/**
	* This returns a metadata tree or an error object.
	*
    * @author KnowledgeTree Team
	* @access private
	* @param integer $fieldid The field id of the document to get data for
	* @param integer $parentid The id of the parent of the metadata tree
	* @return array|object $results SUCCESS - the array of metadata for the field | FAILURE - an error object
	*/
	private function _load_metadata_tree($fieldid, $parentid=0)
	{
		$results = KTAPI::get_metadata_lookup($fieldid);
		return $results;
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
	* This returns a metadata tree or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @param integer $fieldid The id of the tree field to get the metadata for
	* @return array|object $results SUCCESS - the array of metadata for the field | FAILURE - an error object
	*/
	public function get_metadata_tree($fieldid)
	{
		$results = KTAPI::_load_metadata_tree($fieldid);
		return $results;
	}

	/**
	* Returns a list of active workflows or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @return array|object $results SUCCESS - the array of active workflows | FAILURE - an error object
	*/
	public function get_workflows()
	{
		$sql = "SELECT name FROM workflows WHERE enabled=1";
		$rows=DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results =  new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$results[] = $row['name'];
			}
		}
		return $results;
	}

   /**
	 * Get the users subscriptions
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $filter
	 * @return array of Subscription
	 */
	public function getSubscriptions($filter=null)
	{
	    $user = $this->get_user();
	    $userId = $user->getID();

	    $subscriptions = SubscriptionManager::listSubscriptions($userId);

	    return $subscriptions;
	}

    /**
     * Perform a bulk action on a list of folders and documents
     * Available actions are copy, move, delete, archive, checkout, undo_checkout and immute.
     *
     * <code>
     * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
     *
     * $items = array();
     * $items['documents'][] = $document_id;
     * $items['folders'][] = $folder_id;
     *
     * $response = $ktapi->performBulkAction('move', $items, 'Reason for moving', $target_folder_id);
     * if($response['status_code'] != 0) return 'ERROR';
     *
     * </code>
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $action The action to be performed
     * @param array $items A list of id's and item type in the format array('documents' => array(1,6), 'folders' => array(3,4))
     * @param string $reason The reason for performing the action - only immute does not require a reason.
     * @param integer $target_folder_id The id of the target folder if required - copy and move require this.
     * @return array The response array. On success response['results'] will be empty | contain an array of failed items.
     */
    public function performBulkAction($action, $items, $reason = '', $target_folder_id = null,
                                      $sig_username = '', $sig_password = '')
    {
        // NOTE at the moment this checks for the electronic signature on ANY bulk action
        //      this is fine for now as the only actions defined are:
        //      copy, move, delete, archive, checkout, undo_checkout and immute
        //      ALL of which require signature checking when turned on
        //      IF you are adding more actions. be sure they require signature checking
        //      or EXCLUDE them from the check to prevent them being affected
        $response = $this->_check_electronic_signature($target_folder_id, $sig_username, $sig_password, $reason, $reason,
                                                       'ktcore.transactions.permissions_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;

        if(!is_array($items)){
            $response['message'] = sprintf(_kt("The list of id's must be an array of format array('documents' => array(1,2), 'folders' => array(2,3)). Received: %s") , $items);
            return $response;
        }

        if(empty($items)){
            $response['message'] = _kt('No items found to perform the action on.');
            return $response;
        }

        if(!is_string($action)){
            $response['message'] = sprintf(_kt("The bulk action to perform must be a string. Received: %s") , $action);
            return $response;
        }

        // Check that the action exists in the bulk actions class
        $bulkActions = new ReflectionClass('KTAPI_BulkActions');
        $methods = $bulkActions->getMethods();

        $exists = false;
        foreach ($methods as $method){
            if($method->getName() == $action){
                $actionMethod = $method;
                $exists = true;
                break;
            }
        }

        if(!$exists) {
            $response['message'] = sprintf(_kt("The requested action has not been implemented: %s") , $action);
            return $response;
        }

        // Create the document and folder objects
        $objects = array();
        if(isset($items['folders'])){
            foreach($items['folders'] as $item) {
                $folder = $this->get_folder_by_id($item);
                $objects[] = $folder;
            }
        }

        if(isset($items['documents'])){
            foreach($items['documents'] as $item) {
                $document = $this->get_document_by_id($item);
                $objects[] = $document;
            }
        }

        if(empty($objects)){
            $response['message'] = _kt('No folder or document items found to perform the action on.');
            return $response;
        }

        // perform the action
        $ktapi_bulkactions = new KTAPI_BulkActions($this);

        // Get target folder object if required
        if(in_array($action, array('move', 'copy'))){
            if(!is_int($target_folder_id) || empty($target_folder_id)){
                $response['message'] = _kt('No target folder has been specified.');
                return $response;
            }
            $target = $this->get_folder_by_id($target_folder_id);

            // call the action
            $result = $ktapi_bulkactions->$action($objects, $target, $reason);
        }else if($action == 'immute'){
            // call the action
            $result = $ktapi_bulkactions->$action($objects);
        }else {
            // call the action
            $result = $ktapi_bulkactions->$action($objects, $reason);
        }

        if(PEAR::isError($result)) {
            $response['message'] = sprintf(_kt("The bulk action failed: %s") , $result->getMessage());
            return $response;
        }

        // if failed items are returned - flatten the objects
        if(is_array($result)){
            if(isset($result['docs'])){
                foreach ($result['docs'] as $key => $item){
                    $result['docs'][$key]['object'] = $item['object']->get_detail();
                }
            }
            if(isset($result['folders'])){
                foreach ($result['folders'] as $key => $item){
                    $result['folders'][$key]['object'] = $item['object']->get_detail();
                }
            }
        }

        // For a successful action
        $response['status_code'] = 0;
        $response['results'] = $result;
        return $response;
    }

    /* *** ACL Roles and Role_Allocation *** */

    /**
     * Get a list of available roles
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $filter The beginning letter(s) of the role being searched for
     * @return array Response.
     */
    public function get_roles($filter = null)
    {
        $response['status_code'] = 1;

        // check the filter
        if(!empty($filter)) {
            if(!is_string($filter)){
                $response['message'] = _kt('Filter should be a string.');
                return $response;
            }

            // escape filter string - prevent sql injection
            $filter = addslashes($filter);
            $filter = "name like '{$filter}%'";
        }

        $listing = KTAPI_Role::getList($filter);

        if(PEAR::isError($listing)){
            $response['message'] = $listing->getMessage();
            return $response;
        }

        // flatten role objects
        $roles = array();
        foreach ($listing as $ktapi_roll) {
            $roles[] = array(
               'id' => $ktapi_roll->getId(),
               'name' => $ktapi_roll->getName(),
            );
        }

        $response['status_code'] = 0;
        $response['results'] = $roles;
        return $response;
    }

    /**
     * Get a role using its id
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $role_id The id of the role
     * @return array Response
     */
    public function get_role_by_id($role_id)
    {
        $response['status_code'] = 1;
        if(!is_numeric($role_id)){
            $response['message'] = _kt('Role id must be numeric.');
            return $response;

        }

        $role = KTAPI_Role::getById($role_id);

        if(PEAR::isError($role)) {
            $response['message'] = $role->getMessage();
            return $response;
        }

        $response['status_code'] = 0;
        $response['results'] = array(
           'id' => $role->getId(),
           'name' => $role->getName()
        );

       return $response;
    }

    /**
     * Get a role based on its name
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $role_name The name of the role
     * @return array Response
     */
    public function get_role_by_name($role_name)
    {
        $response['status_code'] = 1;
        if(!is_string($role_name)){
            $response['message'] = _kt('Role name must be a string.');
            return $response;

        }

        $role = KTAPI_Role::getByName($role_name);

        if(PEAR::isError($role)) {
            $response['message'] = $role->getMessage();
            return $response;
        }

        $response['status_code'] = 0;
        $response['results'] = array(
           'id' => $role->getId(),
           'name' => $role->getName()
        );

       return $response;
    }

    /**
     * Get the list of role allocations on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integar $folder_id The id of the folder
     * @return array Response
     */
    public function get_role_allocation_for_folder($folder_id)
    {
        $response['status_code'] = 1;
        if(!is_numeric($folder_id)){
            $response['message'] = _kt('Folder id must be numeric.');
            return $response;

        }

        $folder = $this->get_folder_by_id($folder_id);

        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        $role_allocation = $folder->getRoleAllocation();

        // flatten object
        $membership = $role_allocation->getMembership();

        $response['status_code'] = 0;
        $response['results'] = $membership;
        return $response;
    }

    /**
     * Add a user to a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param integer $user_id The id of the user to be added
     * @return array Response
     */
    public function add_user_to_role_on_folder($folder_id, $role_id, $user_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;
        if(!is_numeric($user_id)){
            $response['message'] = _kt('User id must be numeric.');
            return $response;
        }
        $member['users'][] = $user_id;

        return $this->add_members_to_role_on_folder($folder_id, $role_id, $member, $sig_username, $sig_password, $reason);
    }

    /**
     * Add a group to a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param integer $group_id The id of the group to be added
     * @return array Response
     */
    public function add_group_to_role_on_folder($folder_id, $role_id, $group_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;
        if(!is_numeric($group_id)){
            $response['message'] = _kt('Group id must be numeric.');
            return $response;
        }
        $member['groups'][] = $group_id;

        return $this->add_members_to_role_on_folder($folder_id, $role_id, $member, $sig_username, $sig_password, $reason);
    }

    /**
     * Remove a user from a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param integer $user_id The id of the user to be removed
     * @return array Response
     */
    public function remove_user_from_role_on_folder($folder_id, $role_id, $user_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;
        if(!is_numeric($user_id)){
            $response['message'] = _kt('User id must be numeric.');
            return $response;
        }
        $member['users'][] = $user_id;

        return $this->remove_members_from_role_on_folder($folder_id, $role_id, $member, $sig_username, $sig_password, $reason);
    }

    /**
     * Remove a group from a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param integer $group_id The id of the group to be removied
     * @return array Response
     */
    public function remove_group_from_role_on_folder($folder_id, $role_id, $group_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;
        if(!is_numeric($group_id)){
            $response['message'] = _kt('Group id must be numeric.');
            return $response;
        }
        $member['groups'][] = $group_id;

        return $this->remove_members_from_role_on_folder($folder_id, $role_id, $member, $sig_username, $sig_password, $reason);
    }

    /**
     * Remove members (user, group) from a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param array $members The list of id's of members to be removed - array('users' => array(1,2), 'groups' => array(2,4))
     * @return array Response
     */
    public function remove_members_from_role_on_folder($folder_id, $role_id, $members, $sig_username = '', $sig_password = '', $reason = '')
    {
        return $this->update_members_on_role_on_folder($folder_id, $role_id, $members, 'remove', $sig_username, $sig_password, $reason);
    }

    /**
     * Add members (user, group) to a role on a folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param array $members The list of id's of members to be added - array('users' => array(1,2), 'groups' => array(2,4))
     * @return array Response
     */
    public function add_members_to_role_on_folder($folder_id, $role_id, $members, $sig_username = '', $sig_password = '', $reason = '')
    {
        return $this->update_members_on_role_on_folder($folder_id, $role_id, $members, 'add', $sig_username, $sig_password, $reason);
    }

    /**
     * Add / remove members (user, group) to / from a role on a folder
     *
     * @author KnowledgeTree Team
     * @access private
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being modified
     * @param array $members The list of id's of members to be updated - array('users' => array(1,2), 'groups' => array(2,4))
     * @param string $update The type of modification - add | remove
     * @return array Response
     */
    private function update_members_on_role_on_folder($folder_id, $role_id, $members, $update = 'add',
                                                      $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        // Check input information
        $response['status_code'] = 1;
        if(!is_numeric($folder_id)){
            $response['message'] = _kt('Folder id must be numeric.');
            return $response;
        }

        if(!is_numeric($role_id)){
            $response['message'] = _kt('Role id must be numeric.');
            return $response;
        }

        if(!is_array($members)){
            $response['message'] = _kt("The list of members must be in the format: array('users' => array(1,2), 'groups' => array(2,4)).')");
            return $response;
        }

        if(!isset($members['users']) && !isset($members['groups'])){
            $response['message'] = _kt("The list of members must be in the format: array('users' => array(1,2), 'groups' => array(2,4)).')");
            return $response;
        }

        // Get folder and role objects
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        $role = KTAPI_Role::getById($role_id);
        if(PEAR::isError($role)) {
            $response['message'] = $role->getMessage();
            return $response;
        }

        // Get the role allocation for the folder
        $role_allocation = $folder->getRoleAllocation();

        // Get member objects and add them to the role
        // Users
        if(isset($members['users'])){

            foreach($members['users'] as $user_id){
                // Get the user object
                $member = KTAPI_User::getById($user_id);

                if(PEAR::isError($member)) {
                    $response['message'] = $member->getMessage();
                    return $response;
                }

                // Add to / remove from the role
                $role_allocation->$update($role, $member);
            }
        }

        // Groups
        if(isset($members['groups'])){

            foreach($members['groups'] as $group_id){
                // Get the group object
                $member = KTAPI_Group::getById($group_id);

                if(PEAR::isError($member)) {
                    $response['message'] = $member->getMessage();
                    return $response;
                }

                // Add to / remove from the role
                $role_allocation->$update($role, $member);
            }
        }

        // Save the new allocations
        $role_allocation->save();

        $response['status_code'] = 0;
        return $response;
    }

    /**
     * Check if a user or group is allocated to a role on the folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id The id of the role being checked
     * @param integer $member_id The id of the user or group
     * @param string $member_type user | group
     * @return array Response
     */
    public function is_member_in_role_on_folder($folder_id, $role_id, $member_id, $member_type = 'user')
    {
        $response['status_code'] = 1;

        // Get folder and role objects
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        $role = KTAPI_Role::getById($role_id);
        if(PEAR::isError($role)) {
            $response['message'] = $role->getMessage();
            return $response;
        }

        // get the member object
        switch($member_type){
            case 'user':
                $member = KTAPI_User::getById($member_id);
                break;
            case 'group':
                $member = KTAPI_Group::getById($member_id);
                break;
            default:
                $response['message'] = _kt('Unrecognised member type. Must be group or user.');
               return $response;
        }

        if(PEAR::isError($member)) {
            $response['message'] = $member->getMessage();
            return $response;
        }

        // Get the role allocation for the folder
        $role_allocation = $folder->getRoleAllocation();
        $check = $role_allocation->doesRoleHaveMember($role, $member);
        $result = ($check) ? 'YES' : 'NO';

        $response['status_code'] = 0;
        $response['results'] = $result;
        return $response;
    }

    /**
     * Removes all members (users, groups) from all roles or from the specified role on the folder
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @param integer $role_id Optional. The id of the role being reset.
     * @return array Response
     */
    public function remove_all_role_allocation_from_folder($folder_id, $role_id = null, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;

        // Get folder and role objects
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        $role = null;
        if(!empty($role_id)){
            $role = KTAPI_Role::getById($role_id);
            if(PEAR::isError($role)) {
                $response['message'] = $role->getMessage();
                return $response;
            }
        }

        // Get the role allocation for the folder
        $role_allocation = $folder->getRoleAllocation();
        $role_allocation->removeAll($role);
        $role_allocation->save();

        $response['status_code'] = 0;
        $response['results'] = $result;
        return $response;
    }

    /**
     * Overrides the parents role allocation
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @return array Response
     */
    public function override_role_allocation_on_folder($folder_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;

        // Get folder object
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        // Get the role allocation for the folder
        $role_allocation = $folder->getRoleAllocation();
        $result = $role_allocation->overrideAllocation();

        $response['status_code'] = 0;
        $response['results'] = $result;
        return $response;
    }

    /**
     * Inherits the role allocation from the parent
     *
     * @author KnowledgeTree Team
     * @access public
     * @param integer $folder_id The folder id
     * @return array Response
     */
    public function inherit_role_allocation_on_folder($folder_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.role_allocations_change');
        if ($response['status_code'] == 1) return $response;

        $response['status_code'] = 1;

        // Get folder object
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)) {
            $response['message'] = $folder->getMessage();
            return $response;
        }

        // Get the role allocation for the folder
        $role_allocation = $folder->getRoleAllocation();
        $result = $role_allocation->inheritAllocation();

        $response['status_code'] = 0;
        $response['results'] = $result;
        return $response;
    }


    /* *** Refactored web services functions *** */

    /**
     * Creates a new anonymous session.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $ip The users IP address
     * @return array Response 'results' contain the session id | 'message' contains the error message on failure
     */
    function anonymous_login($ip=null)
    {
        $session = $this->start_anonymous_session($ip);
        if(PEAR::isError($session)){
    	    $response['status_code'] = 1;
    	    $response['message']= $session->getMessage();
    	    return $response;
        }

        $session= $session->get_session();
        $response['results'] = $session;
        $response['message'] = '';

        $response['status_code'] = 0;
        return $response;
    }

    /**
     * Creates a new session for the user.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $username The users username
     * @param string $password The users password
     * @param string $ip Optional. The users IP address
     * @return array Response 'results' contain the session id | 'message' contains the error message on failure
     */
    function login($username, $password, $ip=null)
    {
        $session = $this->start_session($username,$password, $ip);
        if(PEAR::isError($session)){
    	    $response['status_code'] = 1;
    	    $response['message']= $session->getMessage();
    	    return $response;
        }

        $session = $session->get_session();
        $response['status_code'] = 0;
        $response['message'] = '';
        $response['results'] = $session;
        return $response;
    }

    /**
     * Closes an active session.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @return array Response Empty on success | 'message' contains the error message on failure
     */
    function logout()
    {
        $session = &$this->get_session();
        if(PEAR::isError($session)){
    	    $response['status_code'] = 1;
    	    $response['message']= $session->getMessage();
    	    return $response;
        }
        $session->logout();

        $response['status_code'] = 0;
        return $response;
    }

    /**
     * Returns the folder details for a given folder id.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder
     * @return array Response 'results' contains kt_folder_detail | 'message' contains error message on failure
     */
    function get_folder_detail($folder_id)
    {
    	$folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}
        $response['status_code'] = 0;
        $response['message'] = '';
        $response['results'] = $folder->get_detail();
    	return $response;
    }

    /**
     * Retrieves all shortcuts linking to a specific folder
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder
     * @return array Response 'results' contains kt_folder_shortcuts | 'message' contains error message on failure
     *
     */
    function get_folder_shortcuts($folder_id)
    {
        $folder = $this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)){
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
        }

        $shortcuts = $folder->get_shortcuts();
    	if(PEAR::isError($shortcuts)){
    	    $response['status_code'] = 1;
    	    $response['message']= $shortcuts->getMessage();
    	    return $response;
    	}

        $response['status_code'] = 0;
        $response['message'] = '';
        $response['results'] = $shortcuts;
    	return $response;
    }

    /**
     * Returns folder detail given a folder name which could include a full path.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $folder_name The name of the folder
     * @return array Response 'results' contains kt_folder_detail | 'message' contains error message on failure
     */
    function get_folder_detail_by_name($folder_name)
    {
        $folder = &$this->get_folder_by_name($folder_name);
        if(PEAR::isError($folder)){
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
        }

        $response['status_code'] = 0;
        $response['message'] = '';
        $response['results'] = $folder->get_detail();
        return $response;
    }

    /**
     * Returns the contents of a folder - list of contained documents and folders
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder
     * @param integer $depth The depth to display - 1 = direct contents, 2 = include contents of the contained folders, etc
     * @param string $what Filter on what should be returned, takes a combination of the following: D = documents, F = folders, S = shortcuts
     * @return array Response 'results' contains kt_folder_contents | 'message' contains error message on failure
     */
    function get_folder_contents($folder_id, $depth=1, $what='DFS')
    {
        $folder = &$this->get_folder_by_id($folder_id);
        if(PEAR::isError($folder)){
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
        }
        $listing = $folder->get_listing($depth, $what);

    	$contents = array(
    		'folder_id' => $folder_id+0,
    		'folder_name'=>$folder->get_folder_name(),
    		'full_path'=>$folder->get_full_path(),
    		'items'=>$listing
    	);

    	$response['status_code'] = 0;
    	$response['message'] = '';
    	$response['results'] = $contents;

    	return $response;
    }

    /**
     * Creates a new folder inside the given folder
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the parent folder
     * @param string $folder_name The name of the new folder
     * @return array Response 'results' contains kt_folder_detail | 'message' contains error message on failure
     */
    function create_folder($folder_id, $folder_name, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.add');
        if ($response['status_code'] == 1) return $response;

        $folder = &$this->get_folder_by_id($folder_id);
    	if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}
    	$newfolder = $folder->add_folder($folder_name);
    	if (PEAR::isError($newfolder))
        {
            $response['status_code'] = 1;
    	    $response['message']= $newfolder->getMessage();
    	    return $response;
        }
    	$response['status_code'] = 0;
    	$response['message'] = '';
        $response['results'] = $newfolder->get_detail();
    	return $response;
    }

    /**
     * Creates a shortcut to an existing folder
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $target_folder_id Id of the folder containing the shortcut.
     * @param integer $source_folder_id Id of the folder to which the shortcut will point.
     * @return array Response 'results' contains kt_shortcut_detail | 'message' contains error message on failure
     */
    function create_folder_shortcut($target_folder_id, $source_folder_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.create_shortcut');
        if ($response['status_code'] == 1) return $response;

        $folder = &$this->get_folder_by_id($target_folder_id);
    	if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}

    	$source_folder = &$this->get_folder_by_id($source_folder_id);
    	if (PEAR::isError($source_folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $source_folder->getMessage();
    	    return $response;
    	}

    	$shortcut = &$folder->add_folder_shortcut($source_folder_id);
    	if (PEAR::isError($shortcut))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $shortcut->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;
    	$response['message'] = '';
    	$response['results'] = $shortcut->get_detail();
    	return $response;
    }

	/**
     * Creates a shortcut to an existing document
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $target_folder_id Id of the parent folder containing the shortcut
     * @param integer $source_document_id Id of the document to which the shortcut will point
     * @return array Response 'results' contains kt_document_detail | 'message' contains error message on failure
     */
    function create_document_shortcut($target_folder_id, $source_document_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.create_shortcut');
        if ($response['status_code'] == 1) return $response;

        $folder = &$this->get_folder_by_id($target_folder_id);
    	if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}

    	$source_document = &$this->get_document_by_id($source_document_id);
    	if (PEAR::isError($source_document))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $source_document->getMessage();
    	    return $response;
    	}

    	$shortcut = &$folder->add_document_shortcut($source_document_id);
    	if (PEAR::isError($shortcut))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $shortcut->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;
    	$response['message'] = '';
    	$response['results'] = $shortcut->get_detail();
    	return $response;
    }

    /**
     * Deletes a folder.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder to delete
     * @param string $reason The reason for performing the deletion
     * @return array Response | 'message' contains error message on failure
     */
    function delete_folder($folder_id, $reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.delete');
        if ($response['status_code'] == 1) return $response;

        $folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}

    	$result = $folder->delete($reason);
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;
    	return $response;
    }

    /**
     * Renames a folder.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder
     * @param string $newname The new name of the folder
     * @return array Response | 'message' contains error message on failure
     */
    function rename_folder($folder_id, $newname, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($folder_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.rename');
        if ($response['status_code'] == 1) return $response;

        $folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $folder->getMessage();
    	    return $response;
    	}
    	$result = $folder->rename($newname);
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;
    	return $response;
    }

    /**
     * Makes a copy of a folder in another location.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $sourceid The id of the folder to be copied
     * @param integer $targetid The id of the folder in which the copy should be placed
     * @param string $reason The reason for performing the copy
     * @return array Response | 'message' contains error message on failure
     */
    function copy_folder($source_id, $target_id, $reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($source_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.copy');
        if ($response['status_code'] == 1) return $response;

    	$src_folder = &$this->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $src_folder->getMessage();
    	    return $response;
    	}

    	$tgt_folder = &$this->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $tgt_folder->getMessage();
    	    return $response;
    	}

    	$result= $src_folder->copy($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;

    	if($this->version >= 2){
        	$sourceName = $src_folder->get_folder_name();
        	$targetPath = $tgt_folder->get_full_path();

        	$response['results'] = $this->get_folder_detail_by_name($targetPath . '/' . $sourceName);
        	return $response;
    	}

		return $response;
    }

    /**
     * Moves a folder to another location.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $sourceid The id of the folder to be moved
     * @param integer $targetid The id of the folder into which the folder should be moved
     * @param string $reason The reason for performing the move
     * @return array Response | 'message' contains error message on failure
     */
    function move_folder($source_id, $target_id, $reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($source_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.move');
        if ($response['status_code'] == 1) return $response;

    	$src_folder = &$this->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $src_folder->getMessage();
    	    return $response;
    	}

    	$tgt_folder = &$this->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $tgt_folder->getMessage();
    	    return $response;
    	}

    	$result = $src_folder->move($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;

    	if($this->version >= 2){
        	$response['results'] = $this->get_folder_detail($source_id);
    		return $response;
    	}

		return $response;

    }

    /**
     * Returns a list of document types.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @return array Response 'results' contain kt_document_types_response | 'message' contains error message on failure
     */
    public function get_document_types()
    {
    	$result = $this->get_documenttypes();
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();
    	    return $response;
    	}

   		$response['status_code']= 0;
   		$response['results']= $result;

    	return $response;

    }

    /**
     * Returns a list of document link types - Attachment, Reference, etc
     *
     * @return array Response 'results' contain kt_document_link_types_response | 'message' contains error message on failure
     */
    public function get_document_link_types_list()
    {
    	$result = $this->get_document_link_types();
    	if (PEAR::isError($result))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $result->getMessage();

    		return $response;
    	}

   		$response['status_code']= 0;
   		$response['results'] = $result;

    	return $response;

    }

    /**
     * Returns document details given a document_id.
     * Details can be filtered using a combination of the following: M = metadata, L = links, T = workflow transitions,
     * V = version history, H = transaction history
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $document_id The id of the document
     * @param string $detailstr Optional. Filter on the level of detail to return.
     * @return array Response 'results' contain kt_document_detail | 'message' contains error message on failure
     */
    public function get_document_detail($document_id, $detailstr='')
    {
        $document = $this->get_document_by_id($document_id);
    	if (PEAR::isError($document))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $document->getMessage();
    	    return $response;
    	}

    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    	    $response['status_code'] = 1;
    	    $response['message']= $detail->getMessage();
    	    return $response;
    	}

    	$response['status_code'] = 0;
    	$response['message'] = '';

    	if ($this->version >= 2)
    	{
    		$detail['metadata'] = array();
    		$detail['links'] = array();
    		$detail['transitions'] = array();
    		$detail['version_history'] = array();
    		$detail['transaction_history'] = array();

    		if (stripos($detailstr,'M') !== false)
    		{
    			$response = $this->get_document_metadata($document_id);
    			$detail['metadata'] = $response['results'];
    			$detail['name'] = 'metadata';
    		}

    		if (stripos($detailstr,'L') !== false)
    		{
    			$response = $this->get_document_links($document_id);
    			$detail['links'] = $response['results'];
    			$detail['name'] = 'links';
    		}

    		if (stripos($detailstr,'T') !== false)
    		{
    			$response = $this->get_document_workflow_transitions($document_id);
    			$detail['transitions'] =  $response['results'] ;
    			$detail['name'] = 'transitions';
    		}

    		if (stripos($detailstr,'V') !== false)
    		{
    			$response = $this->get_document_version_history($document_id);
    			$detail['version_history'] =  $response['results'];
    			$detail['name'] = 'version_history';
    		}

    		if (stripos($detailstr,'H') !== false)
    		{
    			$response = $this->get_document_transaction_history($document_id);
    			$detail['transaction_history'] =  $response['results'];
    			$detail['name'] = 'transaction_history';
    		}
    	}

    	$response['results'] = $detail;
    	return $response;
    }

    /**
     * Returns the document details given the filename of the document
     * Details can be filtered using a combination of the following: M = metadata, L = links, T = workflow transitions,
     * V = version history, H = transaction history
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param integer $folder_id The id of the folder in which to find the document
     * @param string $filename The filename of the document
     * @param string $detail Optional. Filter on the level of detail to return.
     * @return array Response 'results' contain kt_document_detail | 'message' contains error message on failure
     */
    public function get_document_detail_by_filename($folder_id, $filename, $detail='')
    {
    	return $this->get_document_detail_by_name($folder_id, $filename, 'F', $detail);
    }

    /**
     * Returns the document details give the title of the document
     * Details can be filtered using a combination of the following: M = metadata, L = links, T = workflow transitions,
     * V = version history, H = transaction history
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param interger $folder_id The id of the folder in which to find the document
     * @param string $title The title of the document
     * @param string $detail Optional. Filter on the level of detail to return.
     * @return array Response 'results' contain kt_document_detail | 'message' contains error message on failure
     */
    public function get_document_detail_by_title($folder_id, $title, $detail='')
    {
    	return $this->get_document_detail_by_name($folder_id,  $title, 'T', $detail);
    }

    /**
     * Returns document detail given a document name which could include a full path.
     * Details can be filtered using a combination of the following: M = metadata, L = links, T = workflow transitions,
     * V = version history, H = transaction history
     *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param integer $folder_id The id of the folder in which to find the document
     * @param string $document_name The name of the document
     * @param string @what Optional. Defaults to T. The type of name - F = filename or T = title
     * @param string $detail Optional. Filter on the level of detail to return.
     * @return array Response 'results' contain kt_document_detail | 'message' contains error message on failure
     */
    public function get_document_detail_by_name($folder_id, $document_name, $what='T', $detail='')
    {
        $response['status_code'] = 1;
    	if (empty($document_name))
    	{
    		$response['message'] = 'Document_name is empty.';
    		return $response;
    	}

    	if (!in_array($what, array('T','F')))
    	{
    		$response['message'] = 'Invalid what code';
    		return $response;
    	}

    	if ($folder_id < 1) $folder_id = 1;
    	$root = &$this->get_folder_by_id($folder_id);
    	if (PEAR::isError($root))
    	{
    		$response['message'] = $root->getMessage();
    		return $response;
    	}

    	if ($what == 'T')
    	{
    		$document = &$root->get_document_by_name($document_name);
    	}
    	else
    	{
    		$document = &$root->get_document_by_filename($document_name);
    	}
    	if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;
    	}

    	return $this->get_document_detail($document->documentid, $detail);
    }

    /**
     * Returns the role allocation on the document
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @author KnowledgeTree Team
     * @access public
     * @param integer $document_id The id of the document
     * @return array Response
     */
    public function get_role_allocation_for_document($document_id)
    {
        $document = $this->get_document_by_id($document_id);
        if(PEAR::isError($document)){
            $response['status_code'] = 1;
            $response['message'] = $document->getMessage();
            return $response;
        }

        $allocation = $document->getRoleAllocation();

        $response['status_code'] = 0;
        $response['results'] = $allocation->getMembership();
        return $response;
    }

    /**
     * Emails a document as an attachment or hyperlink to a list of users, groups or external email addresses.
	 * In the case of external addresses, if a hyperlink is used then a timed download link (via webservices) is sent allowing the recipient a window period in which to download the document.
	 * The period is set through the webservices config option webservice/downloadExpiry. Defaults to 30 minutes.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $document_id The id of the document
	 * @param array $members The email recipients (users, groups, external) in the format: array('users' => array(1,2), 'groups' => array(3,1), 'external' => array('name@email.com'))
	 * @param string $comment Content to be appended to the email
	 * @param bool $attach TRUE if document is an attachment | FALSE if using a hyperlink to the document
     * @return array Response
     */
    public function email_document($document_id, $members, $content = '', $attach = true)
    {
        $response['status_code'] = 1;
        if(!isset($members['users']) && !isset($members['groups']) && !isset($members['external'])){
            $response['message'] = _kt("No recipients were provided. The list of recipients should be in the format: array('users' => array(1,2), 'groups' => array(3,1), 'external' => array('name@email.com')).");
            return $response;
        }

        $document = $this->get_document_by_id($document_id);
        if(PEAR::isError($document)){
            $response['message'] = $document->getMessage();
            return $response;
        }

        $recipients = array();

        // Get member objects and add them to the role
        // Users
        if(isset($members['users'])){
            foreach($members['users'] as $user_id){
                // Get the user object
                $member = KTAPI_User::getById($user_id);

                if(PEAR::isError($member)) {
                    $response['message'] = $member->getMessage();
                    return $response;
                }

                // Add to recipients list
                $recipients[] = $member;
            }
        }

        // Groups
        if(isset($members['groups'])){
            foreach($members['groups'] as $group_id){
                // Get the group object
                $member = KTAPI_Group::getById($group_id);

                if(PEAR::isError($member)) {
                    $response['message'] = $member->getMessage();
                    return $response;
                }

                // Add to recipients list
                $recipients[] = $member;
            }
        }

        // External recipients
        if(isset($members['external'])){
            foreach ($members['external'] as $email_address){
                // Add to recipients list
                $recipients[] = $member;
            }
        }

        $result = $document->email($recipients, $content, $attach);
        
        if (PEAR::isError($result)) {
            $response['message'] = $result->getMessage();
            return $response;
        }

        $response['status_code'] = 0;
        return $response;
    }

    /**
     * Retrieves all shortcuts linking to a specific document
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param ing $document_id
	 * @return kt_document_shortcuts.
     *
     */
    public function get_document_shortcuts($document_id)
    {
    	$document = $this->get_document_by_id($document_id);
    	if(PEAR::isError($document)){
    	    $response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
    		return $response;
    	}

    	$shortcuts = $document->get_shortcuts();
    	if(PEAR::isError($shortcuts)){
    	    $response['status_code'] = 1;
    		$response['message'] = $shortcuts->getMessage();
    		return $response;
    	}

	    $response['status_code'] = 0;
	    $response['message'] = '';
	    $response['results'] = $shortcuts;
    	return $response;
    }

    /**
     * Adds a document to the repository.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $tempfilename
     * @return kt_document_detail.
     */
    public function add_document($folder_id,  $title, $filename, $documenttype, $tempfilename,
                                 $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.add');
        if ($response['status_code'] == 1) return $response;

		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
    	    $response['status_code'] = 1;
    		$response['message'] = "Invalid temporary file: $tempfilename. Not compatible with $upload_manager->temp_dir.";
    		return $response;
    	}

    	$folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
    	    $response['status_code'] = 1;
    		$response['message'] = $folder->getMessage();
    		return $response;
		}

    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
    	    $response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
    		return $response;
		}

    	$response['status_code'] = 0;
		$response['message'] = '';
		$response['results'] = $document->get_detail();
    	return $response;
    }

    public function add_small_document_with_metadata($folder_id,  $title, $filename, $documenttype, $base64, $metadata, $sysdata,
                                                     $sig_username = '', $sig_password = '', $reason = '')
    {
		$add_result = $this->add_small_document($folder_id, $title, $filename, $documenttype, $base64,
                                                $sig_username, $sig_password, $reason);

		if($add_result['status_code'] != 0){
		    return $add_result;
		}

		$document_id = $add_result['results']['document_id'];

		$update_result = $this->update_document_metadata($document_id, $metadata, $sysdata, $sig_username, $sig_password, $reason);
		if($update_result['status_code'] != 0){
		    $this->delete_document($document_id, 'Rollback because metadata could not be added', false);
			return $update_result;
		}

    	$document = $this->get_document_by_id($document_id);
    	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
		$result = $document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		return $update_result;
    }

    public function add_document_with_metadata($folder_id,  $title, $filename, $documenttype, $tempfilename, $metadata, $sysdata,
                                               $sig_username = '', $sig_password = '', $reason = '')
    {
		$add_result = $this->add_document($folder_id, $title, $filename, $documenttype, $tempfilename,
                                          $sig_username, $sig_password, $reason);

		if($add_result['status_code'] != 0){
		    return $add_result;
		}

		$document_id = $add_result['results']['document_id'];

		$update_result = $this->update_document_metadata($document_id, $metadata, $sysdata, $sig_username, $sig_password, $reason);
		if($update_result['status_code'] != 0){
		    $this->delete_document($document_id, 'Rollback because metadata could not be added', false);
		    return $update_result;
		}

    	$document = $this->get_document_by_id($document_id);
    	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		$result = $document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		return $update_result;
    }


    /**
     * Find documents matching the document oem (integration) no
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $oem_no
     * @param string $detail
     * @return kt_document_collection_response
     */
	public function get_documents_detail_by_oem_no($oem_no, $detail)
	{
    	$documents = $this->get_documents_by_oem_no($oem_no);

    	$collection = array();
    	foreach($documents as $documentId)
    	{
			$detail = $this->get_document_detail($documentId, $detail);
			if ($detail['status_code'] != 0)
			{
				continue;
			}
			$collection[] = $detail->value;
    	}

    	$response=array();
    	$response['status_code'] = 0;
		$response['message'] = empty($collection) ? _kt('No documents were found matching the specified document no') : '';
    	$response['results'] = $collection;
    	return $collection;
	}

    /**
     * Adds a document to the repository.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $base64
     * @return kt_document_detail.
     */
    public function add_small_document($folder_id,  $title, $filename, $documenttype, $base64,
                                       $sig_username = '', $sig_password = '', $reason = '')
    {
    	$folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response['status_code'] = 1;
			$response['message'] = $folder->getMessage();
			return $response;
		}

		$upload_manager = new KTUploadManager();
    	$tempfilename = $upload_manager->store_base64_file($base64);
    	if (PEAR::isError($tempfilename))
    	{
    		$reason = $tempfilename->getMessage();
    		$response['status_code'] = 1;
    		$response['message'] = 'Cannot write to temp file: ' . $tempfilename . ". Reason: $reason";
			return $response;
    	}

		// simulate the upload
		$tempfilename = $upload_manager->uploaded($filename,$tempfilename, 'A');

		// add the document
    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
		}

    	$response['status_code'] = 0;
    	$response['message'] = '';
    	$response['results'] = $document->get_detail();
    	return $response;
    }

    /**
     * Does a document checkin.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $tempfilename
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    public function checkin_document($document_id, $filename, $reason, $tempfilename, $major_update,
                                     $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_in');
        if ($response['status_code'] == 1) return $response;

    	// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
    	    $response['status_code'] = 1;
			$response['message'] = 'Invalid temporary file';
			return $response;
    	}

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
    	    $response['status_code'] = 1;
			$response['message'] = $document->getMessage();
			return $response;
		}

		// checkin
		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
    	    $response['status_code'] = 1;
			$response['message'] = $result->getMessage();
			return $response;
		}

    	// get status after checkin
		return $this->get_document_detail($document_id);
    }

    public function  checkin_small_document_with_metadata($document_id,  $filename, $reason, $base64, $major_update,
                                                          $metadata, $sysdata, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_in');
        if ($response['status_code'] == 1) return $response;

       	$add_result = $this->checkin_small_document($document_id,  $filename, $reason, $base64, $major_update,
                                                    $sig_username, $sig_password);

       	if($add_result['status_code'] != 0){
       		return $add_result;
       	}

       	$update_result = $this->update_document_metadata($document_id, $metadata, $sysdata, $sig_username, $sig_password, $reason);

       	if($update_result['status_code'] != 0){
       		return $update_result;
       	}

       	$document = $this->get_document_by_id($document_id);
       	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
       	$result = $document->mergeWithLastMetadataVersion();
       	if (PEAR::isError($result))
       	{
       		// not much we can do, maybe just log!
       	}

       	return $update_result;
	}

    public function  checkin_document_with_metadata($document_id,  $filename, $reason, $tempfilename, $major_update,
                                                    $metadata, $sysdata, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_in');
        if ($response['status_code'] == 1) return $response;

       	$add_result = $this->checkin_document($document_id,  $filename, $reason, $tempfilename, $major_update,
                                              $sig_username, $sig_password);

       	if($add_result['status_code'] != 0){
       		return $add_result;
       	}

       	$update_result = $this->update_document_metadata($session_id, $document_id, $metadata, $sysdata, $sig_username, $sig_password, $reason);
       	if($update_result['status_code'] != 0){
       		return $update_result;
       	}

       	$document = $this->get_document_by_id($document_id);
       	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
       	$result = $document->mergeWithLastMetadataVersion();
       	if (PEAR::isError($result))
       	{
       		// not much we can do, maybe just log!
       	}

       	return $update_result;
	}

    /**
     * Does a document checkin.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $filename
     * @param string $reason
     * @param string $base64
     * @param boolean $major_update
     * @return kt_document_detail.
     */
    public function checkin_small_document($document_id,  $filename, $reason, $base64, $major_update, $sig_username = '', $sig_password = '' )
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_in');
        if ($response['status_code'] == 1) return $response;

    	$upload_manager = new KTUploadManager();
    	$tempfilename = $upload_manager->store_base64_file($base64, 'su_');
    	if (PEAR::isError($tempfilename))
    	{
    		$reason = $tempfilename->getMessage();
    		$response['status_code'] = 1;
    		$response['message'] = 'Cannot write to temp file: ' . $tempfilename . ". Reason: $reason";
			return $response;
    	}

    	// simulate the upload
		$tempfilename = $upload_manager->uploaded($filename,$tempfilename, 'C');

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
		}

		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
		}
		// get status after checkin
		return $this->get_document_detail($document_id);
    }

    /**
     * Does a document checkout.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $reason
     * @return kt_document_detail.
     */
    public function checkout_document($document_id, $reason, $download = true, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_out');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->checkout($reason);
        
        if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$session = &$this->get_session();

    	$url = '';
    	if ($download)
    	{
	    	$download_manager = new KTDownloadManager();
    		$download_manager->set_session($session->session);
    		$download_manager->cleanup();
    		$url = $download_manager->allow_download($document);
    	}

		if ($this->version >= 2)
		{
			$response = $this->get_document_detail($document_id);
			$response['results']['url'] = $url;

			return $response;
		}

    	$response['status_code'] = 0;
		$response['message'] = '';
		$response['results'] = $url;

    	return $response;
    }

    /**
     * Does a document checkout.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $reason
     * @param boolean $download
     * @return kt_document_detail
     */
    public function checkout_small_document($document_id, $reason, $download, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.check_out');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$content='';
    	if ($download)
    	{
    		$document = $document->document;

    		$oStorage =& KTStorageManagerUtil::getSingleton();
            $filename = $oStorage->temporaryFile($document);

    		$fp=fopen($filename,'rb');
    		if ($fp === false)
    		{
    		    $response['status_code'] = 1;
    			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
    			return $response;
    		}
    		$content = fread($fp, filesize($filename));
    		fclose($fp);
    		$content = base64_encode($content);
    	}

		if ($this->version >= 2)
		{
			$result = $this->get_document_detail($document_id);
			$result['results']['content'] = $content;

			return $result;
		}

		$response['status_code'] = 0;
		$response['results'] = $content;
    	return $response;
    }

    /**
     * Undoes a document checkout.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $reason
     * @return kt_document_detail.
     */
    public function undo_document_checkout($document_id, $reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.force_checkin');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->undo_checkout($reason);
		if (PEAR::isError($result))
        {
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

		if ($this->version >= 2)
		{
			return $this->get_document_detail($document_id);
		}

		$response['status_code'] = 0;
    	return $response;
    }
    
    /**
     * Fetches a list of checked out documents (optionally limited to the logged in user)
     * 
     * @param boolean $userSpecific limit to current user
     * @return $checkedout An array of checked out documents 
     */
    // TODO determine whether the listing is showing docs the user should not be able to see
    //     (when not restricting to docs checked out by that user)
    public function get_checkedout_docs($userSpecific = true)
    {
        $checkedout = array();
       
        $where = null;
        // limit to current user?
        if ($userSpecific) {
            $where = array('checked_out_user_id = ?', $this->get_user()->getId());
        }
        else {
            $where = array('is_checked_out = ?', 1);
        }
        $checkedout = KTAPI_Document::getList($where);

        return $checkedout;
    }

    /**
     * Returns a reference to a file to be downloaded.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @return kt_response.
     */
    public function download_document($document_id, $version=null)
    {
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$session = &$this->get_session();
    	$download_manager = new KTDownloadManager();
    	$download_manager->set_session($session->session);
    	$download_manager->cleanup();
    	$url = $download_manager->allow_download($document);

    	$response['status_code'] = 0;
		$response['results'] = urlencode($url);

    	return $response;
    }

    /**
     * Returns a reference to a file to be downloaded.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @return kt_response.
     */
    public function download_small_document($document_id, $version=null)
    {
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$content='';

		$document = $document->document;

		$oStorage =& KTStorageManagerUtil::getSingleton();
        $filename = $oStorage->temporaryFile($document);

		$fp=fopen($filename,'rb');
		if ($fp === false)
		{
		    $response['status_code'] = 1;
			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
			return $response;
		}
		$content = fread($fp, filesize($filename));
		fclose($fp);
		$content = base64_encode($content);


    	$response['status_code'] = 0;
		$response['results'] = $content;

    	return $response;
    }

    /**
     * Deletes a document.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $reason
     * @return kt_response
     */
    public function delete_document($document_id, $reason, $auth_sig = true, $sig_username = '', $sig_password = '')
    {
        if ($auth_sig)
        {
            $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                          'ktcore.transactions.delete');
            if ($response['status_code'] == 1) return $response;
        }

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->delete($reason);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	return $response;

    }

	/**
     * Change the document type.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $documenttype
     * @return array
     */
    public function change_document_type($document_id, $documenttype, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                       'ktcore.transactions.document_type_change');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->change_document_type($documenttype);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

   		return $this->get_document_detail($document_id);

    }

    /**
     * Copy a document to another folder.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param int $folder_id
     * @param string $reason
     * @param string $newtitle
     * @param string $newfilename
     * @return array
     */
 	public function copy_document($document_id,$folder_id,$reason,$newtitle=null,$newfilename=null, $sig_username = '', $sig_password = '')
 	{
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.copy');
        if ($response['status_code'] == 1) return $response;

      	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$tgt_folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $tgt_folder->getMessage();
			return $response;
    	}

    	$result = $document->copy($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$new_document_id = $result->documentid;
    	return $this->get_document_detail($new_document_id, '');
 	}

 	/**
 	 * Move a document to another location.
 	 *
	 * @author KnowledgeTree Team
	 * @access public
 	 * @param int $document_id
 	 * @param int $folder_id
 	 * @param string $reason
 	 * @param string $newtitle
 	 * @param string $newfilename
 	 * @return array
 	 */
 	public function move_document($document_id,$folder_id,$reason,$newtitle=null,$newfilename=null, $sig_username = '', $sig_password = '')
 	{
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.move');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	if ($document->ktapi_folder->folderid != $folder_id)
    	{
    		// we only have to do something if the source and target folders are different

    		$tgt_folder = &$this->get_folder_by_id($folder_id);
    		if (PEAR::isError($tgt_folder))
    		{
    			$response['status_code'] = 1;
    			$response['message'] = $tgt_folder->getMessage();
    			return $response;
    		}

    		$result = $document->move($tgt_folder, $reason, $newtitle, $newfilename);
    		if (PEAR::isError($result))
    		{
    			$response['status_code'] = 1;
    			$response['message'] = $result->getMessage();
    			return $response;
    		}

    	}

    	return $this->get_document_detail($document_id, '');
 	}

 	/**
 	 * Changes the document title.
 	 *
	 * @author KnowledgeTree Team
	 * @access public
 	 * @param int $document_id
 	 * @param string $newtitle
 	 * @return arry
 	 */
 	public function rename_document_title($document_id,$newtitle, $sig_username = '', $sig_password = '', $reason = '')
 	{
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.rename');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->rename($newtitle);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id);
 	}

 	/**
 	 * Renames the document filename.
 	 *
	 * @author KnowledgeTree Team
	 * @access public
 	 * @param int $document_id
 	 * @param string $newfilename
 	 * @return array
 	 */
 	public function rename_document_filename($document_id,$newfilename, $sig_username = '', $sig_password = '', $reason = '')
 	{
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.rename');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->renameFile($newfilename);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id);

 	}

    /**
     * Changes the owner of a document.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $username
     * @param string $reason
     * @return array
     */
    public function change_document_owner($document_id, $username, $reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                       'ktcore.transactions.document_owner_change');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->change_owner($username,  $reason);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id);
    }

    /**
     * Start a workflow on a document
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $workflow
     * @return array
     */
    public function start_document_workflow($document_id,$workflow, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.workflow_state_transition');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = &$document->start_workflow($workflow);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

   		return $this->get_document_detail($document_id);
    }

	/**
	 * Removes the workflow process on a document.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @return array
	 */
    public function delete_document_workflow($document_id, $sig_username = '', $sig_password = '', $reason = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.workflow_state_transition');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->delete_workflow();
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id);
    }

    /**
     * Starts a transitions on a document with a workflow.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @param string $transition
     * @param string $reason
     * @return array
     */
    public function perform_document_workflow_transition($document_id,$transition,$reason, $sig_username = '', $sig_password = '')
    {
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.workflow_state_transition');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->perform_workflow_transition($transition,$reason);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result>getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id);
    }

    /**
     * Returns the metadata on a document.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param int $document_id
     * @return array
     */
	public function get_document_metadata($document_id)
	{
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$metadata = $document->get_metadata();

		$num_metadata=count($metadata);
		for($i=0;$i<$num_metadata;$i++)
		{
			$num_fields = count($metadata[$i]['fields']);
			for($j=0;$j<$num_fields;$j++)
			{
				$selection=$metadata[$i]['fields'][$j]['selection'];
				$new = array();

				foreach($selection as $item)
				{
					$new[] = array(
						'id'=>null,
						'name'=>$item,
						'value'=>$item,
						'parent_id'=>null
					);
				}
				$metadata[$i]['fields'][$j]['selection'] = $new;
			}
		}

		$response['status_code'] = 0;
		$response['result'] = $metadata;
    	return $response;
	}

	/**
	 * Updates document metadata.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @param array $metadata
	 * @return array
	 */
	public function update_document_metadata($document_id,$metadata, $sysdata=null, $sig_username = '', $sig_password = '', $reason = '')
	{
        $response = $this->_check_electronic_signature($document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.metadata_update');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->update_metadata($metadata);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}


    	$result = $document->update_sysdata($sysdata);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	return $this->get_document_detail($document_id, 'M');

	}

	/**
	 * Returns a list of available transitions on a give document with a workflow.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @return array
	 */
	public function get_document_workflow_transitions($document_id)
	{
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->get_workflow_transitions();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	$response['transitions'] = $result;
		return $response;
	}

	/**
	 * Returns the current state that the document is in.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @return array
	 */
	public function get_document_workflow_state($document_id)
	{
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->get_workflow_state();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	$response['message'] = $result;
		return $response;
	}

	/**
	 * Returns the document transaction history.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @return array
	 */
	public function get_document_transaction_history($document_id)
	{
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->get_transaction_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	$response['history'] = $result;
		return $response;
	}

	/**
	 * Returns the folder transaction history.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $folder_id
	 * @return array
	 */
	public function get_folder_transaction_history($folder_id)
	{
    	$folder = &$this->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $folder->getMessage();
			return $response;
    	}

    	$result = $folder->get_transaction_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	$response['history'] = $result;
		return $response;
	}

	/**
	 * Returns the version history.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $document_id
	 * @return kt_document_version_history_response
	 */
	public function get_document_version_history($document_id)
	{
    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$result = $document->get_version_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	$response['history'] = $result;
		return $response;
	}

	/**
	 * Returns a list of linked documents
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $session_id
	 * @param int $document_id
	 * @return array
	 *
	 *
	 */
	public function get_document_links($document_id)
	{
		$response['status_code'] = 1;
    	$response['message'] = '';
    	$response['parent_document_id'] = (int) $document_id;
    	$response['links'] = array();

    	$document = &$this->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$links = $document->get_linked_documents();

    	$response['status_code'] = 0;
    	$response['links'] = $links;
		return $response;
	}

	/**
	 * Removes a link between documents
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $parent_document_id
	 * @param int $child_document_id
	 * @return kt_response
	 */
	public function unlink_documents($parent_document_id, $child_document_id, $sig_username = '', $sig_password = '', $reason = '')
	{
        $response = $this->_check_electronic_signature($parent_document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.unlink');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($parent_document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$child_document = &$this->get_document_by_id($child_document_id);
		if (PEAR::isError($child_document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $child_document->getMessage();
			return $response;
    	}

    	$result = $document->unlink_document($child_document);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	return $response;
	}

	/**
	 * Creates a link between documents
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $parent_document_id
	 * @param int $child_document_id
	 * @param string $type
	 * @return boolean
	 */
	public function link_documents($parent_document_id, $child_document_id, $type, $sig_username = '', $sig_password = '', $reason = '')
	{
        $response = $this->_check_electronic_signature($parent_document_id, $sig_username, $sig_password, $reason, $reason,
                                                      'ktcore.transactions.link');
        if ($response['status_code'] == 1) return $response;

    	$document = &$this->get_document_by_id($parent_document_id);
		if (PEAR::isError($document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $document->getMessage();
			return $response;
    	}

    	$child_document = &$this->get_document_by_id($child_document_id);
		if (PEAR::isError($child_document))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $child_document->getMessage();
			return $response;
    	}

    	$result = $document->link_document($child_document, $type);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = 1;
    		$response['message'] = $result->getMessage();
			return $response;
    	}

    	$response['status_code'] = 0;
    	return $response;
	}

	/**
	 * Retrieves the server policies for this server
	 *
	 * @author KnowledgeTree Team
	 * @access public
	* @return array $response The formatted response array
	 */
	public function get_client_policies($client=null)
	{
		$config = KTConfig::getSingleton();

		$policies = array(
					array(
						'name' => 'explorer_metadata_capture',
						'value' => bool2str($config->get('clientToolPolicies/explorerMetadataCapture')),
						'type' => 'boolean'
					),
					array(
						'name' => 'office_metadata_capture',
						'value' => bool2str($config->get('clientToolPolicies/officeMetadataCapture')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_delete',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsDelete')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_checkin',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCheckin')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_checkout',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCheckout')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_cancelcheckout',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCancelCheckout')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_copyinkt',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCopyInKT')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_moveinkt',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsMoveInKT')),
						'type' => 'boolean'
					),
					array(
						'name' => 'allow_remember_password',
						'value' => bool2str($config->get('clientToolPolicies/allowRememberPassword')),
						'type' => 'boolean'
					),
				);


		$response['policies'] = $policies;
		$response['message'] = _kt('Knowledgetree client policies retrieval succeeded.');
		$response['status_code'] = 0;

		return $response;
	}

	/**
	 * This is the search interface
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param string $query
	 * @param string $options
	* @return array $response The formatted response array
	 */
	public function search($query, $options)
	{
    	$response['status_code'] = 1;
		$response['results'] = array();

		$results = processSearchExpression($query);
		if (PEAR::isError($results))
		{
			$response['message'] = _kt('Could not process query.')  . $results->getMessage();
			return $response;
		}

		$response['message'] = '';
		if(empty($results)){
    		$response['message'] = _kt('Your search did not return any results');
		}
		$response['status_code'] = 0;
		$response['results'] = $results;

		return $response;
	}

	/**
	* Method to create a saved search
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $name The name of the saved search
	* @param string $query The saved search query
	* @return array $response The formatted response array
	*/
	public function create_saved_search($name, $query)
	{
	    $savedSearch = new savedSearches($this);
	    if(PEAR::isError($savedSearch)){
	        $response['status_code'] = 1;
	        $response['message'] = $savedSearch->getMessage();
	        return $response;
	    }

	    $result = $savedSearch->create($name, $query);
	    if(PEAR::isError($result)){
	        $response['status_code'] = 1;
	        $response['message'] = $result->getMessage();
	        return $response;
	    }

	    $response['message'] = '';
	    $response['status_code'] = 0;
	    $response['results']['search_id'] = $result;

	    return $response;
	}

	/**
	* Method to retrieve a saved search
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $searchID The id of the saved search
	* @return array $response The formatted response array
	*/
	public function get_saved_search($searchID)
	{
	    $savedSearch = new savedSearches($this);
	    if(PEAR::isError($savedSearch)){
	        $response['status_code'] = 1;
	        $response['message'] = $savedSearch->getMessage();
	        return $response;
	    }

	    $result = $savedSearch->get_saved_search($searchID);
	    if(PEAR::isError($result)){
	        $response['status_code'] = 1;
	        $response['message'] = $result->getMessage();
	        return $response;
	    }

	    if(empty($result)){
	        $response['status_code'] = 1;
	        $response['message'] = _kt('No saved searches found');
	        return $response;
	    }

	    $response['message'] = '';
	    $response['status_code'] = 0;
	    $response['results'] = $result[0];

	    return $response;
	}

	/**
	* Method to retrieve a list of saved searches
	*
	* @author KnowledgeTree Team
	* @access public
	* @return array $response The formatted response array
	*/
	public function get_saved_search_list()
	{
	    $savedSearch = new savedSearches($this);
	    if(PEAR::isError($savedSearch)){
	        $response['status_code'] = 1;
	        $response['message'] = $savedSearch->getMessage();
	        return $response;
	    }

	    $result = $savedSearch->get_list();
	    if(PEAR::isError($result)){
	        $response['status_code'] = 1;
	        $response['message'] = $result->getMessage();
	        return $response;
	    }

	    if(empty($result)){
	        $response['status_code'] = 1;
	        $response['message'] = _kt('No saved searches found');
	        return $response;
	    }

	    $response['message'] = '';
	    $response['status_code'] = 0;
	    $response['results'] = $result;

	    return $response;
	}

	/**
	* Method to delete a saved searche
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $searchID The id of the saved search to delete
	* @return array $response The formatted response array
	*/
	public function delete_saved_search($searchID)
	{
	    $savedSearch = new savedSearches($this);
	    if(PEAR::isError($savedSearch)){
	        $response['status_code'] = 1;
	        $response['message'] = $savedSearch->getMessage();
	        return $response;
	    }

	    $result = $savedSearch->delete($searchID);
	    if(PEAR::isError($result)){
	        $response['status_code'] = 1;
	        $response['message'] = $result->getMessage();
	        return $response;
	    }

	    $response['message'] = '';
	    $response['status_code'] = 0;

	    return $response;
	}

	/**
	* Method to retrieve a list of saved searches
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $searchID The id of the saved search to delete
	* @return array $response The formatted response array
	*/
	public function run_saved_search($searchID)
	{
	    $savedSearch = new savedSearches($this);
	    if(PEAR::isError($savedSearch)){
	        $response['status_code'] = 1;
	        $response['message'] = $savedSearch->getMessage();
	        return $response;
	    }

	    $results = $savedSearch->run_saved_search($searchID);
	    if(PEAR::isError($results)){
	        $response['status_code'] = 1;
	        $response['message'] = $results->getMessage();
	        return $response;
	    }

	    $response['message'] = '';
	    if(empty($results)){
	        $response['message'] = _kt('Your saved search did not return any results');
	    }
	    $response['status_code'] = 0;
	    $response['results'] = $results;

	    return $response;
	}

	/**
	* Method to get the details of a user
	*
	* @author KnowledgeTree Team
	* @access private
	* @param object $oUser The user object
	* @return array $results The user details in an array
	*/
	private function _get_user_details($oUser)
	{
	    $results['user_id'] = $oUser->getId();
	    $results['username'] = $oUser->getUsername();
	    $results['name'] = $oUser->getName();
	    $results['email'] = $oUser->getEmail();

	    return $results;
	}

	/**
	* Method to return a user based on the userID
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $userID The id of the user
	* @return array $response The formatted response array
	*/
	public function get_user_by_id($userID)
	{
        $user = KTAPI_User::getById($userID);
        if(PEAR::isError($user)){
            $response['status_code'] = 1;
            $response['message'] = $user->getMessage();
            return $response;
        }

        $results = $this->_get_user_details($user);
        $response['message'] = '';
        $response['status_code'] = 0;
        $response['results'] = $results;

        return $response;
	}

	/**
	* Method to return a user based on the username
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $username The username of the user
	* @return array $response The formatted response array
	*/
	public function get_user_by_username($username)
	{
        $user = KTAPI_User::getByUsername($username);
        if(PEAR::isError($user)){
            $response['status_code'] = 1;
            $response['message'] = $user->getMessage();
            return $response;
        }

        $results = $this->_get_user_details($user);
        $response['message'] = '';
        $response['status_code'] = 0;
        $response['results'] = $results;

        return $response;
	}

	/**
	* Method to return a user based on the username
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $username The username of the user
	* @return array $response The formatted response array
	*/
	public function get_user_object_by_username($username)
	{
        return KTAPI_User::getByUsername($username);
	}

	/**
	* Method to return a user based on the name
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $name The name of the user
	* @return array $response The formatted response array
	*/
	public function get_user_by_name($name)
	{
        $user = KTAPI_User::getByName($name);
        if(PEAR::isError($user)){
            $response['status_code'] = 1;
            $response['message'] = $user->getMessage();
            return $response;
        }

        $results = $this->_get_user_details($user);
        $response['message'] = '';
        $response['status_code'] = 0;
        $response['results'] = $results;

        return $response;
	}

	/**
	* Method to return a list of users matching the filter criteria
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $filter
	* @param string $options
	* @return array $response The formatted response array
	*/
	public function get_user_list($filter = NULL, $options = NULL)
	{
        $users = KTAPI_User::getList($filter, $options);
        if(PEAR::isError($users)){
            $response['status_code'] = 1;
            $response['message'] = $users->getMessage();
            return $response;
        }
        foreach($users as $user){
            $results[] = $this->_get_user_details($user);
        }
        $response['message'] = '';
        $response['status_code'] = 0;
        $response['results'] = $results;

        return $response;
	}

	/**
	* Method to check if a document is subscribed
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $documentID The id of the document
	* @return array $response The formatted response array
	*/
	public function is_document_subscribed($documentID)
	{
	    $document = $this->get_document_by_id($documentID);
	    if(PEAR::isError($document)){
	        $response['message'] = $document->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $document->isSubscribed();
        $response['message'] = '';
        $response['status_code'] = 0;
	    if($result){
	        $response['results']['subscribed'] = 'TRUE';
	    }else{
	        $response['results']['subscribed'] = 'FALSE';
	    }
        return $response;
	}

	/**
	* Method to subscribe to a document
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $documentID The id of the document
	* @return array $response The formatted response array
	*/
	public function subscribe_to_document($documentID)
	{
	    $document = $this->get_document_by_id($documentID);
	    if(PEAR::isError($document)){
	        $response['message'] = $document->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $document->subscribe();
	    if($result === TRUE){
            $response['message'] = '';
            $response['status_code'] = 0;
	        $response['results']['action_result'] = 'TRUE';
	    }else{
            $response['message'] = $result;
            $response['status_code'] = 1;
	    }
        return $response;
	}

	/**
	* Method to unsubscribe from a document
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $documentID The id of the document
	* @return array $response The formatted response array
	*/
	public function unsubscribe_from_document($documentID)
	{
	    $document = $this->get_document_by_id($documentID);
	    if(PEAR::isError($document)){
	        $response['message'] = $document->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $document->unsubscribe();
	    if($result === TRUE){
            $response['message'] = '';
            $response['status_code'] = 0;
	        $response['results']['action_result'] = 'TRUE';
	    }else{
            $response['message'] = $result;
            $response['status_code'] = 1;
	    }
        return $response;
	}

	/**
	* Method to check if a folder is subscribed
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $folderID The id of the folder
	* @return array $response The formatted response array
	*/
	public function is_folder_subscribed($folderID)
	{
	    $folder = $this->get_folder_by_id($folderID);
	    if(PEAR::isError($folder)){
	        $response['message'] = $folder->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $folder->isSubscribed();
        $response['message'] = '';
        $response['status_code'] = 0;
	    if($result){
	        $response['results']['subscribed'] = 'TRUE';
	    }else{
	        $response['results']['subscribed'] = 'FALSE';
	    }
        return $response;
	}

	/**
	* Method to subscribe to a folder
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $folderID The id of the folder
	* @return array $response The formatted response array
	*/
	public function subscribe_to_folder($folderID)
	{
	    $folder = $this->get_folder_by_id($folderID);
	    if(PEAR::isError($folder)){
	        $response['message'] = $folder->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $folder->subscribe();
	    if($result === TRUE){
            $response['message'] = '';
            $response['status_code'] = 0;
	        $response['results']['action_result'] = 'TRUE';
	    }else{
            $response['message'] = $result;
            $response['status_code'] = 1;
	    }
        return $response;
	}

	/**
	* Method to unsubscribe from a folder
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $folderID The id of the folder
	* @return array $response The formatted response array
	*/
	public function unsubscribe_from_folder($folderID)
	{
	    $folder = $this->get_folder_by_id($folderID);
	    if(PEAR::isError($folder)){
	        $response['message'] = $folder->getMessage();
	        $response['status_code'] = 1;
	        return $response;
	    }

	    $result = $folder->unsubscribe();
	    if($result === TRUE){
            $response['message'] = '';
            $response['status_code'] = 0;
	        $response['results']['action_result'] = 'TRUE';
	    }else{
            $response['message'] = $result;
            $response['status_code'] = 1;
	    }
        return $response;
	}

	public function is_latest_version($documentID, $contentID)
	{
		$sql = 'SELECT COUNT(document_content_version.id) AS newdocumentcount
		FROM document_content_version
		WHERE document_content_version.document_id ="'.$documentID.'" AND
		document_content_version.id > "'.$contentID.'"';

		$row = DBUtil::getOneResult($sql);
 		$row = (int)$row['newdocumentcount'];

		if ($row > 0) {
			$response['is_latest'] = 'FALSE';
		} else {
			$response['is_latest'] = 'TRUE';
		}

		$response['status_code'] = 0;

		return $response;
	}

    /**
     * Method to check whether electronic signatures are enabled
     *
     * @author KnowledgeTree Team
	 * @access public
     * @return bool $enabled true or false
     */
    public function electronic_sig_enabled()
    {
        // Check that the wintools plugin is active and available, return false if not.
        if (!KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            return false;
        }

        // Check config for api signatures enabled
        $oConfig =& KTConfig::getSingleton();
        $enabled = $oConfig->get('e_signatures/enableApiSignatures', false);
        // Check that the license is valid
        $enabled = (BaobabKeyUtil::getLicenseCount() >= MIN_LICENSES) & $enabled;

        return $enabled;
    }

    /**
     * Attempts authentication of the signature
     *
     * @author KnowledgeTree Team
     * @access private
     * @param string $username The user's username
     * @param string $password The user's password
     * @param string $comment A comment on the action performed
     * @param string $action The action performed
     * @param string $details Details about the action performed
     * @return bool True if authenticated | False if rejected
     */
    private function _authenticateSignature($username, $password, $comment, $action, $details)
    {
        $eSignature = new ESignature('api');
        $result = $eSignature->sign($username, $password, $comment, $action, $details);
        if(!$result){
            $this->esig_error = $eSignature->getError();
        }

        return $result;
    }

    /**
     * Method to execute electronic signature checks on action
     *
     * @author KnowledgeTree Team
     * @access private
     * @param string $item_id ID of document/folder which will be used as detail string in authentication records
     * @param string $username The user's username
     * @param string $password The user's password
     * @param string $comment A comment on the action performed
     * @param string $details Unused
     * @param string $action The action performed
     * @return array $response containing success/failure result and appropriate message
     */
    private function _check_electronic_signature($item_id, $username, $password, $comment, $details, $action)
    {
        $response = array();
        $response['status_code'] = 0;

        // check electronic signature authentication, if on
        if ($this->esig_enabled && !$this->_authenticateSignature($username, $password, $comment, $action, $item_id))
        {
            $response['status_code'] = 1;
    	    $response['message'] = $this->esig_error;

    	    return $response;
        }

        return $response;
    }
}


/**
* This class handles the saved search functionality within the API
*
* @author KnowledgeTree Team
* @package KTAPI
* @version Version 0.9
*/
class savedSearches
{
     /**
     * Instance of the KTAPI object
     *
     * @access private
     */
    private $ktapi;

    /**
     * Constructs the bulk actions object
     *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
     */
    function __construct(&$ktapi)
    {
//        $this->ktapi = new KTAPI();
        $this->ktapi = $ktapi;
    }

	/**
	* This method creates the saved search
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $name The name of the search
	* @param string $query The query string to be saved
	* @return string|object $result SUCCESS - The id of the saved search | FAILURE - an error object
	*/
	public function create($name, $query)
	{
		$user = $this->ktapi->get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			$result =  new PEAR_Error(KTAPI_ERROR_USER_INVALID);
			return $result;
		}
		$userID = $user->getId();

		$result = SearchHelper::saveSavedSearch($name, $query, $userID);
		return $result;
	}

	/**
	* This method gets a saved searche based on the id
	*
	* @author KnowledgeTree Tean
	* @access public
	* @param integer $searchID The id of the saved search
	* @return array|object $search SUCESS - The saved search data | FAILURE - a pear error object
	*/
	public function get_saved_search($searchID)
	{
		$search = SearchHelper::getSavedSearch($searchID);
		return $search;
	}

	/**
	* This method gets a list of saved searches
	*
	* @author KnowledgeTree Tean
	* @access public
	* @return array|object $list SUCESS - The list of saved searches | FAILURE - an error object
	*/
	public function get_list()
	{
		$user = $this->ktapi->get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			$list =  new PEAR_Error(KTAPI_ERROR_USER_INVALID);
			return $list;
		}
		$userID = $user->getId();

		$list = SearchHelper::getSavedSearches($userID);
		if (PEAR::isError($list))
		{
			$list =  new PEAR_Error('Invalid saved search result');
			return $list;
		}
		return $list;
	}

	/**
	* This method deletes the saved search
	*
	* @author KnowledgeTree Team
	* @access public
	* @param string $searchID The id of the saved search
	* @return void
	*/
	public function delete($searchID)
	{
        SearchHelper::deleteSavedSearch($searchID);
	}

	/**
	* This method runs the saved search bsed on the id of the saved search
	*
	* @author KnowledgeTree Team
	* @access public
	* @param integer $searchID The id of the saved search
	* @return array|object $results SUCCESS - The results of the saved serach | FAILURE - a pear error object
	*/
	public function run_saved_search($searchID)
	{
		$search = $this->get_saved_search($searchID);
		if(is_null($search) || PEAR::isError($search)){
		    $results = new PEAR_Error('Invalid saved search');
		    return $results;
		}
		$query = $search[0]['expression'];

	    $results = processSearchExpression($query);
		return $results;
	}
}

?>

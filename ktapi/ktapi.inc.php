<?php
/**
* Implements a cleaner wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
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

define('KTAPI_DIR',KT_DIR . '/ktapi');

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

 	/**
 	* This returns the current session.
 	*
	* @author KnowledgeTree Team
 	* @access public
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
 	* @access public
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
 	* @access public
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

				$results[] = $idsOnly?$documentid:KTAPI_Document::get($this, $documentid);
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
	public function & start_system_session()
	{
		$user = User::get(1);

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
	public function &get_folder_by_name($foldername)
	{
		$folder = KTAPI_Folder::_get_folder_by_name($this, $foldername, 1);
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
		$user = KTAPI::get_user();
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
	public function getSavedSearch($searchID)
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
	public function getList()
	{
		$user = KTAPI::get_user();
		if (is_null($user) || PEAR::isError($user))
		{
			$list =  new PEAR_Error(KTAPI_ERROR_USER_INVALID);
			return $list;
		}
		$userID = $user->getId();

		$list = SearchHelper::getSavedSearches($userID);
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
	public function runSavedSearch($searchID)
	{
		$search = KTAPI::getSavedSearch($searchID);
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

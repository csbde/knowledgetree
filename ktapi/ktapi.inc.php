<?php
/**
 * $Id$
 *
 * Implements a cleaner wrapper API for KnowledgeTree.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 * Contributor( s): ______________________________________
 *
 */

$_session_id = session_id();
if (empty($_session_id)) session_start();
unset($_session_id);

require_once(realpath(dirname(__FILE__) . '/../config/dmsDefaults.php'));
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

define('KTAPI_DIR',KT_DIR . '/ktapi');

require_once(KTAPI_DIR .'/KTAPIConstants.inc.php');
require_once(KTAPI_DIR .'/KTAPISession.inc.php');
require_once(KTAPI_DIR .'/KTAPIFolder.inc.php');
require_once(KTAPI_DIR .'/KTAPIDocument.inc.php');
require_once(KTAPI_DIR .'/KTAPIAcl.inc.php');

abstract class KTAPI_FolderItem
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

	public abstract function getObject();

	public abstract function isSubscribed();

	public abstract function unsubscribe();

	public abstract function subscribe();

}

class KTAPI_Error extends PEAR_Error
{
	function KTAPI_Error($msg, $obj = null)
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

class KTAPI_DocumentTypeError extends KTAPI_Error {}

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
 	 * Search for documents matching the oem_no.
 	 *
 	 * Note that oem_no is associated with a document and not with version of file (document content).
 	 * oem_no is set on a document using document::update_sysdata().
 	 *
 	 * @param string $oem_no
 	 * @param boolean idsOnly Defaults to true
 	 * @return array
 	 */
 	function get_documents_by_oem_no($oem_no, $idsOnly=true)
 	{
		$sql = array("SELECT id FROM documents WHERE oem_no=?",$oem_no);
		$rows = DBUtil::getResultArray($sql);
		if (is_null($rows) || PEAR::isError($rows))
		{
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}

		$result = array();
		foreach($rows as $row)
		{
			$documentid = $row['id'];

			$result[] = $idsOnly?$documentid:KTAPI_Document::get($this, $documentid);
		}

 		return $result;
 	}

	/**
	 * This returns a session object based on a session string.
	 *
	 * @access public
	 * @param string $session
	 * @return KTAPI_Session
	 */
	function & get_active_session($session, $ip=null, $app='ws')
	{
		if (!is_null($this->session))
		{
			return new PEAR_Error('A session is currently active.');
		}

		$session = &KTAPI_UserSession::get_active_session($this, $session, $ip, $app);

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
	function & start_session($username, $password, $ip=null, $app='ws')
	{
		if (!is_null($this->session))
		{
			return new PEAR_Error('A session is currently active.');
		}

		$session = &KTAPI_UserSession::start_session($this, $username, $password, $ip, $app);
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
	 * start a root session.
	 *
	 * @return KTAPI_SystemSession
	 */
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

	function &get_folder_by_name($foldername)
	{
		return KTAPI_Folder::_get_folder_by_name($this, $foldername, 1);
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
		$sql = array("SELECT id FROM document_types_lookup WHERE name=? and disabled=0", $documenttype);
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			return new KTAPI_DocumentTypeError(KTAPI_ERROR_DOCUMENT_TYPE_INVALID, $row);
		}
		$documenttypeid = $row['id'];
		return $documenttypeid;
	}

	function get_link_type_id($linktype)
	{
		$sql = array("SELECT id FROM document_link_types WHERE name=?",$linktype);
		$row = DBUtil::getOneResult($sql);
		if (is_null($row) || PEAR::isError($row))
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_LINK_TYPE_INVALID);
		}
		$typeid = $row['id'];
		return $typeid;
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

	function get_document_link_types()
	{
		$sql = "SELECT name FROM document_link_types order by name";
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
	 * This should actually not be in ktapi, but in webservice
	 *
	 * @param unknown_type $document_type
	 * @return unknown
	 */
	function get_document_type_metadata($document_type='Default')
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

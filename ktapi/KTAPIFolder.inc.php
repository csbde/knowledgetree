<?php
/**
 *
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright(C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 * Contributor(s): ______________________________________
 */

/**
 * Folder API for KnowledgeTree
 *
 * @copyright 2008-2010, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
*/

require_once(KT_DIR . '/ktwebservice/KTUploadManager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');

/**
 * This class handles folder related operations
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 *
*/
class KTAPI_Folder extends KTAPI_FolderItem {

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
        if (is_null($ktapi) || !($ktapi instanceof KTAPI)) {
            return PEAR::raiseError('A valid KTAPI object is needed');
        }

        if (!is_numeric($folderid)) {
            return PEAR::raiseError('A valid folder id is required');
        }

        $folderid += 0;

        $folder = &Folder::get($folderid);
        if (is_null($folder) || PEAR::isError($folder)) {
            return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID, $folder);
        }

        // A special case. We ignore permission checking on the root folder.
        if ($folderid != 1) {
            $user = $ktapi->can_user_access_object_requiring_permission($folder, KTAPI_PERMISSION_READ);

            if (is_null($user) || PEAR::isError($user)) {
                $user = $ktapi->can_user_access_object_requiring_permission($folder, KTAPI_PERMISSION_VIEW_FOLDER);
                if (is_null($user) || PEAR::isError($user)) {
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

        $wsversion = $this->getWSVersion();

        $detail = array(
                    'id' => (int) $this->folderid,
                    'folder_name' => $this->get_folder_name(),
                    'parent_id' => (int) $this->get_parent_folder_id(),
                    'full_path' => $this->get_full_path(),
                    'linked_folder_id' => $this->folder->getLinkedFolderId(),
                    'permissions' => KTAPI_Folder::get_permission_string($this->folder),
        );

        if ($wsversion < 3) {
            unset($detail['linked_folder_id']);
        }

        $folder = $this->folder;
        $userid = $folder->getCreatorID();
        $username='n/a';
        if (is_numeric($userid)) {
            $username = '* unknown *';
            $user = User::get($userid);
            if (!is_null($user) && !PEAR::isError($user)) {
                $username = $user->getName();
            }
        }

        $detail['created_by'] = $username;
        $detail['created_date'] = $folder->getDisplayCreatedDateTime();

        $userid = $folder->getModifiedUserId();
        $username='n/a';
        if (is_numeric($userid)) {
            $username = '* unknown *';
            $user = User::get($userid);
            if (!is_null($user) && !PEAR::isError($user)) {
                $username = $user->getName();
            }
        }

        $detail['modified_by'] = $detail['updated_by'] = $username;
        $detail['updated_date'] = $detail['modified_date'] = $folder->getDisplayLastModifiedDate();

        //clean uri
        $detail['clean_uri'] = KTBrowseUtil::getUrlForfolder($folder);

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
        $GLOBALS['_OBJECTCACHE']['Folder'] = array();
        $this->folder = &Folder::get($this->folderid);
    }

    /**
	 * @author KnowledgeTree Team
	 * @access public
	 * @return unknown
	 */
    function get_parent_folder_id()
    {
        return (int)$this->folder->getParentID();
    }

    /**
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
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return int
	 */
    function get_folderid()
    {
        return(int) $this->folderid;
    }

    /**
	 * This function will return a folder by it's name(not ID)
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi
	 * @param string $foldername
	 * @param int $folderid The parent folder id
	 * @return KTAPI_Folder
	 */
    static function _get_folder_by_name($ktapi, $foldername, $folderid)
    {
        $foldername = trim($foldername);
        if (empty($foldername))
        {
            return new PEAR_Error('A valid folder name must be specified.');
        }

        $split = explode('/', $foldername);

        foreach ($split as $foldername)
        {
            if (empty($foldername))
            {
                continue;
            }

            $foldername = KTUtil::replaceInvalidCharacters($foldername);
            $foldername = sanitizeForSQL($foldername);
            $sql = "SELECT id FROM folders WHERE
					(name='$foldername' and parent_id = $folderid) OR
					(name='$foldername' and parent_id is null and $folderid=1)";
            $row = DBUtil::getOneResult($sql);
            if (is_null($row) || PEAR::isError($row))
            {
                return new KTAPI_Error(KTAPI_ERROR_FOLDER_INVALID, $row);
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
        if (empty($path)) { $path = '/'; }

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
    function _get_document_by_name($documentname, $function = 'getByNameAndFolder')
    {
        $documentname = trim($documentname);
        if (empty($documentname))
        {
            return new PEAR_Error('A valid document name must be specified.');
        }

        $foldername = dirname($documentname);
        $documentname = basename($documentname);
        $documentname = KTUtil::replaceInvalidCharacters($documentname);

        $ktapi_folder = $this;

        if (!empty($foldername) &&($foldername != '.'))
        {
            // TODO confirm that this addition of the parent folder id as second parameter is correct and necessary
            $ktapi_folder = $this->get_folder_by_name($foldername, $this->folderid);
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
                // TODO confirm that this addition of the parent folder id as second parameter is correct and necessary
                $ktapi_folder = $this->get_folder_by_name($foldername, $this->folderid);
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
        return $this->_get_document_by_name($documentname, 'getByNameAndFolder');
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
        return $this->_get_document_by_name($documentname, 'getByFilenameAndFolder');
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
        $user = null;

        if (!is_null($userid))
        {
            $user = User::get($userid);
            if (is_null($user) || PEAR::isError($user))
            {
                $user = null;
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
            if (Permission::userHasSecurityFolderPermission($folder))
            {
                $perms .= 'S';
            }
        }
        return $perms;
    }

    function get_children_ids()
    {
    	$children_ids = array();
    	$user = $this->ktapi->get_user();

    	$folder_children = Folder::getList(array('parent_id = ?', $this->folderid));

    	//if user can't view the folder's details, then it is empty for him!
    	$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);
        //we first check if there is at least one subfolder that the user has permissions on
        foreach ($folder_children as $child)
        {
	        if (KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $child))
	        {
				$children_ids[] = $child->getId();
			}
        }

        return $children_ids;
    }

    /**
	 * Checks whether a folder is relatively empty, i.e. whether it is empty for a specific user
	 * taking permissions into consideration
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @return string
	 */
    function is_empty()
    {
    	$user = $this->ktapi->get_user();

    	//if user can't view the folder's details, then it is empty for him!
    	$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);
		if (!KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $this->folder)) {
			//$GLOBALS['default']->log->debug('is_empty user does not have view folder permission');
			return true;
		}

        $folder_children = Folder::getList(array('parent_id = ?', $this->folderid));

        //we first check if there is at least one subfolder that the user has permissions on
        foreach ($folder_children as $folder) {
	        if (KTPermissionUtil::userHasPermissionOnItem($user, $folder_permission, $folder)) {
				//$GLOBALS['default']->log->debug('is_empty user has view folder permission on folder '.$folder->getId());

				//if there is at least one subfolder, then it ain't empty!
				return false;
			}
        }

        //now check if there is at least one document in the folder
        //we don't need to check permissions since document permissions are folder-based
    	$document_children = Document::getList(array('folder_id = ? AND status_id = 1', $this->folderid));

    	//$GLOBALS['default']->log->debug('is_empty number of documents '.count($document_children));

    	//if there is at least one document in the folder, then it ain't empty!
    	if (count($document_children) > 0) {
	    	return false;
    	}
	    else {
	    	return true;
	    }
    }

    function get_total_documents()
    {
    	//$GLOBALS['default']->log->debug('get_total_files');

    	$document_children = Document::getList(array('folder_id = ? AND status_id = 1', $this->folderid));

    	$read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
        //$folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);
        $user = $this->ktapi->get_user();

    	$total_files = 0;
    	$total_size = 0;

    	foreach ($document_children as $document) {
    		//$GLOBALS['default']->log->debug('get_total_documents document '.print_r($document, true));
    		if (KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $document)) {
    			//$GLOBALS['default']->log->debug('get_total_documents document size '.$document->getFileSize());
    			$total_files++;
            	$total_size += (int) $document->getFileSize();
    		}
    	}

    	//$GLOBALS['default']->log->debug("get_total_documents document total $total_files $total_size");

    	$result = array(
    		'total_files' => $total_files,
    		'total_size' => $total_size,
    	);

    	//$GLOBALS['default']->log->debug('get_total_documents result '.print_r($result, true));

    	return $result;
    }

    /**
	 * Gets a folder listing, recursing to the given depth.
	 *
	 * <code>
	 * $root = $this->ktapi->get_root_folder();
	 * $listing = $root->get_listing();
	 * foreach ($listing as $val) {
	 * 	if ($val['item_type'] == 'F') {
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
	 * @param int $totalItems The total number of items available (for item navigation when using offsets and limits)
	 * @param array $options Options include limit, offset, orderby (overridden for folders)
	 * @return array
	 */
    function get_listing($depth = 1, $what = 'DFS', &$totalItems = -1, $options = array())
    {
        // TODO no need to get listings if the offset is beyond the total

        $calculateTotal = ($totalItems == -1) ? false : true;
        $totalItems = $totalFolders = $totalDocuments = 0;

        // are we fetching the entire tree?
        // Set a static boolean value which will instruct recursive calls to ignore the depth parameter;
        // negative indicates full tree, positive goes to specified depth, 0 = nothing
        static $fullTree = null;
        if (is_null($fullTree)) {
            $fullTree = ($depth < 0) ? true : false;
        }

        // if we are not getting the full listing, we need to kick out if depth less than 1
        if (!$fullTree && ($depth < 1)) {
            return array();
        }

        $what = strtoupper($what);
        /*$read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
        $folder_permission = &KTPermission::getByName(KTAPI_PERMISSION_VIEW_FOLDER);*/
        $user = $this->ktapi->get_user();
        $contents = $folderContents = $documentContents = array();

        // Initialize the options array and merge it with options passed
        $queryOptions = array();
        if (is_array($options)) {
            $queryOptions = array_merge($queryOptions, $options);
        }

        if (strpos($what, 'F') !== false) {
            $folderContents = $this->getFolderListing($user, $queryOptions, $depth, $what, $fullTree, $calculateTotal, $totalFolders);
            if (PEAR::isError($folderContents)) {
                return $folderContents;
            }
        }

        $remaining = $this->checkLimit($queryOptions, count($folderContents), $totalFolders);

        if (strpos($what, 'D') !== false) {
            $documentContents = $this->getDocumentListing($user, $queryOptions, $what, $calculateTotal, $totalDocuments, $remaining);
            if (PEAR::isError($documentContents)) {
                return $documentContents;
            }
        }

        $totalItems = $totalFolders + $totalDocuments;
        $contents = array_merge($documentContents, $folderContents);

        return $contents;
    }

    /**
     * Fetch the folder content listing for a specified folder.
     * Expects to be fed with inputs from get_listing - not intended for use on its own.
     * (Just did not like how long get_listing had become, was very unwieldy when debugging)
     *
     * @param object $user
     * @param array $queryOptions
     * @param string $what
     * @param boolean $fullTree
     * @param boolean $calculateTotal
     * @param int $totalFolders
     * @return array
     */
    private function getFolderListing($user, $queryOptions, $depth = 1, $what = 'FS', $fullTree = false, $calculateTotal = false, &$totalFolders = 0)
    {
        $folderContents = array();

        $permission = KTAPI_PERMISSION_VIEW_FOLDER;
        // Check for the permissions type in the options array
        if (isset($queryOptions['permission']) && !empty($queryOptions['permission'])) {
        	$permission = $queryOptions['permission'];
        }
        
        $res = KTSearchUtil::permissionToSQL($user, $permission, 'F');
        if (PEAR::isError($res)) {
            return $res;
        }

        list($permissionString, $permissionParams, $permissionJoin) = $res;

        if (isset($_SESSION['adminmode']) && ($_SESSION['adminmode']+0)) {
            if (Permission::adminIsInAdminMode() || Permission::isUnitAdministratorForFolder($user, $this->folder)) {
                $permissionString = true;
                $permissionParams = array();
                $permissionJoin = '';
            }
        }

        $where = "WHERE $permissionString AND F.parent_id = ?";
        // deal with options
        $fQueryOptions = $queryOptions;
        $fQueryOptions['orderby'] = 'F.name';
        $optionString = DBUtil::getDbOptions($fQueryOptions);

        if ($calculateTotal) {
            $totalSql = "SELECT count(F.id) as folder_ids FROM folders as F $permissionJoin $where GROUP BY F.id";
            $totalFolders = DBUtil::getResultArrayKey(array($totalSql, array_merge($permissionParams, array($this->folderid))), 'folder_ids');
            if (PEAR::isError($totalFolders)) {
                return $totalFolders;
            }
            $totalFolders = count($totalFolders);
        }

        $sql = "SELECT F.id as folder_id FROM folders as F $permissionJoin $where $optionString";
        $folder_children = DBUtil::getResultArrayKey(array($sql, array_merge($permissionParams, array($this->folderid))), 'folder_id');
        if (PEAR::isError($folder_children)) {
            return $folder_children;
        }

        foreach ($folder_children as $folderId) {
            $folder = Folder::get($folderId);
            if ($fullTree || ($depth > 1)) {
                $sub_folder = &$this->ktapi->get_folder_by_id($folder->getId());
                // This doesn't support limits and offsets but they wouldn't really make sense with this operation anyway
                $items = $sub_folder->get_listing($depth - 1, $what);
            } else {
                $items = array();
            }

            $this->assemble_folder_array($folder, $folderContents, (strpos($what, 'S') !== false) ? 'FS' : 'F', $items);
        }

        return $folderContents;
    }

    /**
     * Fetch the document content listing for a specified folder.
     * Expects to be fed with inputs from get_listing - not intended for use on its own.
     * (Just did not like how long get_listing had become, was very unwieldy when debugging)
     *
     * @param object $user
     * @param array $queryOptions
     * @param string $what
     * @param boolean $calculateTotal
     * @param int $totalFolders
     * @param int $remaining
     * @return array
     */
    private function getDocumentListing($user, $queryOptions, $what = 'DS', $calculateTotal = false, &$totalDocuments = 0, $remaining = -1)
    {
        $documentContents = array();

        $res = KTSearchUtil::permissionToSQL($user, KTAPI_PERMISSION_READ, 'D');
        if (PEAR::isError($res)) {
            return $res;
        }

        list($permissionString, $permissionParams, $permissionJoin) = $res;

        if (isset($_SESSION['adminmode']) && ($_SESSION['adminmode']+0)) {
            if (Permission::adminIsInAdminMode() || Permission::isUnitAdministratorForFolder($user, $this->folder)) {
                $permissionString = true;
                $permissionParams = array();
                $permissionJoin = '';
            }
        }

        $contentVersionJoin = 'INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
			                   INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id ';
        $where = "WHERE D.status_id = 1 AND $permissionString AND D.folder_id = ?";
        $queryOptions['orderby'] = 'DMV.name';
        $optionString = DBUtil::getDbOptions($queryOptions);

        if ($calculateTotal) {
            $totalSql = "SELECT count(D.id) as document_ids FROM (documents as D INNER JOIN documents as DJ ON D.id = DJ.id) $permissionJoin $where GROUP BY D.id";
            $totalDocuments = DBUtil::getResultArrayKey(array($totalSql, array_merge($permissionParams, array($this->folderid))), 'document_ids');
            if (PEAR::isError($totalDocuments)) {
                // FIXME not what we want?
                return $totalDocuments;
            }
            $totalDocuments = count($totalDocuments);
        }

        // do we need to fetch anything or do we just need the count for paging?
        if ($remaining != 0) {
            $sql = "SELECT distinct D.id as document_id FROM documents as D $contentVersionJoin $permissionJoin $where $optionString";
            $document_children = DBUtil::getResultArrayKey(array($sql, array_merge($permissionParams, array($this->folderid))), 'document_id');
            if (PEAR::isError($document_children)) {
                // FIXME not what we want?
                return $document_children;
            }

            foreach ($document_children as $documentId) {
                $what = (strpos($what, 'S') !== false) ? 'DS' : 'D';
                $document = Document::get($documentId);
                $this->assemble_document_array($document, $documentContents, $what);
            }
        }

        // now sort the array of Documents according to title
        // NOTE This is still needed because the combination of 'distinct' and 'order by' in the query does NOT return
        //      what you might expect - duplicated objects end up in the incorrect place in the order
        /*usort($documentContents, array($this, 'compare_title'));*/

        return $documentContents;
    }

    /**
     * Check collection limits after getting a certain number of items of one type (folders.)
     *
     * @param array $queryOptions
     * @param int $found
     * @param int $totalFolders
     * @return int
     */
    private function checkLimit(&$queryOptions, $found, $totalFolders = 0)
    {
        $remaining = -1;

        if (!empty($queryOptions['limit'])) {
            $remaining = $queryOptions['limit'] - $found;
            $queryOptions['limit'] = $remaining;
            // remaining offset will depend on whether there were any folders returned in this match and the total folder count.
            // no folders means offset may need to be applied to docs.
            // folders = no offset to be applied to docs, it has already been applied to the folder results.
            if (($found == 0) && isset($queryOptions['offset'])) {
                $toFind = $queryOptions['offset'] - $totalFolders;
                $queryOptions['offset'] = ($toFind > 0) ? $toFind : 0;
            }
            else {
                $queryOptions['offset'] = 0;
            }
        }

        return $remaining;
    }

    private function getWSVersion()
    {
        $wsversion = $this->ktapi->getVersion();
        return $wsversion;
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
    function add_document_shortcut($document_id) {
        $user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_WRITE);
        if (PEAR::isError($user))
        {
            return $user;
        }
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            return $oDocument;
        }

        $user = $this->can_user_access_object_requiring_permission($oDocument, KTAPI_PERMISSION_READ);
        if (PEAR::isError($user))
        {
            return $user;
        }
        $document = KTDocumentUtil::createSymbolicLink($document_id, $this->folder, $user);
        if (PEAR::isError($document))
        {
            return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $document->getMessage());
        }

        return new KTAPI_Document($this->ktapi, $this, $document);
    }

    /**
	 * This adds a shortcut pointing to an existing folder to the current folder
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param int $folder_id The ID of the folder to create a shortcut to
	 * @return KTAPI_Folder
	 */
    function add_folder_shortcut($folder_id) {
        $user = $this->can_user_access_object_requiring_permission($this->folder, KTAPI_PERMISSION_WRITE);
        if (PEAR::isError($user))
        {
            return $user;
        }
        $oFolder = Folder::get($folder_id);
        if (PEAR::isError($oFolder)) {
            return $oFolder;
        }

        $user = $this->can_user_access_object_requiring_permission($oFolder, KTAPI_PERMISSION_READ);
        if (PEAR::isError($user))
        {
            return $user;
        }
        $folder = & KTFolderUtil::createSymbolicLink($folder_id, $this->folder, $user);
        if (PEAR::isError($folder))
        {
            return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ' : ' . $folder->getMessage());
        }

        return new KTAPI_Folder($this->ktapi, $folder);
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
        $storage = KTStorageManagerUtil::getSingleton();
        if (!$storage->isFile($tempfilename))
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
                // FIXME This should not assume the name, it should look it up using id 1.
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
        'cleanup_initial_file' => true,
        'source' => 'ktapi'
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
//            return $user;
            return new KTAPI_Error(KTAPI_ERROR_INSUFFICIENT_PERMISSIONS, $user);
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
        assert($ktapi_target_folder instanceof KTAPI_Folder);

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
            return $result;
//            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $result);
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
        assert($ktapi_target_folder instanceof KTAPI_Folder);

        $user = $this->ktapi->get_user();

        $target_folder = $ktapi_target_folder->get_folder();

        $result  = $this->can_user_access_object_requiring_permission($target_folder, KTAPI_PERMISSION_WRITE);

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
            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $transactions );
        }

        $wsversion = $this->getWSVersion();
        foreach ($transactions as $key=>$transaction)
        {
            $transactions[$key]['version'] =(float) $transaction['version'];
            $transactions[$key]['datetime'] = datetimeutil::getLocaleDate($transactions[$key]['datetime']);
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

        if (PEAR::isError($result)) {
            return $result->getMessage();
        }
        if ($result) {
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

        if (PEAR::isError($result)) {
            return $result->getMessage();
        }
        if ($result) {
            return $result;
        }

        return $_SESSION['errorMessage'];
    }

    /**
	 * Method to add a Document to the User's History
	 *
	 * This integrates with the User History commercial plugin
	 * @author KnowledgeTree Team
	 * @access public
	 */
    public function addFolderToUserHistory()
    {
        if (KTPluginUtil::pluginIsActive('brad.UserHistory.plugin')) {
            $path = KTPluginUtil::getPluginPath('brad.UserHistory.plugin');
            require_once($path.'UserHistoryActions.php');

            $folderAction = new UserHistoryFolderAction($this->folder, $this->ktapi->get_user());
            $folderAction->_show();
        }
    }

    /**
	 * Method to get the Ids of all the Parent Folders
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 */
    public function getParentFolderIDs()
    {
        return $this->folder->getParentFolderIDs();
    }

    /**
	 * Helper function for sorting documents
	 * @param $a
	 * @param $b
	 */
    private function compare_title($a, $b)
    {
        //$GLOBALS['default']->log->debug('compare_title '.$a['title'].' to '.$b['title'].' result '.strnatcmp($a['title'], $b['title']));
        return strnatcasecmp($a['title'], $b['title']);
    }

    /**
     * Gets the changes in a folder since a specific time
     *
     * @return number
     */
    public function getChanges($timestamp, $depth = 1, $what = 'DF')
    {
    	//$GLOBALS['default']->log->debug("getChanges $timestamp $depth '$what'");

    	//get the user; used to determine permissions
    	$user = $this->ktapi->get_user();

    	$folderPermissionsSQL = array();
    	$filePermissionsSQL = array();

    	//cache the permissions for this call
    	if (strpos($what, 'F') !== false)
        {
	    	$folderPermissionsSQL = KTSearchUtil::permissionToSQL($user, KTAPI_PERMISSION_VIEW_FOLDER, 'F');

	    	//$GLOBALS['default']->log->debug('getChanges folderPermissionsSQL '.print_r($folderPermissionsSQL, true));

	        if (PEAR::isError($folderPermissionsSQL)) {
	            $folderPermissionsSQL = array();
	        }
        }
    	if (strpos($what, 'D') !== false)
        {
	    	$filePermissionsSQL = KTSearchUtil::permissionToSQL($user, KTAPI_PERMISSION_READ, 'D');
	        if (PEAR::isError($filePermissionsSQL)) {
	            $filePermissionsSQL = array();
	        }
        }

    	//array to store all the changes
    	$changes = array();

    	//has the current folder itself changed in relevant ways?
    	$this->renamedSince($timestamp, $folderPermissionsSQL, $changes);
    	$this->movedSince($timestamp, $folderPermissionsSQL, $changes);
    	$this->updatedSince($timestamp, $folderPermissionsSQL, $changes);
    	$this->pathChangedSince($timestamp, $changes);

    	//have to check more than just myself?
    	if ($depth != 0)
    	{
	    	$this->childrenCreatedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
	    	$this->childrenDeletedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
	    	$this->childrenRenamedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
	    	$this->childrenMovedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
	    	$this->childrenUpdatedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);

	    	//$GLOBALS['default']->log->debug('getChanges created '.print_r($changes, true));

	    	//have to check more than just my immediate children?
	    	if ($depth != 1)
	    	{
	    		$this->getChangesRecursive($timestamp, $depth, $what, $user, $folderPermissionsSQL, $filePermissionsSQL, $changes);

	    		//$GLOBALS['default']->log->debug('getChanges recursive '.print_r($changes, true));
	    	}
    	}

    	//now sort the array according to id
        usort($changes, array($this, 'compare_changes'));

        //$this->resolveChanges($merged);

    	//$GLOBALS['default']->log->debug('getChanges merged '.print_r($changes, true));

    	return $changes;
    }

     /**
     * Recursively gets the changes in a folder since a specific time
     *
     * @return number
     */
	private function getChangesRecursive($timestamp, $depth = 1, $what = 'DF', $user, $folderPermissionsSQL, $filePermissionsSQL, &$changes = array())
    {
    	//$GLOBALS['default']->log->debug("getChangesRecursive timestamp $timestamp depth $depth ".print_r($changes, true));

    	// are we fetching the entire tree?
        // Set a static boolean value which will instruct recursive calls to ignore the depth parameter;
        // negative indicates full tree, positive goes to specified depth, 0 = nothing
    	static $fullTree = null;
        if (is_null($fullTree)) {
            $fullTree = ($depth < 0) ? true : false;
        }

        // if we are not getting the full listing, we need to kick out if depth less than 1
        if (!$fullTree && ($depth < 1)) {
            return array();
        }

        if ($fullTree || ($depth > 1)) {
        	//build up the SQL, including the permissions query
        	$res = KTSearchUtil::permissionToSQL($user, KTAPI_PERMISSION_VIEW_FOLDER, 'F');
	        if (PEAR::isError($res)) {
	            return $res;
	        }

	        list($sPermissionSQL, $aPermissionParams, $sPermissionJoin) = $res;

        	$sSelect1SQL = 'F.id AS folder_id FROM '. KTUtil::getTableName('folders') . ' AS F ';
        	$sWhere1SQL = 'F.parent_id = ? ';
	    	$sSelect2SQL = 'FT.folder_id AS folder_id FROM folder_transactions AS FT ';
	    	$Where2SQL = 'FT.transaction_namespace = \'ktcore.transactions.delete\' AND FT.parent_id = ?';
	    	$aOptions = array('orderby' => 'folder_id ASC');
	    	$sOptionSQL = DBUtil::getDbOptions($aOptions);

	    	$sSQL = "SELECT $sSelect1SQL $sPermissionJoin WHERE $sPermissionSQL AND $sWhere1SQL UNION SELECT $sSelect2SQL WHERE $Where2SQL $sOptionSQL";

	        $aParams = array_merge($aPermissionParams, array($this->folderid), array($this->folderid));

	        $results = DBUtil::getResultArray(array($sSQL, $aParams));
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result)
	        {
	        	$folder = &Folder::get($result['folder_id']);
	        	if (!PEAR::isError($folder))
	        	{
					$ktapi_folder = &$this->ktapi->get_folder_by_id($folder->getId());

					// get the changes for the current folder
					$ktapi_folder->childrenCreatedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
					$ktapi_folder->childrenDeletedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
					$ktapi_folder->childrenRenamedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
					$ktapi_folder->childrenMovedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);
					$ktapi_folder->childrenUpdatedSince($timestamp, $what, $folderPermissionsSQL, $filePermissionsSQL, $changes);

					// now recurse!
					if ($fullTree || ($depth > 1))
					{
						$ktapi_folder->getChangesRecursive($timestamp, $depth - 1, $what, $user, $folderPermissionsSQL, $filePermissionsSQL, $changes);
					}
	        	}
	        }
        }
    }

	/**
     * Checks whether a folder itself has been deleted
     * Have to make it a static function as cannot get a $this handle on a deleted folder!
     *
     * @return array of folders
     */
    public static function deletedSince($folderID, $timestamp)
    {
    	//$GLOBALS['default']->log->debug("static deletedSince $folderID $timestamp");

    	$contents = array();

        $sQuery = 'SELECT FT.folder_id AS id, FT.datetime AS change_date, FT.parent_id AS parent_id FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT ' .
        'WHERE FT.transaction_namespace = \'ktcore.transactions.delete\' AND FT.folder_id = ? AND FT.datetime >= ? ORDER BY FT.datetime ASC';

        $aParams = array($folderID, $timestamp);

        $results = DBUtil::getResultArray(array($sQuery, $aParams));

        if (!is_null($results) && !PEAR::isError($results))
        {
        	//if there is a result, then it has been deleted!
    		if (count($results) > 0)
	        {
	        	foreach ($results as $result)
	            {
		        	$contents[] = array(
						'id' => $result['id'],
		        		'item_type' => 'F',
		        		'parent_id' => $result['parent_id'],
		        		'changes' => array(
							'change_type' => 'D',
							'change_date' => datetimeutil::getLocaleDate($result['change_date'])
						)
		        	);
	            }
	        }

	        //need to check whether ANY parent folder has been deleted because result will be that this folder has also been deleted
	        //have to do it in this roundabout way since child folder delete transactions are not recorded!
	        //don't do this for the root folder!
	        else if ($folderID > 1)
	        {
		        $sQuery = 'SELECT F.parent_folder_ids FROM '.KTUtil::getTableName('folders').' AS F WHERE F.id = ?';
		        $aParams = array($folderID);

		        $results = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'parent_folder_ids');

		        //$GLOBALS['default']->log->debug('deletedSince results '.print_r($results, true));

		    	if (!is_null($results) && !PEAR::isError($results))
		        {
		        	if (count($results) == 0)
		        	{
		        		$contents[] = array(
							'id' => $folderID,
			        		'item_type' => 'F',
			        		'parent_id' => 'n/a',
			        		'changes' => array(
								'change_type' => 'D',
								'change_date' => 'n/a'
							)
			        	);
		        	}
	        	}
	        }
        }

        //$GLOBALS['default']->log->debug('deletedSince folders '.print_r($contents, true));

        return $contents;
    }

    /**
     * Checks whether a folder's permissions have changed
     * Have to make it a static function as cannot get a $this handle on a folder
     * that a user does not have permissions on!
     *
     * @param unknown_type $folderID
     * @param unknown_type $timestamp
     */
	public static function permissionsRemovedSince($folderID, $timestamp)
    {
    	//$GLOBALS['default']->log->debug("static permissionsRemovedSince $folderID $timestamp");

    	$contents = array();

        $sQuery = 'SELECT FT.folder_id AS id, FT.datetime AS change_date, FT.parent_id AS parent_id FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT ' .
        'WHERE FT.transaction_namespace = \'ktcore.transactions.permissions_change\' AND FT.folder_id = ? AND FT.datetime >= ? ORDER BY FT.datetime DESC LIMIT 1';

        $aParams = array($folderID, $timestamp);

        $results = DBUtil::getResultArray(array($sQuery, $aParams));

        if (!is_null($results) && !PEAR::isError($results))
        {
	        foreach ($results as $result)
	        {
	        	$contents[] = array(
					'id' => $result['id'],
	        		'item_type' => 'F',
	        		'parent_id' => $result['parent_id'],
	        		'changes' => array(
						'change_type' => 'UP',
						'change_date' => datetimeutil::getLocaleDate($result['change_date'])
					)
	        	);
	        }
	    }

        //$GLOBALS['default']->log->debug('permissionsRemovedSince folders '.print_r($contents, true));

        return $contents;
    }

    /**
     * Checks whether a folder's path has changed
     * i.e. whether itself or any parent has been moved or renamed
     *
     * @param unknown_type $timestamp
     */
    public function pathChangedSince($timestamp, &$contents = array())
    {
		$aParentFolderIDs = explode(',', $this->folder->getParentFolderIDs());

        $sParamsPlaceholders = DBUtil::paramArray($aParentFolderIDs);

        $sQuery = 	'SELECT F.id, FT.datetime AS change_date ' .
        			'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id '.
        			'WHERE (FT.transaction_namespace = \'ktcore.transactions.rename\' OR FT.transaction_namespace = \'ktcore.transactions.move\') '.
        			'AND (FT.folder_id = ? OR FT.folder_id IN ( '.$sParamsPlaceholders.' )) AND FT.datetime >= ? ';

        $aParams = array_merge(array($this->folderid), $aParentFolderIDs, array($timestamp));

        $results = DBUtil::getResultArray(array($sQuery, $aParams));

        //$GLOBALS['default']->log->debug('pathChanged results '.print_r($results, true));

        if (!is_null($results) && !PEAR::isError($results))
        {
	    	foreach ($results as $result)
	    	{
	        	//$folder = &Folder::get($result['id']);
				$this->assemble_folder_array($this->folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'UPC',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);

					// $GLOBALS['default']->log->debug('renamedSince assembled contents '.print_r($contents, true));
	        }
        }
    }

	public function renamedSince($timestamp, $folderPermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("renamedSince timestamp $timestamp");

    	$sSelectQuery = 'F.id, FT.datetime AS change_date ' .
        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.rename\' AND FT.folder_id = ? AND FT.datetime >= ? ';

        $aParams = array($this->folderid, $timestamp);

        $aOptions = array('orderby' => 'FT.datetime ASC');

        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);

        if (!is_null($results) && !PEAR::isError($results))
        {
	    	foreach ($results as $result)
	    	{
	        	$folder = &Folder::get($result['id']);
				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'R',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);

					// $GLOBALS['default']->log->debug('renamedSince assembled contents '.print_r($contents, true));
	        }
        }

        //$GLOBALS['default']->log->debug('renamedSince folders '.print_r($contents, true));
    }

	public function movedSince($timestamp, $folderPermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("movedSince timestamp $timestamp");

    	$sSelectQuery = 'F.id, FT.datetime AS change_date ' .
        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.move\' AND FT.folder_id = ? AND FT.datetime >= ? ';

        $aParams = array($this->folderid, $timestamp);

        $aOptions = array('orderby' => 'FT.datetime ASC');

        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);

        $totalResults = array();

    	if (!is_null($results) && !PEAR::isError($results))
        {
            $totalResults = array_merge($totalResults, $results);
        }

        //also have to check whether ANY parent folder has been moved because result will be that this folder has also been moved
        //have to do it in this roundabout way since child moves are not recorded!
        //don't do this for the root folder!
        if ($this->folderid > 1)
        {
        	//$GLOBALS['default']->log->debug('movedSince checking for parents');

	        $sQuery = 'SELECT F.parent_folder_ids FROM '.KTUtil::getTableName('folders').' AS F WHERE F.id = ?';
	        $aParams = array($this->folderid);

	        $results = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'parent_folder_ids');

	        //$GLOBALS['default']->log->debug('movedSince results2 '.print_r($results2, true));

	        if (!is_null($results) && !PEAR::isError($results))
	        {
	        	//get all the parent folder IDs
	        	$folderIDs = explode(',', $results[0]);

	        	//$GLOBALS['default']->log->debug('movedSince exploded folderIDs '.print_r($folderIDs, true));

	        	foreach ($folderIDs as $folderID)
	        	{
	        		$aParams = array($folderID, $timestamp);

	        		$result = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);

	        		if (count($result) > 0)
	        		{
	        			//$GLOBALS['default']->log->debug('movedSince I am set '.print_r($result, true));

	        			$totalResults = array_merge($totalResults, $result);

	        			//don't need to look any further!
	        			break;
	        		}
	        	}
	        }
        }

        if (!is_null($totalResults) && !PEAR::isError($totalResults))
        {
	    	foreach ($totalResults as $result) {
	        	$folder = &Folder::get($result['id']);
				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'M',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);

				// $GLOBALS['default']->log->debug('renamedSince assembled contents '.print_r($contents, true));
	        }
        }

        //$GLOBALS['default']->log->debug('movedSince folders '.print_r($contents, true));
    }

	public function updatedSince($timestamp, $folderPermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("updatedSince timestamp $timestamp");

        $sSelectQuery = 'F.id, FT.datetime AS change_date ' .
        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.permissions_change\' AND FT.folder_id = ? AND FT.datetime >= ? ';

        $aParams = array($this->folderid, $timestamp);

        $aOptions = array('orderby' => 'FT.datetime ASC');

        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);

        if (!is_null($results) && !PEAR::isError($results))
        {
	    	foreach ($results as $result) {
	        	$folder = &Folder::get($result['id']);
				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'UP',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);

				// $GLOBALS['default']->log->debug('renamedSince assembled contents '.print_r($contents, true));
	        }
        }

        //$GLOBALS['default']->log->debug('updatedSince folders '.print_r($contents, true));
    }

	/**
     * Gets the subfolders created since a specific time
     *
     * @return array of folders
     */
    public function childrenCreatedSince($timestamp, $what = 'DF', $folderPermissionsSQL, $filePermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("childrenCreatedSince timestamp $timestamp");

        // need to do folders?
        if (strpos($what, 'F') !== false)
        {
	        $sSelectQuery = 'F.id, FT.datetime AS change_date ' .
	        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

	    	$sWhereQuery = '(FT.transaction_namespace = \'ktcore.transactions.create\' OR FT.transaction_namespace = \'ktcore.transactions.copy\') AND F.parent_id = ? AND FT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'FT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result) {
	        	$folder = &Folder::get($result['id']);

				$this->assemble_folder_array($folder, $contents, $what);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'C',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);
	        }

	        // $GLOBALS['default']->log->debug('createdSince folder result '.print_r($contents, true));
        }

        // need to do documents?
        if (strpos($what, 'D') !== false)
        {
        	$sSelectQuery = 'D.id, DT.datetime AS change_date ' .
	        'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('documents') . ' AS D ON D.id = DT.document_id ';

	    	$sWhereQuery = '(DT.transaction_namespace = \'ktcore.transactions.create\' OR DT.transaction_namespace = \'ktcore.transactions.copy\') AND DT.parent_id = ? AND DT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'DT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $filePermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result) {
	        	$document = &Document::get($result['id']);

    			$this->assemble_document_array($document, $contents);

    			$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'C',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);
	        }

	        // $GLOBALS['default']->log->debug('documentsCreatedSince contents '.print_r($contents, true));
        }
    }

    /**
     * Gets the subfolders deleted since a specific time
     *
     * @return array of folders
     */
    public function childrenDeletedSince($timestamp, $what = 'DF', $folderPermissionsSQL, $filePermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("deletedSince timestamp $timestamp \'$what\'");

    	//need to do folders?
        if (strpos($what, 'F') !== false)
        {
        	$sQuery = 'SELECT FT.folder_id AS id, FT.datetime AS change_date, FT.parent_id AS parent_id ' .
	        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT '.
	    	'WHERE FT.transaction_namespace = \'ktcore.transactions.delete\' AND FT.parent_id = ? AND FT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

        	$results = DBUtil::getResultArray(array($sQuery, $aParams));

	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results);
	        }

	        foreach ($results as $result) {
	        	// can't do this since folder is deleted and thus does not exist in folders table anymore!
	        	/*$oFolder = Folder::get((int)$folderID);
	        	$this->assemble_folder_array($folder, $contents);
	        	$contents[$key]['change_type'] = 'D';
				$contents[$key]['items'] = array();*/

	        	$contents[] = array(
					'id' => $result['id'],
	        		'item_type' => 'F',
	        		'parent_id' => $result['parent_id'],
	        		'changes' => array(
						'change_type' => 'D',
						'change_date' => datetimeutil::getLocaleDate($result['change_date'])
					)
	        	);
	        }

	        //$GLOBALS['default']->log->debug('deletedSince folders '.print_r($contents, true));
        }

        //TODO: what about archived documents? Or is that picked up by documents modified?
        //need to do documents?
        if (strpos($what, 'D') !== false)
        {
	        $sSelectQuery = 'D.id, DT.datetime AS change_date, DT.parent_id AS parent_id '.
	        'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('documents') . ' AS D ON D.id = DT.document_id ';

	    	$sWhereQuery = 'DT.transaction_namespace = \'ktcore.transactions.delete\' AND DT.parent_id = ? AND DT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'DT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $filePermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result) {
	        	$contents[] = array(
					'id' => $result['id'],
	        		'item_type' => 'D',
	        		'parent_id' => $result['parent_id'],
	        		'changes' => array(
						'change_type' => 'D',
						'change_date' => datetimeutil::getLocaleDate($result['change_date'])
	        		)
	        	);

	        	/*$oDocument = &Document::get($document['id']);

	        	if (KTPermissionUtil::userHasPermissionOnItem($user, $read_permission, $oDocument)) {

	        		// TODO: need to do this? Rather do the same as for folders?
	        		$this->assemble_document_array($oDocument, $contents);

	    			$contents[count($contents) - 1]['changes'] = array(
						'change_type' => 'D',
						'change_date' => $document['change_date']
					);
	    		}*/
	        }

	        //$GLOBALS['default']->log->debug('deletedSince documents '.print_r($contents, true));
        }
    }

    /**
     * Gets the subfolders renamed since a specific time
     *
     * @return array of folders
     */
    public function childrenRenamedSince($timestamp, $what = 'DF', $folderPermissionsSQL, $filePermissionsSQL, &$contents = array())
    {
    	// $GLOBALS['default']->log->debug("renamedSince timestamp $timestamp");

        // need to do folders?
        if (strpos($what, 'F') !== false)
        {
        	$sSelectQuery = 'F.id, FT.datetime AS change_date ' .
	        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

	    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.rename\' AND F.parent_id = ? AND FT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'FT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results);
	        }

	    	foreach ($results as $result) {
	        	$folder = &Folder::get($result['id']);

				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'R',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);

				// $GLOBALS['default']->log->debug('renamedSince assembled contents '.print_r($contents, true));
	        }

	        // $GLOBALS['default']->log->debug('renamedSince result '.print_r($contents, true));
        }

        // need to do documents?
        if (strpos($what, 'D') !== false)
        {
	        $sSelectQuery = 'D.id, DT.datetime AS change_date '.
	        'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('documents') . ' AS D ON D.id = DT.document_id ';

	    	$sWhereQuery = 'DT.transaction_namespace = \'ktcore.transactions.rename\' AND DT.parent_id = ? AND DT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'DT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $filePermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        $read_permission = &KTPermission::getByName(KTAPI_PERMISSION_READ);
	        $user = $this->ktapi->get_user();

	        foreach ($results as $result) {
	        	$document = &Document::get($result['id']);

    			$this->assemble_document_array($document, $contents);

    			$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'R',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);
	        }
        }
    }

	/**
     * Gets the subfolders moved since a specific time
     *
     * @return array of folders
     */
    public function childrenMovedSince($timestamp, $what = 'DF', $folderPermissionsSQL, $filePermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("childrenMovedSince timestamp $timestamp '$what'");

        // need to do folders?
        if (strpos($what, 'F') !== false)
        {
        	$sSelectQuery = 'F.id, FT.datetime AS change_date, FT.parent_id AS transaction_parent_id ' .
	        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

	    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.move\' AND FT.parent_id = ? AND FT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'FT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results);
	        }

	        foreach ($results as $result) {
	        	$folder = &Folder::get($result['id']);

				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'M',
					'change_date' => datetimeutil::getLocaleDate($result['change_date']),
					'previous_parent_id' => $result['transaction_parent_id']
				);

				//$GLOBALS['default']->log->debug('movedSince assembled contents '.print_r($contents, true));
	        }

	        //$GLOBALS['default']->log->debug('childrenMovedSince folders '.print_r($contents, true));
        }

    	// need to do documents?
        if (strpos($what, 'D') !== false)
        {
	        $sSelectQuery = 'D.id, DT.datetime AS change_date, DT.parent_id AS transaction_parent_id '.
	        'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('documents') . ' AS D ON D.id = DT.document_id ';

	    	$sWhereQuery = 'DT.transaction_namespace = \'ktcore.transactions.move\' AND DT.parent_id = ? AND DT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'DT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $filePermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result) {
	        	$document = &Document::get($result['id']);

    			$this->assemble_document_array($document, $contents);

    			$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'M',
					'change_date' => datetimeutil::getLocaleDate($result['change_date']),
    				'previous_parent_id' => $result['transaction_parent_id']
				);
	        }
        }
    }

    /**
     * Gets the subfolders moved since a specific time
     *
     * @return array of folders
     */
    public function childrenUpdatedSince($timestamp, $what = 'DF', $folderPermissionsSQL, $filePermissionsSQL, &$contents = array())
    {
    	//$GLOBALS['default']->log->debug("childrenUpdatedSince $timestamp $what");

    	// need to do folders?
        if (strpos($what, 'F') !== false)
        {
	       	$sSelectQuery = 'F.id, FT.datetime AS change_date, FT.transaction_namespace AS change_type ' .
	        'FROM ' . KTUtil::getTableName('folder_transactions') . ' AS FT INNER JOIN ' . KTUtil::getTableName('folders') . ' AS F ON F.id = FT.folder_id ';

	    	$sWhereQuery = 'FT.transaction_namespace = \'ktcore.transactions.permissions_change\' AND FT.parent_id = ? AND FT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'FT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $folderPermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results);
	        }

	        foreach ($results as $result) {
	        	$folder = &Folder::get($result['id']);

				$this->assemble_folder_array($folder, $contents);

				$contents[count($contents) - 1]['changes'] = array(
					'change_type' => 'UP',
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);
				// $GLOBALS['default']->log->debug('updatedSince assembled contents '.print_r($contents, true));
	        }

	        // $GLOBALS['default']->log->debug('updatedSince folders '.print_r($contents, true));
        }

    	// need to do documents?
        if (strpos($what, 'D') !== false)
        {
	    	//TODO: ktcore.transactions.ownership_change?

	    	$sSelectQuery = 'D.id, DT.datetime AS change_date, DT.transaction_namespace AS change_type, DT.comment AS comment '.
	        'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('documents') . ' AS D ON D.id = DT.document_id ';

	    	$sWhereQuery = 'DT.transaction_namespace IN (\'ktcore.transactions.update\', \'ktcore.transactions.check_in\', \'ktcore.transactions.check_out\', '.
	    		'\'ktcore.transactions.force_checkin\', \'ktcore.transactions.immutable\', \'ktcore.transactions.permissions_change\') '.
	    		'AND DT.parent_id = ? AND DT.datetime >= ? ';

	        $aParams = array($this->folderid, $timestamp);

	        $aOptions = array('orderby' => 'DT.datetime ASC');

	        $results = $this->buildChangesSQL($sSelectQuery, $sWhereQuery, $filePermissionsSQL, $aParams, $aOptions);
	        if (is_null($results) || PEAR::isError($results))
	        {
	            return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $results );
	        }

	        foreach ($results as $result) {
	        	$document = &Document::get($result['id']);

    			$this->assemble_document_array($document, $contents);

    			//$GLOBALS['default']->log->debug('updatedSince change type '.$result['change_type']);

	        	//extract the change type
				switch($result['change_type'])
				{
					case 'ktcore.transactions.update':
						$changeType = 'U';
						//$GLOBALS['default']->log->debug('updatedSince comment '.$result['comment']);

						//this type is a bit generic, but the only way to refine it is to parse
						//the comment!
						if (strpos(strtolower($result['comment']), 'archived') !== false)
						{
							//$GLOBALS['default']->log->debug('updatedSince ARCHIVED');
							$changeType = 'UA';
						}
						if (strpos(strtolower($result['comment']), 'restored') !== false)
						{
							//$GLOBALS['default']->log->debug('updatedSince RESTORED');
							$changeType = 'UR';
						}
						if (strpos(strtolower($result['comment']), 'metadata updated') !== false)
						{
							//$GLOBALS['default']->log->debug('updatedSince METADATA UPDATED');
							$changeType = 'UM';
						}
						break;
					case 'ktcore.transactions.check_in':
						$changeType = 'UCI';
						break;
					case 'ktcore.transactions.check_out':
						$changeType = 'UCO';
						break;
					case 'ktcore.transactions.force_checkin':
						$changeType = 'UCI';
						break;
					case 'ktcore.transactions.immutable':
						$changeType = 'UI';
						break;
					case 'ktcore.transactions.permissions_change':
						$changeType = 'UP';
						break;
					default:
						$changeType = 'U';
				}

				//$GLOBALS['default']->log->debug("updatedSince extracted change type $changeType");

    			$contents[count($contents) - 1]['changes'] = array(
					'change_type' => $changeType,
					'change_date' => datetimeutil::getLocaleDate($result['change_date'])
				);
	        }
        }

        // $GLOBALS['default']->log->debug('updatedSince contents '.print_r($contents, true));
    }

    /**
     *
     * @param $user
     */
    private function buildChangesSQL($sSelectSQL, $sWhereSQL, $aPermissionsSQL, $aSQLParams, $aOptions)
    {
    	//$GLOBALS['default']->log->debug("buildChangesSQL $sSelectSQL $sWhereSQL ".print_r($aPermissionsSQL, true));

        list($sPermissionSQL, $aPermissionParams, $sPermissionJoin) = $aPermissionsSQL;

        //$GLOBALS['default']->log->debug("buildChangesSQL PermissionSQL: $sPermissionSQL permissionParams: ".print_r($aPermissionParams, true)." permissionJoin: $sPermissionJoin");

        $sOptionSQL = DBUtil::getDbOptions($aOptions);

        $sSQL = "SELECT $sSelectSQL $sPermissionJoin WHERE $sPermissionSQL AND $sWhereSQL $sOptionSQL";

        //$GLOBALS['default']->log->debug("buildChangesSQL sql: $sSQL");

        $results = DBUtil::getResultArray(array($sSQL, array_merge($aPermissionParams, $aSQLParams)));

        return $results;
    }

    /**
     * Compares the changes array as follows
     *
     * id ASC
     * if ids are equal, then date changes made ASC
     *
     * @param $a
     * @param $b
     */
	private function compare_changes($a, $b)
	{
		if ($a['id'] == $b['id'])
		{
			// ($a['change_type'] == 'F' && $b['change_type'] == 'D') ? -1 : (($a['change_type'] == 'D' && $b['change_type'] == 'F') ? 1);

			// we need to compare item_types as well since could get a doc and a folder which have the same id!
			if ($a['item_type'] == 'F' && $b['item_type'] == 'D')
			{
				return -1;
			}

			if ($a['item_type'] == 'D' && $b['item_type'] == 'F')
			{
				return 1;
			}

			if ($a['changes']['change_date'] == $b['changes']['change_date'])
			{
				return 0;
			}

			return ($a['changes']['change_date'] < $b['changes']['change_date']) ? -1 : 1;
		}

		return ($a['id'] < $b['id']) ? -1 : 1;
	}


	/**
	 * Assembles/constructs the array used in getting a folder's details
	 *
	 * Uses an array passed by reference to recursively build up the folder details
	 *
	 * @param object $folder
	 * @param array $contents
	 * @param string $what
	 * @param array $items
	 */
	private function assemble_folder_array($folder, &$contents, $what = 'F', $items = array())
	{
		$wsversion = $this->getWSVersion();

		$created_by = $this->_resolve_user($folder->getCreatorID());
		$created_date = $folder->getDisplayCreatedDateTime();
		if (empty($created_date)) {
			$created_date = 'n/a';
		}

		$modified_by = $this->_resolve_user($folder->getModifiedUserID());
		$modified_date = $folder->getDisplayLastModifiedDate();
		if (empty($modified_date)) {
				$modified_date = 'n/a';
		}

		$owned_by = $this->_resolve_user($folder->getOwnerID());

		if ($wsversion >= 2) {
		    $detail = array(
				'id' => (int)$folder->getId(),
				'item_type' => 'F',
				'custom_document_no' => 'n/a',
				'oem_document_no' => 'n/a',
				'title' => KTUtil::checkEncoding($folder->getName()),
				'document_type' => 'n/a',
				'filename' => KTUtil::checkEncoding($folder->getName()),
				'filesize' => 'n/a',
				'created_by' => is_null($created_by) ? 'n/a' : $created_by->getName(),
				'created_date' => $created_date,
				'checked_out_by' => 'n/a',
				'checked_out_date' => 'n/a',
				'modified_by' => is_null($modified_by) ? 'n/a' : $modified_by->getName(),
				'modified_date' => $modified_date,
				'owned_by' => is_null($owned_by) ? 'n/a' : $owned_by->getName(),
				'version' => 'n/a',
				'is_immutable' => 'n/a',
				'permissions' => KTAPI_Folder::get_permission_string($folder),
				'workflow' => 'n/a',
				'workflow_state' => 'n/a',
				'mime_type' => 'folder',
				'mime_icon_path' => 'folder',
				'mime_display' => 'Folder',
				'storage_path' => 'n/a',
				'full_path' => $folder->getFullPath()
			);

		    if ($wsversion >= 3) {
		        $detail['parent_id'] = (int)$folder->getParentID();
				$detail['linked_folder_id'] = $folder->getLinkedFolderId();

				if ($folder->isSymbolicLink()) {
					$detail['item_type'] = 'S';
				}

				$detail['has_rendition'] = 'n/a';
				$detail['clean_uri'] = KTBrowseUtil::getUrlForfolder($folder);
				$detail['created_by_user_name'] = is_null($created_by) ? 'n/a' : $created_by->getUserName();
				$detail['modified_by_user_name'] = is_null($modified_by) ? 'n/a' : $modified_by->getUserName();
				$detail['checked_out_by_user_name'] = 'n/a';
				$detail['owned_by_user_name'] = is_null($owned_by) ? 'n/a' : $owned_by->getUserName();
		    }

		    $detail['items'] = $items;

		    if ($wsversion < 3 || ((strpos($what, 'F') !== false) && !$folder->isSymbolicLink()) || ($folder->isSymbolicLink() && (strpos($what, 'S') !== false))) {
		        $contents[] = $detail;
		    }
		}
		else {
		    $contents[] = array(
		    'id' =>(int) $folder->getId(),
		    'item_type' => 'F',
		    'title' => KTUtil::checkEncoding($folder->getName()),
		    'creator' => is_null($created_by) ? 'n/a' : $created_by->getName(),
		    'checkedoutby' => 'n/a',
		    'modifiedby' => 'n/a',
		    'filename' => KTUtil::checkEncoding($folder->getName()),
		    'size' => 'n/a',
		    'major_version' => 'n/a',
		    'minor_version' => 'n/a',
		    'storage_path' => 'n/a',
		    'mime_type' => 'folder',
		    'mime_icon_path' => 'folder',
		    'mime_display' => 'Folder',
		    'items' => $items,
		    'workflow' => 'n/a',
		    'workflow_state' => 'n/a'
		    );
		    // NOTE merged version had nothing here anymore - check to see whether setting it here breaks anything...
		}
	}

	/**
	 * Assembles/constructs the array used in getting a document's details
	 *
	 * Uses an array passed by reference to recursively build up the folder details
	 *
	 * @param object $document
	 * @param array $contents
	 * @param string $what
	 */
	private function assemble_document_array($document, &$contents, $what = 'D')
	{
		// $GLOBALS['default']->log->debug('assemble_document_array '.print_r($document, true));

		$wsversion = $this->getWSVersion();
		$mime_cache = array();

        $created_by = $this->_resolve_user($document->getCreatorID());
		$created_date = $document->getDisplayCreatedDateTime();
		if (empty($created_date)) {
			$created_date = 'n/a';
		}

		$checked_out_by_id = $document->getCheckedOutUserID();
		$checked_out_by = $this->_resolve_user($checked_out_by_id);
		$checked_out_date = $document->getDisplayCheckedOutDate();
		if (empty($checked_out_date)) {
			$checked_out_date = 'n/a';
		}

		$modified_by = $this->_resolve_user($document->getModifiedUserId());
		$modified_date = $document->getDisplayLastModifiedDate();
		if (empty($modified_date)) {
			$modified_date = 'n/a';
		}

		$owned_by = $this->_resolve_user($document->getOwnerID());

        $owned_by = $this->_resolve_user($document->getOwnerID());

        $mimetypeid = $document->getMimeTypeID();
		if (!array_key_exists($mimetypeid, $mime_cache))
		{
			$type = KTMime::getMimeTypeName($mimetypeid);
			$icon = KTMime::getIconPath($mimetypeid);
			$display = KTMime::getFriendlyNameForString($type);
			$mime_cache [$mimetypeid] = array('type' => $type, 'icon' => $icon, 'display' => $display);
		}
		$mimeinfo = $mime_cache[$mimetypeid];

        $workflow = 'n/a';
		$state = 'n/a';
		$wf = KTWorkflowUtil::getWorkflowForDocument($document);
		if (!is_null($wf) && !PEAR::isError($wf)) {
			$workflow = $wf->getHumanName();
			$ws = KTWorkflowUtil::getWorkflowStateForDocument($document);
			if (!is_null($ws) && !PEAR::isError($ws)) {
				$state = $ws->getHumanName();
			}
		}

		if ($wsversion >= 2) {
            $docTypeId = $document->getDocumentTypeID();
			$documentType = DocumentType::get($docTypeId);

			$oemDocumentNo = $document->getOemNo();
			if (empty($oemDocumentNo)) {
				$oemDocumentNo = 'n/a';
			}

		    $detail = array(
		    'id' =>(int) $document->getId(),
		    'item_type' => 'D',
		    'custom_document_no' => 'n/a',
		    'oem_document_no' => $oemDocumentNo,
		    'title' => KTUtil::checkEncoding($document->getName()),
		    'document_type' => $documentType->getName(),
		    'filename' => KTUtil::checkEncoding($document->getFileName()),
		    'filesize' => $document->getFileSize(),
		    'created_by' => is_null($created_by) ? 'n/a' : $created_by->getName(),
		    'created_date' => $created_date,
		    'checked_out_by' => is_null($checked_out_by) ? 'n/a' : $checked_out_by->getName(),
		    'checked_out_by_id' => $checked_out_by_id,
		    'checked_out_date' => $checked_out_date,
		    'modified_by' => is_null($modified_by) ? 'n/a' : $modified_by->getName(),
		    'modified_date' => $modified_date,
		    'owned_by' => is_null($owned_by) ? 'n/a' : $owned_by->getName(),
		    'version' => $document->getMajorVersionNumber() . '.' . $document->getMinorVersionNumber(),
		    'content_id' => $document->getContentVersionId(),
		    'is_immutable' => $document->getImmutable() ? 'true' : 'false',
		    'permissions' => KTAPI_Document::get_permission_string($document),
		    'workflow' => $workflow,
		    'workflow_state' => $state,
		    'mime_type' => $mime_cache[$mimetypeid]['type'],
		    'mime_icon_path' => $mime_cache[$mimetypeid]['icon'],
		    'mime_display' => $mime_cache[$mimetypeid]['display'],
		    'storage_path' => $document->getStoragePath()
		    );

		    if ($wsversion >= 3) {
                $document->switchToRealCore();
				$detail['linked_document_id'] = $document->getLinkedDocumentId();
				$document->switchToLinkedCore();
				if ($document->isSymbolicLink()) {
					$detail['item_type'] = 'S';
				}

				$detail['parent_id'] = (int)$this->folderid;
				$detail['has_rendition'] = $document->getHasRendition();
				$detail['clean_uri'] = KTBrowseUtil::getUrlForDocument($document);
				$detail['created_by_user_name'] = is_null($created_by) ? 'n/a' : $created_by->getUserName();
				$detail['modified_by_user_name'] = is_null($modified_by) ? 'n/a' : $modified_by->getUserName();
				$detail['checked_out_by_user_name'] = is_null($checked_out_by) ? 'n/a' : $checked_out_by->getUserName();
				$detail['owned_by_user_name'] = is_null($owned_by) ? 'n/a' : $owned_by->getUserName();
		    }

		    $detail['items'] = array();

		    if ($wsversion < 3 || ((strpos($what, 'D') !== false) && !$document->isSymbolicLink()) || ($document->isSymbolicLink() && (strpos($what, 'S') !== false))) {
		        $contents[] = $detail;
		    }
		}
		else {
		    $contents[] = array(
		    'id' =>(int) $document->getId(),
		    'item_type' => 'D',
		    'title' => KTUtil::checkEncoding($document->getName()),
		    'creator' => is_null($created_by) ? 'n/a' : $created_by->getName(),
		    'checkedoutby' => is_null($checked_out_by) ? 'n/a' : $checked_out_by->getName(),
		    'modifiedby' => is_null($modified_by) ? 'n/a' : $modified_by->getName(),
		    'filename' => KTUtil::checkEncoding($document->getFileName()),
		    'size' => $document->getFileSize(),
		    'major_version' => $document->getMajorVersionNumber(),
		    'minor_version' => $document->getMinorVersionNumber(),
		    'storage_path' => $document->getStoragePath(),
		    'mime_type' => $mime_cache[$mimetypeid]['type'],
		    'mime_icon_path' => $mime_cache[$mimetypeid]['icon'],
		    'mime_display' => $mime_cache[$mimetypeid]['display'],
		    'items' => array(),
		    'workflow' => $workflow,
		    'workflow_state' => $state
		    );
		}
	}

}

?>

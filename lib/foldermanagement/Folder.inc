<?php
/**
 * $Id$
 *
 * Represents as folder as the per the folders table in the database.
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
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . "/foldermanagement/PhysicalFolderManagement.inc");
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class Folder extends KTEntity {

	/** folder primary key */
	var $iId;
	/** folder name */
	var $sName;
	/** folder description */
	var $sDescription;
	/** folder parent primary key */
	var $iParentID;
	/** primary key of user who created folder */
	var $iCreatorID;
    /** The creation time of the folder */
    var $dCreated;
    /** The user that last modified the folder */
    var $iModifiedUserID;
    /** The time of the last modification to the folder */
    var $dModified;
	/** public status of folder */
	var $bIsPublic = false;
	/** comma deliminated string of parent ids */
	var $sParentFolderIDs;
	/** forward slash deliminated path from file system root */
	var $sFullPath;
    /** which permission object I get permissions from */
    var $iPermissionObjectID;
    /** lookup accelerator id */
    var $iPermissionLookupID;
    /** whether to restrict to only certain document types */
    var $bRestrictDocumentTypes = false;
    /** ID of folder owner */
    var $iOwnerID;
    /** ID of the folder this is a shortcut to(if any) */
    var $iLinkedFolderId;

    // {{{ KTEntity stuff
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'sDescription' => 'description',
        'iParentID' => 'parent_id',
        'iCreatorID' => 'creator_id',
        'dCreated' => 'created',
        'iModifiedUserID' => 'modified_user_id',
        'dModified' => 'modified',
        'bIsPublic' => 'is_public',
        'sFullPath' => 'full_path',
        'sParentFolderIDs' => 'parent_folder_ids',
        'iPermissionObjectID' => 'permission_object_id',
        'iPermissionLookupID' => 'permission_lookup_id',
        'bRestrictDocumentTypes' => 'restrict_document_types',
    	'iLinkedFolderId' => 'linked_folder_id',
    	'iOwnerID' => 'owner_id',
    );
    // }}}

	function getID() { return $this->iId; }
	function getName() { return ($this->sName); }
	function setName($sNewValue) { $this->sName = ($sNewValue); }
	function getDescription() { return ($this->sDescription); }
	function setDescription($sNewValue) { $this->sDescription = ($sNewValue); }
	function getParentID() { return $this->iParentID; }
	function setParentID($iNewValue) { $this->iParentID = $iNewValue; }
	function getCreatorID() { return $this->iCreatorID; }
	function setCreatorID($iNewValue) { $this->iCreatorID = $iNewValue; }

    function getCreatedDateTime() { return $this->dCreated; }
    function getModifiedUserID() { return $this->iModifiedUserID; }
    function setModifiedUserID($iNewValue) { $this->iModifiedUserID = $iNewValue; }
    function getLastModifiedDate() { return $this->dModified; }
    function setLastModifiedDate($dNewValue) { $this->dModified = $dNewValue; }
    function getOwnerID() { return $this->iOwnerID; }
    function setOwnerID($iNewValue) { $this->iOwnerID = $iNewValue; }

	function getIsPublic() { return $this->bIsPublic; }
	function setIsPublic($bNewValue) { $this->bIsPublic = $bNewValue; }
	function getFullPath() { return $this->sFullPath; }
	function getParentFolderIDs() { return $this->sParentFolderIDs; }

	function getPermissionObjectID() { return $this->iPermissionObjectID; }
	function setPermissionObjectID($iPermissionObjectID) { $this->iPermissionObjectID = $iPermissionObjectID; }
	function getPermissionLookupID() { return $this->iPermissionLookupID; }
	function setPermissionLookupID($iPermissionLookupID) { $this->iPermissionLookupID = $iPermissionLookupID; }

	function getRestrictDocumentTypes() { return $this->bRestrictDocumentTypes; }
	function setRestrictDocumentTypes($bRestrictDocumentTypes) { $this->bRestrictDocumentTypes = $bRestrictDocumentTypes; }

	function getLinkedFolderId(){ return $this->iLinkedFolderId;}
	function setLinkedFolderId($iNewValue){ $this->iLinkedFolderId = $iNewValue;}

    // {{{ create()
    function create () {
        $oParentFolder =& Folder::get($this->iParentID);
        $this->iPermissionObjectID = $oParentFolder->getPermissionObjectID();
        
        if (empty($this->dCreated)) {
            $this->dCreated = getCurrentDateTime();
        }
        if (empty($this->dModified)) {
            $this->dModified = getCurrentDateTime();
        }
        if (empty($this->iModifiedUserID)) {
            $this->iModifiedUserID = $this->iCreatorID;
        }
        if (empty($this->iOwnerID)) {
            $this->iOwnerID = $this->iCreatorID;
        }

        $res = parent::create();
        if ($res === true) {
            KTPermissionUtil::updatePermissionLookup(Folder::get($this->getId()));
        }
        return $res;
    }
    // }}}

    function loadFromArray ($aOptions) {
    	parent::loadFromArray($aOptions);

    	//now load fields from the folder this folder is linking to, if any.
    	if($this->isSymbolicLink()){
    		$oLinkedFolder = $this->getLinkedFolder();

            if(PEAR::isError($oLinkedFolder)){
                return PEAR::raiseError(_kt("Linked folder can't be found: ") . $oLinkedFolder->getMessage());
            }

    		$this->sName = $oLinkedFolder->getName();
    		$this->sDescription = $oLinkedFolder->getDescription();
    	}

    }

        /**
         * Returns a comma delimited string containing the parent folder ids, strips leading /
         *
         * @return String  comma delimited string containing the parent folder ids
         */
        function generateFolderIDs($iFolderId) {
            if (empty($iFolderId)) {
                    return;
            }
            $oFolder =& Folder::get($iFolderId);
            if (PEAR::isError($oFolder)) {
                return $oFolder;
            }
            $iParentId = $oFolder->getParentId();
            if (empty($iParentId)) {
                return $oFolder->getId();
            }
            $oParentFolder =& Folder::get($iParentId);
            if (PEAR::isError($oParentFolder)) {
                return $oParentFolder;
            }
            $sParentFolderParentFolderIds = $oParentFolder->getParentFolderIDs();
            if (empty($sParentFolderParentFolderIds)) {
                return sprintf('%s,%s', $iParentId, $oFolder->getId());;
            }
            return sprintf('%s,%s,%s', $sParentFolderParentFolderIds, $iParentId, $oFolder->getId());
        }

	/**
	 * Recursively generates forward slash deliminated string giving full path of document
	 * from file system root url
	 */
	function generateFullFolderPath($iFolderId) {
		//if the folder is not the root folder
		if ($iFolderId == 0) {
            return;
        }
		if ($iFolderId == 1) {
            $oFolder =& Folder::get(1);
            return $oFolder->getName();
        }

        $oFolder =& Folder::get($iFolderId);
		if (PEAR::isError($oFolder)) {
            global $default;
            $default->log->error("Invalid folder passed to generateFullFolderPath: %s", print_r($oFolder, true));
            return $oFolder;
        }
        $iParentId = $oFolder->getParentId();
        if (empty($iParentId)) {
            return $oFolder->getName();
        }
        $res = Folder::generateFullFolderPath($iParentId);
        if (PEAR::isError($res)) {
            return $res;
        }
        return sprintf('%s/%s', $res, $oFolder->getName());
	}

	/**
	 * Returns a forward slash deliminated string giving full path of document, strips leading /
	 */
	function generateFolderPath($iFolderID) {
		$sPath = Folder::generateFullFolderPath($iFolderID);
		return $sPath;
	}

    function _fieldValues () {
    	if ($this->getId() == 1)
    	{
			$this->sFullPath = null;
			$this->sParentFolderIDs = null;
    	}
    	else
    	{
    		$parent = Folder::get($this->iParentID);
        	$this->sFullPath = $parent->getFullPath();
        	if (!empty($this->sFullPath)) $this->sFullPath .= '/';
        	$this->sFullPath .= $this->sName;
        	$this->sParentFolderIDs = $parent->getParentFolderIDs();
			if (!empty($this->sParentFolderIDs)) $this->sParentFolderIDs .= ',';
        	$this->sParentFolderIDs .= $this->iParentID;
    	}
        return parent::_fieldValues();
        // NOTE below no longer needed?
        return array(
            'name' => $this->sName,
            'description' => $this->sDescription,
            'parent_id' => $this->iParentID,
            'creator_id' => $this->iCreatorID,
            'created' => $this->dCreated,
            'iModifiedUserId' => $this->iModifiedUserId,
            'modified' => $this->dModified,
            'is_public' => KTUtil::anyToBool($this->bIsPublic),
            'full_path' => $this->sFullPath,
            'parent_folder_ids' => $this->sParentFolderIDs,
            'permission_object_id' => $this->iPermissionObjectID,
            'permission_lookup_id' => $this->iPermissionLookupID,
        );
    }

    function _table () {
        global $default;
        return $default->folders_table;
    }

	/**
	* Update the current folder values in the database
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*/
	function update($bPathChange = false) {
        $res = parent::update();
        if ($res === true) {
            if ($bPathChange) {
                // XXX: TransactionCheckPoint
                $this->updateChildPaths($this->iId);
                $this->updateDocumentPaths($this->iId);
            }
        }
        return $res;
	}

	function renameFolder($sOldPath) {
		PhysicalFolderManagement::renameFolder($sOldPath, $default->documentRoot . "/" . $this->sFullPath . "/" . $this->sName);
	}

	/**
	* When a folder is renamed, we must update
	* the paths of the children in the database
	*
	*/
	function updateChildPaths($iId) {
		global $default;
		//get the direct children
        $sql = $default->db;
		$aFolders =& Folder::getByParentId($iId);
		foreach ($aFolders as $oFolder) {
			$oFolder->update(true);
		}
		return;
	}

    /**
     * When a folder's path changes, we must update the paths in the
     * documents in that folder.  Sub-folders are handled elsewhere in
     * update().
     */

    function updateDocumentPaths($iId) {
        $aDocuments = Document::getList(array('folder_id = ?', $iId));
        if (PEAR::isError($aDocuments)) {
            return $aDocuments;
        }
        foreach ($aDocuments as $oDocument) {
            // Document->update() automatically adjusts the path.
            $oDocument->update();
            // XXX: Should handle failure here somehow, but rather get
            // most working than just the first two.  Must find a sane
            // way to handle transactions.
            // TransactionCheckPoint
        }
        return true;
    }

    /**
     * Returns the documents in this folder
     */
    function getDocumentIDs($iFolderID) {
		$sTable = KTUtil::getTableName('documents');
		$aQuery = array("SELECT id FROM {$sTable} WHERE folder_id = ? AND status_id = ?", array($iFolderID, LIVE));
		$res = DBUtil::getResultArrayKey($aQuery,'id');
		if (PEAR::isError($res)) {
		    return $res;
		}
		return implode(',', $res);
    }

    function &get($iFolderID) {
        return KTEntityUtil::get('Folder', $iFolderID);
    }

	/**
	 * Checks if a folder with the same name and parent exists in the database already
	 */
	function exists() {
        $sTable = KTUtil::getTableName('folders');
        $sQuery = "SELECT count(*) as folder_count FROM $sTable WHERE name = ? AND parent_id = ?";/*ok*/
        $aParams = array($this->sName, $this->iParentID);
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'folder_count');
        if (PEAR::isError($res)) {
            return false; // return $res
        }
        return ($res != 0); // handle pre-existing duplicates gracefully.
	}

	/**
	 * Checks if this folder is a symbolic link
	 *
	 * @return boolean
	 */
	function isSymbolicLink()
    {
        return !is_null($this->getLinkedFolderId());
    }

    /**
     * Retrieves the folder this folder links to, if any.
     *
     * @return Folder
     */
    function getLinkedFolder(){
    	if($this->isSymbolicLink()){
    		return Folder::get($this->getLinkedFolderId());
    	}
    }

    /**
     * Returns the "Symbolic link" folder
     *
     * @return Folder the folder
     */
	function getRealFolder(){
		if (is_null($this->getLinkedFolderId()))
        {
            return Folder::get($this->getId());
        }

        $oFolder = Folder::get($this->getLinkedFolderId());
        return $oFolder->getRealFolder();
	}

	/**
	 * Returns the ID of the "Symbolic link" folder
	 *
	 * @return int the ID
	 */
	function getRealFolderId(){
    	$oRealFolder = $this->getRealFolder();
    	return $oRealFolder->getId();
    }

    /**
     * Returns the symbolic links linking to this folder
     *
     * @return Array array with folder IDs
     */
    function getSymbolicLinks(){
    	$sQuery = 'SELECT * FROM folders ' .
				'WHERE folders.linked_folder_id =  '.$this->getId();
    	return DButil::getResultArray($sQuery);
    }

	/**
    * Static function
    * Get a list of Documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Documents objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null, $aOptions = null) {
        return KTEntityUtil::getList2('Folder', $sWhereClause, $aOptions);
    }

	/**
	* Static function.
	* Get the full path for a folder
	*
	* @param 	Primary key of folder to generate path for
	*
	* @return String full path of folder
	*/
	function getFolderPath($iFolderID) {
		global $default;
		$oFolder = Folder::get($iFolderID);
		$sPath = $default->documentRoot . "/" . $oFolder->getFullPath() . "/" . $oFolder->getName() . "/";
		return $sPath;
	}

	/**
     * Static function.
     * Get the full path for a folder as an array
     *
     * @param int primary key of folder to generate path for
     *
     * @return array full path of folder as an array of folderIDs
     */
    function getFolderPathNamesAsArray($iFolderID) {
		global $default;

		$oFolder = Folder::get($iFolderID);
		$aPathArray = array();
		if ($oFolder) {
			if (strlen($oFolder->getFullPath()) > 0) {
				if (strlen($oFolder->getFullPath()) > 1) {
					$aPathArray = explode("/",$oFolder->getFullPath());
				} else {
					$aPathArray = array($oFolder->getFullPath());
				}

				//$aPathArray[count($aPathArray)] = $oFolder->getName();
			} else {
				$aPathArray = array($oFolder->getName());
			}
		}
		return $aPathArray;
    }

    // {{{
    function getPathArray() {
        return Folder::getFolderPathNamesAsArray($this->getID());
    }
    // }}}

	/**
     * Static function.
     * Get the full path for a folder as an array
     *
     * @param int primary key of folder to generate path for
     *
     * @return array full path of folder as an array of folderIDs
     */
    function getFolderPathAsArray($iFolderID) {
		global $default;
		$oFolder = Folder::get($iFolderID);
        if ($oFolder === false) {
            return false;
        }
		if (strlen($oFolder->getParentFolderIDs()) > 0) {
            if ($oFolder->iParentID == 0) {
                $aPathArray = array();
            } else if (strlen($oFolder->getParentFolderIDs()) > 1) {
				$aPathArray = explode(",",$oFolder->getParentFolderIDs());
			} else {
				$aPathArray = array($oFolder->getParentFolderIDs());
			}
			$aPathArray[count($aPathArray)] = $oFolder->getID();
		} else {
			$aPathArray = array($oFolder->getID());
		}
		return $aPathArray;
    }

	/**
	* Static function.
	* Get the path for a folder that will be displated to the user
	*
	* @param 	Primary key of folder to generate path for
	*
	* @return String full path of folder
	*/
	function getFolderDisplayPath($iFolderID) {
		global $default;
        $aPathNamesArray = Folder::getFolderPathNamesAsArray($iFolderID);

        foreach($aPathNamesArray as $k=>$v)
        {
            $aPathNamesArray[$k] = sanitizeForHTML($v);
        }
        if (count($aPathNamesArray) > 0) {
        	return implode(" &raquo; ", $aPathNamesArray);
        } else {
        	return "";
        }
	}

	/**
	* Static function
	* Get the primary key of the parent folder
	*
	* @param $iFolderID		Primary key of folder to get parent for
	*
	* @return integer primary key of parent folder
	*/
	function getParentFolderID($iFolderID) {
		if ($iFolderID != 0) {
		    $oFolder = Folder::get($iFolderID);
			return $oFolder->getParentFolderID();
		}
		return 0;
	}

	/**
	* Static function
	* Checks if a given folder already exists using the folder name
	*
	* @param $sName			Name of folder
	* @param $iParentID		Primary key of parent folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsName($sName, $iParentID) {
        $sQuery = "SELECT id, name FROM " . KTUtil::getTableName('folders') . " WHERE name = ? AND parent_id = ?";/*ok*/
        $aParams = array($sName, $iParentID);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		if (count($res) != 0) {

		    // mysql is case-insensitive - check using php
		    foreach ($res as $folder){
    		    $name = isset($folder['name']) ? $folder['name'] : '';
    		    if($name == $sName){
    		        return true;
    		    }
		    }
			return false;
		}
		return false;
	}

	/**
	* Checks if a given folder already exists using the folder name
	*
	* @param $iFolderID			Primary key of folder
	*
	* @return	true if the folder exists, false otherwise and set $_SESSION["errorMessage"]
	*/
	function folderExistsID($iFolderID) {
		$oFolder = Folder::get($iFolderID);
		if (PEAR::isError($oFolder)) {
		    return false; // no such folder, or bad ID
		} else {
		    return true;
		}
	}

	/**
	* Get the folder name using the primary key
	*
	* @param int primary key of folder to get name for
	*
	* @return String name on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderName($iFolderID) {
		$oFolder = Folder::get($iFolderID);
		if (PEAR::isError($oFolder)) {
		    return false; // return $oFolder;
		} else {
		    return $oFolder->getName();
		}
	}

    function getByParentIDAndLookupID($iParentID, $iLookupID) {
        return KTEntityUtil::getByDict('Folder', array(
            'parent_id' => $iParentID,
            'permission_lookup_id' => $iLookupID,
        ), array('multi' => true));
    }

	function getByParentId($iParentID) {
	    return KTEntityUtil::getByDict('Folder', array(
            'parent_id' => $iParentID,
        ), array('multi' => true));
	}

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('Folder', $aOptions);
    }

    function clearAllCaches() {
    	$GLOBALS["_OBJECTCACHE"]['Folder'] = array();
        return KTEntityUtil::clearAllCaches('Folder');
    }

}

?>

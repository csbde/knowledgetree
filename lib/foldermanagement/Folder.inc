<?php
/**
 * $Id$
 *
 * Represents as folder as the per the folders table in the database.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.foldermanagement 
 */
require_once(KT_LIB_DIR . "/foldermanagement/PhysicalFolderManagement.inc");
require_once(KT_LIB_DIR . "/lib/documentmanagement/Document.inc");

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
	/** primary key of unit to which this folder belongs */
	var $iUnitID;
	/** public status of folder */
	var $bIsPublic;
	/** comma deliminated string of parent ids */
	var $sParentFolderIDs;
	/** forward slash deliminated path from file system root */
	var $sFullPath;
	/** whether to inherit parent permissions or not */
	var $bInheritParentPermission;
    /** which folder I get permissions from (eases search lookups) */
    var $iPermissionFolderID;
	
	/**
	* Folder class constructor
	*
	* @param $sNewName				Folder name
	* @param $sNewDescription		Folder description
	* @param $iNewParentID			Primary key of parent folder
	* @param $iNewCreatorID			Primary key of user who created folder
	* @param $iNewDocumentTypeID	Primary key of document type folder will hold
	* @param $iNewUnitID			Primary key of unit to which folder belongs
	* @param $bNewIsPublic			Folder public status (is the folder public or not?)
	*/
	function Folder($sNewName, $sNewDescription, $iNewParentID, $iNewCreatorID, $iNewUnitID, $bNewIsPublic = false) {
		//id of -1 means that the object has not yet been stored in the database
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sDescription = $sNewDescription;
		$this->iParentID = $iNewParentID;
		$this->iCreatorID = $iNewCreatorID;		
		$this->iUnitID = $iNewUnitID;
		$this->bIsPublic = $bNewIsPublic;
		$this->bInheritParentPermissions = true;
	}
	
    /**
	* Get the primary key of the current folder object
	*
	* @return integer primary key of folder
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/** 
	* Get the folder name 
	*
	* @return String folder name
	*/
	function getName() {
		return $this->sName;
	}
	
	/** 
	* Set the folder name 
	*
	* @param $sNewValue		New folder name
	*/
	function setName($sNewValue) {
		$this->sName = $sNewValue;
	}
	
	/**
	* Get the folder description
	*
	* @return String folder description
	*/
	function getDescription() {
		return $this->sDescription;
	}
	
	/**
	* Set the folder description
	*
	* @param $sNewValue		New folder description
	*
	*/
	function setDescription($sNewValue) {
		$this->sDescription = $sNewValue;
	}
	
	/**
	* Get the parent folder primary key
	*
	* @return integer parent folder primary key
	*
	*/
	function getParentID() {
		return $this->iParentID;
	}
	
	/**
	* Set the parent folder primary key
	*
	* @param $iNewValue		New parent folder primary key
	*
	*/
	function setParentID($iNewValue) {
		$this->iParentID = $iNewValue;
	}
	
	/**
	* Get the creator user id
	*
	* @return integer creator user id
	*
	*/
	function getCreatorID() {
		return $this->iCreatorID;
	}
	
	/**
	* Set the creator user id
	*
	* @param $iNewValue	New creator user id
	*
	*/
	function setCreatorID($iNewValue) {
		$this->iCreatorID = $iNewValue;
	}
		
	/**
	* Get the unit primary key
	*
	* @return integer primary key of unit to which the folder belongs
	*
	*/
	function getUnitID() {
		return $this->iUnitID;
	}
	
	/**
	* Set the unit prinary key 
	*
	* @param $iNewValue		New primary key of unit to which folder belongs
	*
	*/
	function setUnitID($iNewValue) {
		$this->iUnitID = $iNewValue;
	}
		
	/**
	* Get the folder public status
	*
	* @return boolean true if folder is public, false if folder is not
	*
	*/
	function getIsPublic() {
		return $this->bIsPublic;
	}
	
	/**
	* Set the folder public status
	*
	* @param $bNewValue 	New folder public status
	*
	*/
	function setIsPublic($bNewValue) {
		$this->bIsPublic = $bNewValue;
	}
	
	function getFullPath() {
		return $this->sFullPath;
	}
	
	function getParentFolderIDs() {
		return $this->sParentFolderIDs;
	}
	
	function getInheritParentPermission() {
		return $this->bInheritParentPermissions;
	}
	
	function setInheritParentPermission($bNewValue) {
		$this->bInheritParentPermissions = $bNewValue;
	}

	/**
	* Get the permission folder id
	*
	* @return integer permission folder id
	*
	*/
	function getPermissionFolderID() {
		return $this->iPermissionFolderID;
	}
	
	/**
	 * Recursive function to generate a comma delimited string containing
	 * the parent folder ids
	 * 
	 * @return String	comma delimited string containing the parent folder ids
	 */
	function generateParentFolderIDS($iFolderID) {
		global $default;
		//if the folder is not the root folder
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query(array("SELECT parent_id FROM $default->folders_table WHERE ID = ?", $iFolderID));/*ok*/
			$sql->next_record();
			return $this->generateParentFolderIDS($sql->f("parent_id")) . ",$iFolderID";
		}
		return;
		
	}
	
	/**
	 * Returns a comma delimited string containing the parent folder ids, strips leading /
	 * 
	 * @return String	comma delimited string containing the parent folder ids
	 */	
	function generateFolderIDs($iFolderID) {
		$sFolderIDs = $this->generateParentFolderIDS($iFolderID);
		return substr($sFolderIDs, 1, strlen($sFolderIDs));
	}
	
	/**
	 * Recursively generates forward slash deliminated string giving full path of document
	 * from file system root url
	 */
	function generateFullFolderPath($iFolderID) {
		global $default;
		//if the folder is not the root folder 
		if ($iFolderID != 0) {
			$sql = $default->db;
			$sql->query(array("SELECT name, parent_id FROM $default->folders_table WHERE ID = ?", $iFolderID));/*ok*/
			$sql->next_record();
			return $this->generateFullFolderPath($sql->f("parent_id")) . "/" . $sql->f("name");
		}
		return;
	}
	
	/**
	 * Returns a forward slash deliminated string giving full path of document, strips leading /
	 */	
	function generateFolderPath($iFolderID) {
		global $default;
		$sPath = $this->generateFullFolderPath($iFolderID);
		$sPath = substr($sPath, 1, strlen($sPath));
		$sPath = addslashes($sPath);
		return $sPath;			
	}

    function _fieldValues () {
        $this->sFullPath = $this->generateFolderPath($this->iParentID);
        $this->sParentFolderIDs = $this->generateFolderIDs($this->iParentID);
        return array(
            'name' => $this->sName,
            'description' => $this->sDescription,
            'parent_id' => $this->iParentID,
            'creator_id' => $this->iCreatorID,
            'unit_id' => $this->iUnitID,
            'is_public' => KTUtil::anyToBool($this->bIsPublic),
            'full_path' => $this->sFullPath,
            'parent_folder_ids' => $this->sParentFolderIDs,
            'inherit_parent_folder_permission' => $this->bInheritParentPermissions,
            'permission_folder_id' => $this->iPermissionFolderID,
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
	function updateChildPaths($iID) {
		global $default;
		//get the direct children
        $sql = $default->db;
		$sql->query(array("SELECT id from $default->folders_table WHERE parent_id = ?", $iID));/*ok*/
		while ($sql->next_record()) {
			//force an update, this will cause this child's children to be updated			
			$oFolder = Folder::get($sql->f("id"));			
			$oFolder->update(true);		
						
		}
		return;
	}

    /**
     * When a folder's path changes, we must update the paths in the
     * documents in that folder.  Sub-folders are handled elsewhere in
     * update().
     */

    function updateDocumentPaths($iID) {
        $aDocuments = Document::getList(array('folder_id = ?', $iID));
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
	* Get a folder's sub-folders
	* 
	* @param int	primary key of folder to get children for
	* 
	* @return Array array of child ids
	*/
	function getChildren($iFolderID, & $aChildren) {		
		global $default;
		$sql = $default->db;
		$sql->query(array("SELECT id from $default->folders_table WHERE parent_id = ?", $iFolderID));/*ok*/
		while ($sql->next_record()) {
			$aChildren[count($aChildren)] = $sql->f("id");
			Folder::getChildren($sql->f("id"), $aChildren);
		}		
		return $aChildren;
	}
	
    /**
     * Returns the documents in this folder
     */
    function getDocumentIDs($iFolderID) {
		global $default;
		$sql = $default->db;
		$sql->query(array("SELECT id from $default->documents_table WHERE folder_id = ?", $iFolderID));/*ok*/
		while ($sql->next_record()) {
			$sChildString .= $sql->f("id") . ",";
		}
		return substr($sChildString,0, strlen($sChildString)-1);
    }
	
	/**
	* Static function.  
	* Given a folder primary key will create
	* a folder object and populate it with the corresponding
	* database values
	*
	* @param $iFolderID		Primary key of folder to get
	*
	* @return Folder folder object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
	*/
	function get($iFolderID) {		
		global $default,  $lang_err_object_not_exist;
		$sql = $default->db;		
		$sql->query(array("SELECT * FROM " . $default->folders_table . " WHERE id = ?", $iFolderID));/*ok*/
		if ($sql->next_record()) {
			$oFolder = & new Folder($sql->f("name"), $sql->f("description"), $sql->f("parent_id"), $sql->f("creator_id"), $sql->f("unit_id"), $sql->f("is_public"));
            $oFolder->iId = $iFolderID;
			$oFolder->sFullPath = $sql->f("full_path");
			$oFolder->sParentFolderIDs = $sql->f("parent_folder_ids");
			$oFolder->iPermissionFolderID = $sql->f("permission_folder_id");
			return $oFolder;
		} 
 		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderID . " table = folders";
		return false;
	}

	/**
	 * Checks if a folder with the same name and parent exists in the database already
	 */
	function exists() {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folders_table WHERE name = ? AND parent_id = ?";/*ok*/
        $aParams = array($this->sName, $this->iParentID);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
    * Static function
    * Get a list of Documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Documents objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(Folder::_table(), 'Folder', $sWhereClause);
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
		$sPath = $default->documentRoot . "/" . $oFolder->sFullPath . "/" . $oFolder->getName() . "/";
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
			if (strlen($oFolder->sFullPath) > 0) {
				if (strlen($oFolder->sFullPath) > 1) {				
					$aPathArray = explode("/",$oFolder->sFullPath);				
				} else {
					$aPathArray = array($oFolder->sFullPath);
				}
				$aPathArray[count($aPathArray)] = $oFolder->getName();
			} else {
				$aPathArray = array($oFolder->getName());
			}
		}
		return $aPathArray;
    }
	
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
		if (strlen($oFolder->sParentFolderIDs) > 0) {
            if ($oFolder->iParentID == 0) {
                $aPathArray = array();
            } else if (strlen($oFolder->sParentFolderIDs) > 1) {				
				$aPathArray = explode(",",$oFolder->sParentFolderIDs);				
			} else {
				$aPathArray = array($oFolder->sParentFolderIDs);
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
        if (count($aPathNamesArray) > 0) {
        	return implode(" > ", $aPathNamesArray);
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
			global $default;		
			$sql = $default->db;
			$sql->query(array("SELECT parent_id FROM " . $default->folders_table . " WHERE id = ?", $iFolderID));/*ok*/
			$sql->next_record();		
			return $sql->f("parent_id");
		}
		return 0;
	}
	
	/**
	* Static function
	* Gets the default document type for a folder (where default is simply the first one)
	*
	* @param $iFolderID
	*
	* @return integer document type primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getDefaultFolderDocumentType($iFolderID) {
		global $default, $lang_err_database;
		if (Folder::folderExistsID($iFolderID)) {
			$sql = $default->db;
			$sql->query(array("SELECT document_type_id FROM " . $default->folder_doctypes_table . " WHERE folder_id = ?", $iFolderID));/*ok*/
			if ($sql->next_record()) {
				return $sql->f("document_type_id");
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			
		}
		//error message set by Folder::folderExists
		return false;
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
		global $default, $lang_err_folder_exist;
		$sql = $default->db;
        $sQuery = "SELECT * FROM " . $default->folders_table . " WHERE name = ? AND parent_id = ?";/*ok*/
        $aParams = array($sName, $iParentID);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] = $lang_err_folder_exist . $sName . " parent_id " . $iParentID;
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
		global $default, $lang_err_folder_exist;
		$sql = $default->db;
		$sql->query(array("SELECT * FROM " . $default->folders_table . " WHERE id = ?", $iFolderID));/*ok*/
		if ($sql->next_record()) {
			return true;
		}
		$_SESSION["errorMessage"] =$lang_err_folder_exist . "id " . $iFolderID;
		return false;
	}
	
	/**
	* Get the folder name using the primary key
	*
	* @param int primary key of folder to get name for
	*
	* @return String name on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderName($iFolderID) {
		global $default, $lang_err_database;
		$sql = $default->db;
		$sql->query(array("SELECT name FROM " . $default->folders_table . " WHERE id = ?", $iFolderID));/*ok*/
		if ($sql->next_record()) {
			return $sql->f("name");
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	* Get the folder id using the folder name
	*
	* @param string the name of the folder to get the ID for
	*
	* @return int name on success, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getFolderID($sFolderName) {
		global $default, $lang_err_database;
		$sql = $default->db;
		$sql->query(array("SELECT id FROM " . $default->folders_table . " WHERE name = ?", $sFolderName));/*ok*/
		if ($sql->next_record()) {
			return $sql->f("id");
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}    
	
	/**
	* Checks if a document type is already
	* linked to a folder
	* 
	*/
	function folderIsLinkedToDocType($iFolderID, $iDocTypeID) {
		global $default;
		$sql = $default->db;
        $sQuery = "SELECT id FROM $default->folder_doctypes_table WHERE document_type_id = ? and folder_id = ?";/*ok*/
        $aParams = array($iDocTypeID, $iFolderID);
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
	
	
	/**
	* Checks if a folder is a unit's root folder.
	* A folder is assumed to be a unit's root folder if it has the
	* same name as a unit and has an appropriate description
	* 
	*/
	function folderIsUnitRootFolder($iFolderID) {
		global $default;
		$sQuery = "SELECT F.id FROM $default->folders_table AS F " .
				  "INNER JOIN $default->folders_table AS P ON F.parent_id=P.id " .
		          "INNER JOIN $default->units_table AS U ON F.unit_id=U.id " .
		          "WHERE F.id = ? " .
		          " AND P.unit_id<>F.unit_id ";	
        $aParams = array($iFolderID);
		$sql = $default->db;
		$sql->query(array($sQuery, $aParams));
		if ($sql->next_record()) {
			return true;
		}
		return false;
	}
    
    /**
     * Returns whether this folder has any documents currently in collaboration
     */
    function hasDocumentInCollaboration($iFolderID) {
        global $default;
        $sDocumentIDs = Folder::getDocumentIDs($iFolderID);
        if (strlen($sDocumentIDs) > 0) {
            $aDocumentIDs = split(',', $sDocumentIDs);
            $sQms = DBUtil::paramArray($aDocumentIDs);

            $sQuery = "SELECT * FROM folders_users_roles_link WHERE document_id in ($sQms) AND (active = ?)";/*ok*/
            $aParams = $aDocumentIDs;
            $aParams[] = true;
            $sql = $default->db;
            $sql->query(array($sQuery, $aParams));
            if ($sql->next_record()) {
                return true;
            }
            return false;
        } else {
            // no documents
            return false;
        }
    }

    function calculatePermissionFolder() {
        global $default;
        $oInheritedFolder = $this;
        while ($bFoundPermissions !== true) {
            /*ok*/$aCheckQuery = array('SELECT id FROM groups_folders_link WHERE folder_id = ? LIMIT 1', $oInheritedFolder->getID());
            if (count(DBUtil::getResultArrayKey($aCheckQuery, 'id')) == 0) {
                $default->log->debug('No direct permissions on folder ' . $oInheritedFolder->getID());
                $bInherited = true;
                $oInheritedFolder =& Folder::get($oInheritedFolder->getParentID());
                if ($oInheritedFolder === false) {
                    break;
                }
                // if our parent knows the permission folder, use that.
                if ($oInheritedFolder->getPermissionFolderID()) {
                    $this->iPermissionFolderID = $oInheritedFolder->getPermissionFolderID();
                }
                $default->log->debug('... trying parent: ' . $oInheritedFolder->getID());
            } else {
                $default->log->debug('Found direct permissions on folder ' . $oInheritedFolder->getID());
                $this->iPermissionFolderID = $oInheritedFolder->getID();
                return;
            }
        }

        $default->log->error('No permissions whatsoever for folder ' . $this->getID());
        // 0, which can never exist, for non-existent.  null for not set yet (database upgrade).
        $this->iPermissionFolderID = 0;
    }
    
    // {{{ copyPermissionsFromFolder()
    function copyPermissionsFromFolder ($oFolder) {
        $sQuery = DBUtil::compactQuery("
        SELECT
            GFL.group_id AS group_id,
            GFL.can_read AS can_read,
            GFL.can_write AS can_write
        FROM
            $default->groups_folders_table AS GFL
        WHERE GFL.folder_id = ?");
        $aParams = array($oFolder->getID());
        $aPermissions = DBUtil::getResultArray(array($sQuery, $aParams));

        if (PEAR::isError($aPermissions)) {
            return $aPermissions;
        }

        foreach ($aPermissions as $aRow) {
            $aRow['folder_id'] = $this->getID();
            $res = DBUtil::autoInsert($default->groups_folders_table, $aRow);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        $this->updatePermissions();
    }
    // }}}

    // {{{ create()
    function create () {
        $this->calculatePermissionFolder();
        return parent::create();
    }
    // }}}

    // {{{ updatePermissions()
    function updatePermissions () {
        // Update PermissionFolderIDs on folder and all folders in this
        // branch.
        $this->updatePermissionFolders();

        // Now, we need to redo the permissions for all documents that
        // share are in this branch of the tree that are in folders that
        // share our PermissionFolderID.

        // First, we update the documents in this folder
        $sQuery = "SELECT D.id AS id FROM documents AS D WHERE D.folder_id = ?";
        $aParams = array($this->getID());
        $aDocumentIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
        // OPT: We could just do the work for the first, and copy it to
        // the others...
        foreach ($aDocumentIDs as $iDocumentID) {
            Permission::updateSearchPermissionsForDocument($iDocumentID);
        }
        
        // Then, we update the documents in our subfolders
        $sFolderIDs = $this->generateFolderIDs($this->getID());
        $sFolderIDs .= '%';
        $sQuery = "SELECT D.id AS id FROM documents AS D INNER JOIN folders AS F on D.folder_id = F.id WHERE F.parent_folder_ids LIKE ?";
        $aParams = array($sFolderIDs);
        $aDocumentIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        // OPT: We could just do the work for the first, and copy it to
        // the others...
        foreach ($aDocumentIDs as $iDocumentID) {
            Permission::updateSearchPermissionsForDocument($iDocumentID);
        }
    }
    // }}}

    // {{{ updatePermissionFolders()
	/**
     * Update the PermissionFolderID on this folder and all folders on
     * this branch.
	 *
	 */
    function updatePermissionFolders() {
        $iOldPermissionFolderID = $this->iPermissionFolderID;
        $this->calculatePermissionFolder();
        $iNewPermissionFolderID = $this->iPermissionFolderID;

        // If our permission folder hasn't changed, our children's won't
        // have.
        if ($iOldPermissionFolderID === $iNewPermissionFolderID) {
            return;
        }

        // XXX: Non-explicit update... Have to update, or the children
        // wouldn't have access to it for the shortcut...
        $this->update();
        $aChildren = Folder::getList(array('parent_id = ?', $this->getID()));
        foreach ($aChildren as $oChild) {
            $oChild->updatePermissionFolders();
        }
    }
    // }}}

    function addPermission ($oGroup, $bCanRead, $bCanWrite) {
        $oGroupFolderLink = & new GroupFolderLink($this->getID(), $oGroup->getID(), $bCanRead, $bCanWrite);

        if ($oGroupFolderLink->exists()) {
            return new PEAR_Error(_("A folder access entry for the selected folder and group already exists."));
        }

        if (!$oGroupFolderLink->create()) {
            //otherwise display an error message
            return new PEAR_Error(_("A folder access entry for the selected folder and group already exists."));
        }

        $this->updatePermissions();
        return true;
    }

}

?>

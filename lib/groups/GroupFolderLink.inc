<?php
/**
 * $Id$
 *
 * Represents the group_folders_link table in the db used to represent
 * folder access
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.foldermanagement
 */
class GroupFolderLink {
	
	/** primary key of current object */
	var $iId;
	/** primary key of folder */
	var $iFolderID;
	/** primary key of group from which user can be assigned */
	var $iGroupID;
	/** read access */
	var $bCanRead;
	/** write access */
	var $bCanWrite;
	
	function GroupFolderLink($iNewFolderID, $iNewGroupID, $iNewCanRead, $iNewCanWrite) {
		//object not created in database yet
		$this->iId = -1;
		$this->iFolderID = $iNewFolderID;
		$this->iGroupID = $iNewGroupID;
		$this->bCanRead = $iNewCanRead;
		$this->bCanWrite = $iNewCanWrite;
	}
	
	function getID() {
		return $this->iId;
	}
	
	function getFolderID() {
		return $this->iFolderID;
	}
	
	function setFolderID($iNewValue) {
		$this->iFolderID = $iNewValue;
	}
	
	function getGroupID() {
		return $this->iGroupID;
	}
	
	function setGroupID($iNewValue) {
		$this->iGroupID = $iNewValue;
	}
	
	function getCanRead() {
		return $this->bCanRead;
	}
	
	function setCanRead($bNewValue) {
		$this->bCanRead = $bNewValue;
	}
	
	function getCanWrite() {
		return $this->bCanWrite;
	}
	
	function setCanWrite($iNewValue) {
		$this->bCanWrite = $iNewValue;
	}
	
	/**
	* Create the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_database, $lang_err_object_exists;
		//if the object hasn't been created
		if ($this->iId < 0) {
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_groups_folders_table . " (folder_id, group_id, can_read, can_write) VALUES ($this->iFolderID, $this->iGroupID, " . ($this->bCanRead ? 1 : 0) . ", " . ($this->bCanWrite ? 1 : 0) . ")");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_groups_folders_table";
		return false;
	}
	
	/**
	* Update the values in the database table with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this->iId > 0) {
			$sql = $default->db;
			$result = $sql->query("UPDATE $default->owl_groups_folders_table SET group_id = $this->iGroupID, folder_id = $this->iFolderID, can_read = " . ($this->bCanRead ? 1 : 0) .", can_write = " . ($this->bCanWrite ? 1 : 0) . " WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM $default->owl_groups_folders_table WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
    /**
     * Whether the folder permission exists in the database
     */     
    function exists() {
        global $default;
        $sql = $default->db;
        if ($sql->query("SELECT count(*) AS count FROM $default->owl_groups_folders_table WHERE folder_id = $this->iFolderID AND group_id = $this->iGroupID")) {
            $sql->next_record();
            return ($sql->f("count") > 0) ? true : false;
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
    }
    
	/**
	* Static function.
	* Given a primary key it will create a FolderAccess object and populate it with the 
	* corresponding database values
	*
	* @return FolderAccess populated FolderAccess object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iGroupFolderID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_groups_folders_table WHERE id = $iGroupFolderID");
		if ($result) {
			if ($sql->next_record()) {
				$oGroupFolderLink = & new GroupFolderLink($sql->f("folder_id"), $sql->f("group_id"), $sql->f("can_read"), $sql->f("can_write"));
				$oGroupFolderLink->iId = $iGroupFolderID;
				return $oGroupFolderLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderAccessID . " table = $default->owl_groups_folders_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

   /**
	* Static function
	* Get a list of folder collaborations
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of FolderCollaboration objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$oGroupFolderLinkArray = array();
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_groups_folders_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			while ($sql->next_record()) {
				$oGroupFolderLinkArray[] = & GroupFolderLink::get($sql->f("id"));
			}
			return $oGroupFolderLinkArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}
?>

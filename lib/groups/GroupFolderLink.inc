<?php
/**
 * $Id$
 *
 * Represents the group_folders_link table in the db used to represent
 * folder access.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.groups 
 */ 
class GroupFolderLink extends KTEntity {
	
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

    function _fieldValues () {
        return array(
            'folder_id' => $this->iFolderID,
            'group_id' => $this->iGroupID,
            'can_read' => KTUtil::anyToBool($this->bCanRead),
            'can_write' => KTUtil::anyToBool($this->bCanWrite),
        );
    }

    function _table () {
        global $default;
        return $default->groups_folders_table;
    }
	
    /**
     * Whether the folder permission exists in the database
     */     
    function exists() {
        global $default;
        $sql = $default->db;
        if ($sql->query("SELECT count(*) AS count FROM $default->groups_folders_table WHERE folder_id = $this->iFolderID AND group_id = $this->iGroupID")) {
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
		$result = $sql->query("SELECT * FROM $default->groups_folders_table WHERE id = $iGroupFolderID");
		if ($result) {
			if ($sql->next_record()) {
				$oGroupFolderLink = & new GroupFolderLink($sql->f("folder_id"), $sql->f("group_id"), $sql->f("can_read"), $sql->f("can_write"));
				$oGroupFolderLink->iId = $iGroupFolderID;
				return $oGroupFolderLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderAccessID . " table = $default->groups_folders_table";
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
		$result = $sql->query("SELECT * FROM " . $default->groups_folders_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
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

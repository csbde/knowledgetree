<?php
/**
 * $Id$
 *
 * Represents a role as per the roles database table.
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
 * @package lib.roles
 */
class Role extends KTEntity {
	
	/** role object primary key */
	var $iId;
	/** role name */
	var $sName;
	/** is an active user*/
	var $bActive;
	/** role has document read permission */
	var $bCanRead;
	/** role has document write */
	var $bCanWrite;
	
	
	
	/**
	* Default constructor
	*
	* @param 	String		Role name
	* @param 	boolean		Role has document read permission
	* @param 	boolean		Role has document write permission
	*
	*/
	function Role($sNewName, $bNewCanRead, $bNewCanWrite) {
		//object not yet created in database
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->bCanRead = $bNewCanRead;
		$this->bCanWrite = $bNewCanWrite;
	}
	
	function getName() {
		return $this->sName;
	}
	
	function getID() {
		return $this->iId;
	}
	
	function getActive() {
		return $this->bActive;
	}
	
	function setActive($bNewValue) {
		$this->bActive = $bNewValue;
	}
	
	function getReadable() {
		return $this->bCanRead;
	}
	
	function setReadable($bNewValue) {
		$this->bCanRead = $bNewValue;
	}
	
	function getWriteable() {
		return $this->bCanWrite;
	}
	
	function setWriteable($bNewValue) {
		$this->bCanWrite = $bNewValue;
	}
	
	function setName($bNewValue) {
		$this->sName = $bNewValue;
	}

    function _fieldValues () {
        return array(
            'name' => $this->sName,
            'active' => $this->bActive,
            'can_read' => $this->bCanRead,
            'can_write' => $this->bCanWrite,
        );
    }

    function _table () {
        global $default;
        return $default->roles_table;
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
			//check to see if role is linked to a folder
			$sql = $default->db;
			$query = "SELECT role_id FROM ". $default->groups_folders_approval_table ." WHERE role_id = '" . $this->iId . "'";
            $sql->query($query);
            $rows = $sql->num_rows($sql);
        
             if ($rows > 0){
                 $_SESSION["errorMessage"] = "Role::The Role " . $this->sName . " is Assigned to a folder!";
                 return false;
             }
        }
        return parent::delete();
	}
	
	/**
	* Static function.
	* Given a roles primary key it will create a 
	* Roles object and populate it with the 
	* corresponding database values
	*
	* @return WebDocument populated WebDocument object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iRoleID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->roles_table WHERE id = $iRoleID");
		if ($result) {
			if ($sql->next_record()) {
				$oRole = & new Role($sql->f("name"), $sql->f("can_read"), $sql->f("can_write"));
				$oRole->iId = $iRoleID;
				$oRole->bActive = $sql->f("active");
				return $oRole;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iRoleID . " table = $default->roles_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list of web documents
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of WebDocument objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aRoleArray;
		settype($aRoleArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->roles_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oRole = & Role::get($sql->f("id"));
				$aRoleArray[$iCount] = $oRole;
				$iCount++;
			}
			return $aRoleArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
}



/**
* Static function
*
* Creates a roles object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & roleCreateFromArray($aParameters) {
	$oRole = & new Role($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oRole;
}

?>

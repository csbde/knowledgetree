<?php
/**
 * $Id$
 *
 * Represents a unit as per the database table units.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.unitmanagement
 */
class Unit extends KTEntity {

    /** object's primary key */
    var $iId;
    /** unit's name */
    var $sName;


    function Unit($sNewName) {
        //object has not been created in database yet
        $this->iId = -1;
        $this->sName = $sNewName;
    }

    /**
    * Get the object's primary key
    *
    * @return int object's primary key
    *
    */
    function getID() {
        return $this->iId;
    }

    /**
    * Get the unit's name
    *
    * @return String unit's name
    *
    */
    function getName() {
        return $this->sName;
    }

    /**
    * Set the unit's name
    *
    * @param  String  Unit's name
    *
    */
    function setName($sNewValue) {
        $this->sName = $sNewValue;
    }

    function _fieldValues () {
        return array(
            'name' => $this->sName,
        );
    }

    function _table () {
        global $default;
        return $default->units_table;
    }

    /**
    * Create the current object in the database
    *
    * @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
    *
    */
    function create() {
        $res = parent::create();
        if ($res === false) {
            return $res;
        }

        // create a new unit root folder
        // FIXME: lookup the organisation for this unit and use the appropriate folder id for the org root folder
        $oFolder = new Folder($this->sName, $this->sName . " Unit Root Folder", 1, $_SESSION["userID"], $this->iId);
        if (!$oFolder->exists()) {
            if ($oFolder->create()) {
                if (PhysicalFolderManagement::createFolder(Folder::getFolderPath($oFolder->getID()))) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        // a unit root folder already exists in the database
        // update the description
        $aFolders = Folder::getList(array("name = ? AND parent_id = 1", $this->sName));/*ok*/
        $oFolder = $aFolders[0];
        $oFolder->setDescription($this->sName . " Unit Root Folder");
        $oFolder->setUnitID($this->iId);
        $oFolder->update();
        return true;
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
        if ($this->iId <= 0) {
            $_SESSION["errorMessage"] = "Can't update an object not in the database.";
            return false;
        }

        $sOldName = addslashes(lookupField($default->units_table, "name", "id", $this->iId));
        $res = parent::update();
        if ($res === false) {
            return $res;
        }
        $iFolderID = Folder::getFolderID($sOldName);

        if($iFolderID) {
            // lookup the folder with the old unit name
            $oFolder = Folder::get($iFolderID);
            // folder name has changed, update the full_path
            $sOldPath = $default->documentRoot . "/" . $oFolder->getFullPath() . "/" . $oFolder->getName();
            // update the folder name
            $sNewPath = $default->documentRoot . "/" . $oFolder->getFullPath() . "/" . $this->sName;	                
            $oFolder->setName(addslashes($this->sName));
            $oFolder->setDescription(addslashes($this->sName . " Unit Root Folder"));

            if ($oFolder->update(true)) {
                if (!PhysicalFolderManagement::renameFolder($sOldPath, $sNewPath)) {
                    //reverse the database changes if the physical rename failed
                    $oFolder->setName($sOldName);
                    $oFolder->update(true);
                    // TODO: revert unit name change also
                    return false;
                }
            }
        }
        return true;
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
			if ($sql->query("DELETE FROM $default->units_table WHERE id = $this->iId")) {
			    return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
        }
        $_SESSION["errorMessage"] = $lang_err_object_key;
        return false;
    }

    /**
    * Static function.
    * Given a web_documents primary key it will create a 
    * Unit object and populate it with the 
    * corresponding database values
    *
    * @return Unit populated Unit object on successful query, false otherwise and set $_SESSION["errorMessage"]
    */
    function & get($iUnitID) {
        global $default;
        $sql = $default->db;
        $result = $sql->query(array("SELECT * FROM $default->units_table WHERE id = ?", $iUnitID));/*ok*/
        if ($result) {
            if ($sql->next_record()) {
                $oUnit = & new Unit($sql->f("name"));
                $oUnit->iId = $iUnitID;
                return $oUnit;
            }
            $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iUnitID . " table = $default->units_table";
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }

	/**
	 * Returns true if the unit has groups linked to it
	 */
	function hasGroups() {
		global $default;
		// check to see if group is linked to a unit
		$sql = $default->db;
		$query = array("SELECT unit_id FROM ". $default->groups_units_table ." WHERE unit_id = ?", $this->iId);/*ok*/
		$sql->query($query);
		if ($sql->num_rows($sql) > 0) {
			return true;
		} else {
			return false;
		}
	}
		
    /**
     * static function
     *
     * gets the id of a unit using their name
     *
     * @param   String  The unit name
     */
    function getUnitID($name) {
        global $default;
        $id = lookupID($default->units_table, "name",  $name);
        $this->iId= $id;
    }

    /**
     * static function
     *
     * gets the name of a unit using their id
     *
     * @param   String The name
     *   
     */
    function getUnitName($id) {
        global $default;
        $name = lookupField("$default->units_table", "name", "id", $id );
        $this->sName= $name;
    }

    /**
    * Static function
    * Get a list of web documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Unit objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(Unit::_table(), 'Unit', $sWhereClause);
    }

}
/**
* Static function
*
* Creates a unit object from an array
*
* @param  Array  Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & unitCreateFromArray($aParameters) {
    $oUnit = & new Unit($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oUnit;
}
?>

<?php
/**
 * $Id$
 *
 * Represents a organisation as per the database table organisations_lookup.
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
 * @package lib.orgmanagement
 */
class Organisation extends KTEntity {
    /** object's primary key */
    var $iId;
    /** org's name */
    var $sName;
    function Organisation($sNewName) {
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
    * Get the org's name
    *
    * @return String org's name
    *
    */
    function getName() {
        return $this->sName;
    }
    /**
    * Set the orgs name
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
        return $default->organisations_table;
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
            //check to see if group is linked to a unit
            $sql = $default->db;
            $query = "SELECT organisation_id FROM ". $default->units_organisations_table ." WHERE organisation_id = '" . $this->iId . "'";
            $sql->query($query);
            $rows = $sql->num_rows($sql);
            if ($rows > 0) {
                // duplicate link exists
                $_SESSION["errorMessage"] = "Group::The Group " . $this->sName . " exits!";
                return false;
            }
        }
        return parent::delete();
    }
    /**
    * Static function.
    * Given a web_documents primary key it will create a
    * Unit object and populate it with the
    * corresponding database values
    *
    * @return Unit populated Unit object on successful query, false otherwise and set $_SESSION["errorMessage"]
    */
    function & get($iOrgID) {
        global $default;
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->organisations_table WHERE id = $iOrgID");
        if ($result) {
            if ($sql->next_record()) {
                $oOrg = & new Organisation($sql->f("name"));
                $oOrg->iId = $iOrgID;
                return $oOrg;
            }
            $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iOrgID . " table = $default->organisations_table";
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
    /*
    * static function
    *
    * gets the id of a org using their name
    *
    * @param   String
    *   The username for which we want its ID
    *
    */
    function getOrgID($name) {
        global $default;
        $id = lookupID($default->organisations_table, "name", $name);
        $this->iId = $id;
    }
    /**
    * Static function
    * Get a list of web documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of org objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        global $default, $lang_err_database;
        $aOrgArray;
        settype($aUnitArray, "array");
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->organisations_table . (isset($sWhereClause) ? " " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oOrg = & Organisation::get($sql->f("id"));
                $aOrgArray[$iCount] = $oOrg;
                $iCount++;
            }
            return $aOrgArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
}
/**
* Static function
*
* Creates a Org object from an array
*
* @param  Array  Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & organisationCreateFromArray($aParameters) {
    $oOrg = & new Organisation($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oOrg;
}
?>

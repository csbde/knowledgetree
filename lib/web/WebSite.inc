<?php
/**
 * $Id$
 *
 * Represents a web site as per the web_sites database table.
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
 * @package lib.web
 * @todo - add regex parsing to ensure URL is valid
 */
class WebSite {

    /** primary key of web site */
    var $iId;
    /** web site name */
    var $sWebSiteName;
    /** web site url */
    var $sWebSiteURL;
    /** primary key of user responsible for web site */
    var $iWebMasterID;

    /**
    * Default constructor
    *
    * @param  String  Name of web site
    * @param  String  Web site URL
    * @param  int   Primary key of user who is the web site master
    *
    */
    function WebSite($sNewWebSiteName, $sNewWebSiteURL, $iNewWebMasterID) {
        //object not created in database yet
        $this->iId = -1;
        $this->sWebSiteName = $sNewWebSiteName;
        $this->sWebSiteURL = $sNewWebSiteURL;
        $this->iWebMasterID = $iNewWebMasterID;
    }

    /**
    * Get the primary key for this object
    *
    * @return int primary key for this object
    *
    */
    function getID() {
        return $this->iId;
    }

    /**
    * Get the web site name
    *
    * @return String web site name
    *
    */
    function getWebSiteName() {
        return $this->sWebSiteName;
    }

    /**
    * Set the web site name
    *
    * @param  String  Web site name
    *
    */
    function setWebSiteName($sNewValue) {
        $this->sWebSiteName = $sNewValue;
    }

    /**
    * Get the web site URL
    *
    * @return String web site URL
    *
    */
    function getWebSiteURL() {
        return $this->sWebSiteURL;
    }

    /**
    * Set the web site URL
    *
    * @param  String  Web site URL
    *
    */
    function setWebSiteURL($sNewValue) {
        $this->sWebSiteURL = $sNewValue;
    }

    /**
    * Get the primary key of the user that is the web master
    *
    * @return int primary key of user that is the web master
    *
    */
    function getWebMasterID() {
        return $this->iWebMasterID;
    }

    /**
    * Set the web master id
    *
    * @param  int  Primary key of user that is web master
    *
    */
    function setWebMasterID() {
        $this->iWebMasterID = $iNewValue;
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
            //check to see if name exsits
            $sql = $default->db;
            $query = "SELECT web_site_name FROM ". $default->web_sites_table ." WHERE web_site_name = '" . $this->sWebSiteName . "' and web_site_url = '" . $this->sWebSiteURL . "' and web_master_id = '" . $this->iWebMasterID . "'";
            $sql->query($query);
            $rows = $sql->num_rows($sql);
            if ($rows > 0) {
                // duplicate username
                $_SESSION["errorMessage"] = "Website::The Wesbite name " . $this->sWebSiteName . " is already in use!";
                return false;

            } else {

                $sql = $default->db;
                $result = $sql->query("INSERT INTO " . $default->web_sites_table . " (web_site_name, web_site_url, web_master_id) VALUES ('$this->sWebSiteName', '$this->sWebSiteURL', $this->iWebMasterID)");
                if ($result) {
                    $this->iId = $sql->insert_id();
                    return  true;
                }
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }
        }
        $_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->web_sites_table";
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
            $result = $sql->query("UPDATE " . $default->web_sites_table . " SET web_site_name = '$this->sWebSiteName', web_site_url = '$this->sWebSiteURL', web_master_id = '" . $this->iWebMasterID . "' WHERE id = $this->iId");
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
            $result = $sql->query("DELETE FROM $default->web_sites_table WHERE id = $this->iId");
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
    * Static function.
    * Given a web_sites primary key it will create a 
    * WebSite object and populate it with the 
    * corresponding database values
    *
    * @return WebSite populated WebSite object on successful query, false otherwise and set $_SESSION["errorMessage"]
    */
    function & get($iWebSiteID) {
        global $default;
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->web_sites_table WHERE id = $iWebSiteID");
        if ($result) {
            if ($sql->next_record()) {
                $oWebSite = & new WebSite($sql->f("web_site_name"), $sql->f("web_site_url"), $sql->f("web_master_id"));
                $oWebSite->iId = $iWebSiteID;
                return $oWebSite;
            }
            $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iWebSiteID . " table = $default->web_sites_table";
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }

    /**
    * Static function
    * Get a list of web sites
    *
    * @return Array array of WebSite objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList() {
        global $default, $lang_err_database;
        $aWebSiteArray;
        settype($aWebSiteArray, "array");
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->web_sites_table);
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oWebSite = & WebSite::get($sql->f("id"));
                $aWebSiteArray[$iCount] = $oWebSite;
                $iCount++;
            }
            return $aWebSiteArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
}

/**
* Static function
*
* Creates a Group object from an array
*
* @param  Array  Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & websiteCreateFromArray($aParameters) {
    $oWebsite = & new Website($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oWebsite;
}
?>

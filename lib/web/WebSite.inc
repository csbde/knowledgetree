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

class WebSite extends KTEntity {

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

    function _fieldValues () {
        return array(
            'web_site_name' => $this->sWebSiteName,
            'web_site_url' => $this->sWebSiteURL,
            'web_master_id' => $this->iWebMasterID,
        );
    }

    function _table () {
        global $default;
        return $default->web_sites_table;
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
        $result = $sql->query(array("SELECT * FROM $default->web_sites_table WHERE id = ?", $iWebSiteID));/*ok*/
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
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(WebSite::_table(), 'WebSite', $sWhereClause);
    }
    
    /**
     * Returns true if the website is currently in use- ie. a document is being published to it
     */
    function inUse() {
    	global $default;
    	$sql = $default->db;
    	/*ok*/ $sQuery = array("SELECT WS.id FROM $default->web_sites_table WS " . 
    			  "INNER JOIN $default->web_documents_table WD ON WS.id=WD.web_site_id " .
    			  "WHERE WS.id = ? " . 
    			  "AND WD.status_id=1", $this->iId);
    	$sql->query($sQuery);
		$rows = $sql->num_rows();
		if ($rows > 0){
	    	return true;
		} else {
			return false;
		}    		
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

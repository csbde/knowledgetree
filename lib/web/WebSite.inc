<?php

/**
*
* Class WebSite
* Represents a web site as per the web_sites database table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
*
* @todo - add regex parsing to ensure URL is valid
* @package lib.web 
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
	* @param 	String		Name of web site
	* @param 	String		Web site URL
	* @param 	int			Primary key of user who is the web site master
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
	* @param 	String		Web site name
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
	* @param 	String		Web site URL
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
	* @param 	int		Primary key of user that is web master
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
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_web_sites_table . " (web_site_name, web_site_url, web_master_id) VALUES ('" . addslashes($this->sWebSiteName) . "', '" . addslashes($this->sWebSiteURL) . "', $this->iWebMasterID)");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_web_sites_table";
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
			$result = $sql->query("UPDATE " . $default->owl_web_sites_table . " SET web_site_name = '" . addslashes($this->sWebSiteName) . "', web_site_url = '" . addslashes($this->sWebSiteURL) . "', web_master_id = $this->iWebMasterID WHERE id = $this->iId");
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
			$result = $sql->query("DELETE FROM $default->owl_web_sites_table WHERE id = $this->iId");
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
		$result = $sql->query("SELECT * FROM $default->owl_web_sites_table WHERE id = $iWebSiteID");
		if ($result) {
			if ($sql->next_record()) {
				$oWebSite = & new WebSite(stripslashes($sql->f("web_site_name")), stripslashes($sql->f("web_site_url")), $sql->f("web_master_id"));
				$oWebSite->iId = $iWebSiteID;
				return $oWebSite;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iWebSiteID . " table = $default->owl_web_sites_table";
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
		$result = $sql->query("SELECT * FROM " . $default->owl_web_sites_table);
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

?>

<?php
/**
 * $Id$
 *
 * Represents a Link as per the database table links.
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
 * @package lib.groups 
 */
class Link {
	
	/** primary key of current object */
	var $iId;
	/** Link name */
	var $sName;
	/** url link */
	var $sUrl;
	/** rank of website */
	var $iRank;
	
	
	function Link($sNewName, $sNewUrl, $iNewRank) {
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->sUrl = $sNewUrl;
		$this->iRank = $iNewRank;
	}
	
	function getUrl() {
		return $this->sUrl;
	}
	
	function setUrl($sNewValue) {
		$this->sUrl = $sNewValue;
	}
	
	function getRank() {
		return $this->iRank;
	}
	
	function setRank($sNewValue) {
		$this->iRank = $sNewValue;
	}
	
	function getName() {
		return $this->sName;
	}
	
	function setName($sNewValue) {
		$this->sName = $sNewValue;
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
			$query = "SELECT name FROM ". $default->quicklinks_table ." WHERE name = '" . $this->sName . "'";
            $sql->query($query);
            $rows = $sql->num_rows($sql);
        
            if ($rows > 0){
                $_SESSION["errorMessage"] = "Link::The Link name " . $this->sName . " is already in use!";
                return false;
            }

	       	$sql = $default->db;
			$query = "SELECT rank FROM ". $default->quicklinks_table ." WHERE rank = '" . $this->iRank . "'";
        	$sql->query($query);
       		$rows = $sql->num_rows($sql);
	       		 
	       	if ($rows > 0){
                $_SESSION["errorMessage"] = "Link::The Rank " . $this->iRank . " is already in use!";
                return false;
            }

            $sTable = $default->quicklinks_table;
            $aFieldValues = array(
                'name' => $this->sName,
                'url' => $this->sUrl,
                'rank' => $this->iRank,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = document_fields";
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
			$result = $sql->query("UPDATE " . $default->quicklinks_table . " SET name = '$this->sName', url = '$this->sUrl', rank = $this->iRank WHERE id = $this->iId");
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
				$result = $sql->query("DELETE FROM $default->quicklinks_table WHERE id = $this->iId");
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
	* Given a Links primary key it will create a 
	* Link object and populate it with the 
	* corresponding database values
	*
	* @return Link populated Link object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iLinkID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->quicklinks_table WHERE id = $iLinkID");
		if ($result) {
			if ($sql->next_record()) {
				$oLink = & new Link($sql->f("name"), $sql->f("url"), $sql->f("rank"));
				$oLink->iId = $iLinkID;
				return $oLink;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iLinkID . " table = $default->quicklinks_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

/**
	* Static function
	* Get a list ocuments
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of Link objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aLinkArray;
		settype($aLinkArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->quicklinks_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oLink = & Link::get($sql->f("id"));
				$aLinkArray[$iCount] = $oLink;
				$iCount++;
			}
			return $aLinkArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	
}
/*
     * static function
     *
     * gets the name of a unit using their id
     *
     * @param   String
     *   The name
     *   
     */
     
	function getLinkName($id)
	 {
	       global $default;
	
	        $name = lookupField("$default->quicklinks_table", "name", "id", $id );
	        
	        $this->sName= $name;
	}
	
	/*
     * static function
     *
     * gets the name of a unit using their id
     *
     * @param   String
     *   The name
     *   
     */
     
	function getLinkUrl($id)
	 {
	       global $default;
	
	        $url = lookupField("$default->quicklinks_table", "url", "id", $id );
	        
	        $this->sName= $url;
	}



/**
* Static function
*
* Creates a Link object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & linkCreateFromArray($aParameters) {
	$oLink = & new Link($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oLink;
}

?>

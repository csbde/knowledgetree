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
class Link extends KTEntity {
	
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

    function _fieldValues () {
        return array(
            'name' => $this->sName,
            'url' => $this->sUrl,
            'rank' => $this->iRank,
        );
    }

    function _table () {
        global $default;
        return $default->quicklinks_table;
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
			$query = "SELECT name FROM ". $default->quicklinks_table ." WHERE name = ?";/*ok*/
            $aParams = array($this->sName);
            $sql->query(array($query, $aParams));
            $rows = $sql->num_rows($sql);
        
            if ($rows > 0){
                $_SESSION["errorMessage"] = "Link::The Link name " . $this->sName . " is already in use!";
                return false;
            }

	       	$sql = $default->db;
			$query = "SELECT rank FROM ". $default->quicklinks_table ." WHERE rank = ?";/*ok*/
            $aParams = array($this->iRank);
        	$sql->query(array($query, $aParams));
       		$rows = $sql->num_rows($sql);
	       		 
	       	if ($rows > 0){
                $_SESSION["errorMessage"] = "Link::The Rank " . $this->iRank . " is already in use!";
                return false;
            }
        }
        return parent::create();
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
		$result = $sql->query(array("SELECT * FROM $default->quicklinks_table WHERE id = ?", $iLinkID));/*ok*/
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
            return KTEntityUtil::getList(Link::_table(), 'Link', $sWhereClause);
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

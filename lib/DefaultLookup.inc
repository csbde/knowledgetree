<?php
/**
 * $Id$
 *
 * Represents the base class for any table in the database that ends in a _lookup
 * i.e. any tables containing only an ID and name column
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
 * @package lib
 */
class DefaultLookup extends KTEntity {
	
	/** database table name */
	var $sTableName;
	/** primary key */
	var $iId;
	/** name value */
	var $sName;
	
	function DefaultLookup($sNewTableName, $sNewName) {
		//object not stored yed
		$this->iId = -1;
		$this->sTableName = $sNewTableName;
		$this->sName = $sNewName;
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
	* Get the name value for this object
	*
	* @return String name value for this object
	*
	*/
	function getName() {
		return $this->sName;
	}
	
	/**
	* Set the name value for this object
	*
	* @param 	New name value
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
        return $this->sTableName;
    }
	
	/**
	* Static function.
	* Given a primary key and a tablename, will create
	* the corresponding lookup object
	*
	* @param 	Database table to query
	* @param 	Primary key of object to retrieve
	*
	* @return Lookup lookup object populated with relevant values
	*/
	function & get($sTableName, $iId) {
		$sql = $default->db;
		$result = $sql->query(array("SELECT * FROM $sTableName WHERE id = ?", $iId));/*ok*/
		if ($result) {
			if ($sql->next_record()) {
				$oLookup = & new DefaultLookup($sTableName, $sql->f("name"));
				$oLookup->iId = $iId;
				return $oLookup;
			}		 
			return false;
		}
		return false;		
	}
	
}

?>

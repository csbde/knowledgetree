<?php
/**
 * $Id$
 *
 * Represents archiving types.
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
 * @package lib.archiving 
 */
class ArchivingType extends KTEntity {

	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The archiving type
	 */
	var $sName;
	
	/**
	 * Constructs an archiving type instance
	 *
	 * @param string the archiving type name 
	 */
	function ArchivingType($sNewName) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->sName = $sNewName;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the name
	 */
	function getName() {
		return $this->sName;
	}

	/**
	 * Sets the name
	 *
	 * @param string the new name
	 */
	function setName($sNewName){
		$this->sName = $sNewName;
	}

    function _fieldValues () {
        return array(
            'name' => $this->sName,
        );

    }

    function _table () {
        global $default;
        return $default->archiving_type_lookup_table;
    }

    /**
     * Static function.  Given a news item primary key will create
     * a ArchivingType object and populate it with the corresponding
     * database values
     *
     * @return ArchivingType populated ArchivingType object on success, false otherwise
     */
    function & get($iArchivingTypeID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * FROM $default->archiving_type_lookup_table WHERE id = ?", $iArchivingTypeID));/*ok*/
        if ($sql->next_record()) {
            $oArchivingType = & new ArchivingType($sql->f("name"));
            $oArchivingType->iId = $iArchivingTypeID;
            return $oArchivingType;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of ArchivingType objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of ArchivingType objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aArchivingTypeArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->archiving_type_lookup_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));/*wc*/
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oArchivingType = & ArchivingType::get($sql->f("id"));
                $aArchivingTypeArray[$iCount++] = $oArchivingType;
            }
            return $aArchivingTypeArray;
        }
        return false;
    }    
}

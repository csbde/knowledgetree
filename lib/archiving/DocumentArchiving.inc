<?php
/**
 * $Id$
 *
 * Represents archive settings for a document.
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

class DocumentArchiving extends KTEntity {
	/**
	 * The primary key
	 */
	var $iId;
	/**
	 * The document ID
	 */
	var $iDocumentID;

	/**
	 * The archiving settings
	 */
	var $iArchivingSettingsID;	
	
	/**
	 * Constructs an archive settings instance
	 *
	 * @param integer the document id
	 * @param integer the archiving type id
	 * @param integer the archiving settings id
	 */
	function DocumentArchiving($iNewDocumentID, $iNewArchivingSettingsID) {
		global $default;
		
		// primary key not set as this is not stored yet
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->iArchivingSettingsID = $iNewArchivingSettingsID;
	}

	/**
	 * Gets the primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the document id
	 */
	function getDocumentID(){
		return $this->iDocumentID;
	}

	/**
	 * Sets the document id
	 *
	 * @param integer the new document id
	 */
	function setDocumentID($iNewDocumentID){
		$this->iDocumentID = $iNewDocumentID;
	}
	
	/**
	 * Gets the archiving settings
	 */
	function getArchivingSettingsID(){
		return $this->iArchivingSettingsID;
	}

	/**
	 * Sets the archiving settings
	 * 
	 * @param integer the new archiving settings
	 */ 
	function setArchivingSettingsID($iNewArchivingSettingsID){
		$this->iArchivingSettingsID = $iNewArchivingSettingsID;
	}

    function _fieldValues () {
        return array(
            'document_id' => $this->iDocumentID,
            'archiving_settings_id' => $this->iArchivingSettingsID,
        );
    }

    function _table () {
        global $default;
        return $default->document_archiving_table;
    }
		
    /**
     * Static function.  Given a document primary key will create
     * a DocumentArchiving object and populate it with the corresponding
     * database values
     *
     * @return DocumentArchiving populated DocumentArchiving object on success, false otherwise
     */
    function & getFromDocumentID($iDocumentID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * FROM $default->document_archiving_table WHERE document_id = ?", $iDocumentID));/*ok*/
        if ($sql->next_record()) {
            $oDocumentArchiving = & new DocumentArchiving($sql->f("document_id"), $sql->f("archiving_settings_id"));
            $oDocumentArchiving->iId = $sql->f("id");
            return $oDocumentArchiving;          
        }
        return false;
    }
    
    /**
     * Static function.  Given a news item primary key will create
     * a DocumentArchiving object and populate it with the corresponding
     * database values
     *
     * @return DocumentArchiving populated DocumentArchiving object on success, false otherwise
     */
    function & get($iDocumentArchivingID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * FROM $default->document_archiving_table WHERE id = ?", $iDocumentArchivingID));/*ok*/
        if ($sql->next_record()) {
            $oDocumentArchiving = & new DocumentArchiving($sql->f("document_id"), $sql->f("archiving_settings_id"));
            $oDocumentArchiving->iId = $iDocumentArchivingID;
            return $oDocumentArchiving;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of DocumentArchiving objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of DocumentArchiving objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aDocumentArchivingArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM $default->document_archiving_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));/*wc*/
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDocumentArchiving = & DocumentArchiving::get($sql->f("id"));
                $aDocumentArchivingArray[$iCount++] = $oDocumentArchiving;
            }
            return $aDocumentArchivingArray;
        }
        return false;
    }    
}

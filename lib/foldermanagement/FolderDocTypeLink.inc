<?php
/**
 * $Id$
 *
 * Represents an entry in the database as per the folder_doctype_link table.
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
 * @package lib.foldermanagement 
 */
class FolderDocTypeLink extends KTEntity {
	
	/** primary key of object */
	var $iId;
	/** primary key of folder */
	var $iFolderID;
	/** primary key of document type */
	var $iDocumentTypeID;
	
	function FolderDocTypeLink($iNewFolderID, $iNewDocumentTypeID) {
		$this->iId = -1;
		$this->iFolderID = $iNewFolderID;
		$this->iDocumentTypeID = $iNewDocumentTypeID;
	}
	
	/**
	* Create the current folder in the database
	*
	* @return boolean true and set $this->iId with new primary key, false otherwise and set $_SESSION["errorMessage"]
	*/
	function create() {
		global $default, $lang_err_database; $lang_err_object_exists;
		//if the object has not already been stored		
		if ($this->iId < 0) {			
            $sTable = $default->folder_doctypes_table;
            $aFieldValues = array(
                'folder_id' => $this->iFolderID,
                'document_type_id' => $this->iDocumentTypeID,
            );
            $id =& DBUtil::autoInsert($sTable, $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = folders";
		return false;
	}
	
	/**
	* Static function.  
	* Given a folder_doctype_link primary key will create
	* a FolderDocTypeLink object and populate it with the corresponding
	* database values
	*
	* @param $iFolderDocTypeID		Primary key of folder to get
	*
	* @return Folder folder object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
	*/
	function get($iFolderDocTypeLinkID) {		
		global $default,  $lang_err_object_not_exist;
		$sql = $default->db;		
		$sql->query("SELECT * FROM " . $default->folder_doctypes_table . " WHERE id = " . $iFolderDocTypeLinkID);
		if ($sql->next_record()) {
			$oFolderDocTypeLink = & new FolderDocTypeLink($sql->f("folder_id"), $sql->f("document_type_id"));
            $oFolderDocTypeLink->iId = $iFolderDocTypeLinkID;			
			return $oFolderDocTypeLink;
		} 
 		$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderID . " table = folders";
		return false;
	}
	
	/**
    * Static function
    * Get a list of Documents
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of Documents objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        global $default, $lang_err_database;
        $aFolderDocTypeLinkArray;
        settype($aFolderDocTypeLinkArray, "array");
        $sql = $default->db;
        // TODO: join on sys_deleted
        $result = $sql->query("SELECT * FROM " . $default->folder_doctypes_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oFolderDocTypeLink = & FolderDocTypeLink::get($sql->f("id"));
                $aFolderDocTypeLinkArray[$iCount] = $oFolderDocTypeLink;
                $iCount++;
            }
            return $aFolderDocTypeLinkArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
	
}
?>

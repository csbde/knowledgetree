<?php
require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/security/permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
/**
 * $Id$
 *
 * Contains folder view document browsing business logic.
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
 * @package lib.browse
 */
class FolderBrowser extends Browser {

	/**
	 * Construct a new FolderBrowser instance with the specified sort criteria
	 *
	 * @param string the field to sort the results by
	 * @param string the direction to sort the results
	 */
	function FolderBrowser($sSortField = "name", $sSortDirection = "asc", $aNewSortCriteria = array()) {
		Browser::Browser($sSortField, $sSortDirection, $aNewSortCriteria);
	}
														 										   		  									 	  
    /**
     * Browse the documents by folder
     *
     * @return array folders and documents
     */
    function browse() {
        global $default, $fFolderID;

		// we're browsing folders
		$this->setBrowseStart($fFolderID);
		$iFolderID = $fFolderID;
		
        // instantiate and initialise
        $results = array();
        $sql = $default->db;
        
        // no folder specified, so depending on the users groups, resolve to the right folderID
        if ($iFolderID == "") {
            // no folder specified, so start at the root folder
                        
            // look up this users unit
            $unitID = User::getUnitID($_SESSION["userID"]);
            
            if ($unitID) {
                // if the user is in a unit, start at the unit's root folder
                
                // lookup the unit name
                $unitName = lookupField($default->units_table, "name", "id", $unitID);             

                // the unit root folder has the same name as the unit
                // FIXME: dodgy i know, but its easy
                $unitRootFolderName = $unitName;
               
                // now lookup the folderID
                $aFolders = Folder::getList("name='$unitRootFolderName' and parent_id=1");
                
                if (!$aFolders) {
                    // no folder exists with this name, so start at the root
                    $iFolderID = lookupID($default->folders_table, "parent_id", 0);
                } else {
                	$iFolderID = $aFolders[0]->getID();
                }
                
            } else {
                // else just start at the root
                // FIXME: start at the root folder for the default organisation
                $iFolderID = lookupID($default->folders_table, "parent_id", 0);
             }
        }
        
        $default->log->debug("FolderBrowser::browseByFolder: folderID=$iFolderID");
        
        // get the folder
        $results["folders"][] = & Folder::get($iFolderID);
        if ($results["folders"][0]) {
            
            // now find all the child folders relative to this one 
            // FIXME: in the same unit?
            
            $aLookupCriteria = $this->aSortCriteria[$this->sSortField]["lookup"];
                        
            // if we're sorting by name or creator_id then sort folders in the appropriate direction
            $sFolderQuery = "SELECT f.id FROM $default->folders_table AS f ";
			if (in_array($this->sSortField, array("name", "creator_id"))) {             
	            if (isset($aLookupCriteria)) {
	            	$sFolderQuery .= "INNER JOIN " .  $aLookupCriteria["table"] . " lt ON f.$this->sSortField=lt.id WHERE parent_id=" . $iFolderID;
	            	$sFolderQuery .= " ORDER BY lt." . $aLookupCriteria["field"] . " $this->sSortDirection";
	            } else {
	             	$sFolderQuery .= "WHERE parent_id=$iFolderID ORDER BY $this->sSortField $this->sSortDirection";
	            }
			} else {
				$sFolderQuery .= "WHERE parent_id=$iFolderID ORDER BY f.name asc";
			}
			$default->log->debug("folderQuery = $sFolderQuery");
            if ($sql->query($sFolderQuery)) {
                while ($sql->next_record()) {
                    // add the child folders to the array
                    $results["folders"][] = & Folder::get($sql->f("id"));
                }
            }
            
            // create query to retrieve documents in this folder
            $documentQuery  = "SELECT d.id as id FROM $default->documents_table AS d ";
            if ( isset($aLookupCriteria) ) {
            	$documentQuery .= "INNER JOIN " .  $aLookupCriteria["table"] . " lt ON ";
            	$documentQuery .= "d.$this->sSortField" . "=lt." . (isset($aLookupCriteria["joinColumn"]) ? $aLookupCriteria["joinColumn"] : "id");
            }            
            $documentQuery .= " WHERE d.folder_id=$iFolderID " . (isset($aLookupCriteria["whereClause"]) ? "AND lt." . $aLookupCriteria["whereClause"] : "") . " ";            
			if ( isset($aLookupCriteria) ) {
            	$documentQuery .= "ORDER BY lt." . $aLookupCriteria["field"] . " $this->sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $this->sSortField $this->sSortDirection";
            }
            $default->log->debug("docQuery=$documentQuery");
            
            // initialise access flag;
            $results["accessDenied"] = false;
            if ($sql->query($documentQuery)) {
                while ($sql->next_record()) {
                    // check permissions
                    if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                        // add documents to array
                        // set file attributes
                        $oDocument = & Document::get($sql->f("id"));
                        if ($oDocument->isLive()) {
                        	$results["documents"][] = $oDocument;
                        } 
                    } else {
                        // set access denied message
                        $results["accessDenied"] = true;
                    }
                }
            } else {
                $_SESSION["errorMessage"] = "documents table select failed";
            }
            
            return $results;
        }
    }
    
    function getSectionName() {
    	return "Manage Documents";
    }
}
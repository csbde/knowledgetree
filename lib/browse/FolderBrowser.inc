<?php

require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/security/permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

/**
 * 
 * $Id$
 * 
 * Contains document browsing business logic.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.documentmanagement
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
                $unitName = lookupField($default->owl_units_table, "name", "id", $unitID);             

                // the unit root folder has the same name as the unit
                // FIXME: dodgy i know, but its easy
                $unitRootFolderName = $unitName;
               
                // now lookup the folderID
                $aFolders = Folder::getList("name='$unitRootFolderName' and parent_id=1");
                
                if (!$aFolders) {
                    // no folder exists with this name, so start at the root
                    $iFolderID = lookupID($default->owl_folders_table, "parent_id", 0);
                } else {
                	$iFolderID = $aFolders[0]->getID();
                }
            } else {
                // else just start at the root
                // FIXME: start at the root folder for the default organisation
                $iFolderID = lookupID($default->owl_folders_table, "parent_id", 0);
             }
        }
        
        $default->log->debug("FolderBrowser::browseByFolder: folderID=$iFolderID");
        
        // get the folder
        $results["folders"][] = & Folder::get($iFolderID);
        if ($results["folders"][0]) {
            
            // now find all the child folders relative to this one 
            // FIXME: in the same unit?
            
            // if we're sorting by name then sort folders in the appropriate direction
            $folderQuery = "SELECT id FROM $default->owl_folders_table WHERE parent_id=" . $iFolderID . " ORDER BY name " . ($this->sSortField == "name" ? $this->sSortDirection : "ASC");

            if ($sql->query($folderQuery)) {
                while ($sql->next_record()) {
                    // add the child folders to the array
                    $results["folders"][] = & Folder::get($sql->f("id"));
                }
            }
            
            // create query to retrieve documents in this folder
            $documentQuery  = "SELECT d.id as id FROM $default->owl_documents_table d ";                       
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$this->sSortField=lt.id ";
            }            
            $documentQuery .= "WHERE d.folder_id=$iFolderID ";            
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $this->sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $this->sSortField $this->sSortDirection";
            }
            
            // initialise access flag;
            $results["accessDenied"] = false;
            if ($sql->query($documentQuery)) {
                while ($sql->next_record()) {
                    // check permissions
                    if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                        // add documents to array
                        // set file attributes
                        $results["documents"][] = & Document::get($sql->f("id")); 
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
}
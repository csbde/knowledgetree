<?php

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
class DocumentBrowser {
    
    var $aSortCriteria = array ( "name"       		=> array ("display" => "Title"), 	
	 						 	 "filename"   		=> array ("display" => "Filename"),
							 	 "creator_id" 		=> array ("display" => "Creator", 
							 	 						      "lookup"  => array ("lookupTable" => "users",
							 	 										   		  "lookupField" => "name")),
								 "modified"         => array ("display" => "Modified"),
								 "document_type_id" => array ("display" => "Document Type",
							 	 						      "lookup"  => array ("lookupTable" => "document_types_lookup",
							 	 										   		  "lookupField" => "name")) );
	/**
	 * Returns the document sort criteria
	 */					
	function getSortCriteria() {
		return $this->aSortCriteria;
	}
								 	  
    /**
     * Browse the documents by folder
     *
     * @param int the folder to browse from
     * @return array folders and documents
     */
    function browseByFolder($folderID = -1, $sSortField = "name", $sSortDirection = "asc") {
        global $default;

        // instantiate and initialise
        $results = array();
        $sql = $default->db;
        
        // no folder specified, so depending on the users groups, resolve to the right folderID
        if ($folderID == -1) {
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
                    $folderID = lookupID($default->owl_folders_table, "parent_id", 0);
                } else {
                	$folderID = $aFolders[0]->getID();
                }
            } else {
                // else just start at the root
                // FIXME: start at the root folder for the default organisation
                $folderID = lookupID($default->owl_folders_table, "parent_id", 0);
             }
        }
        
        $default->log->debug("DocumentBrowser::browseByFolder: folderID=$folderID");
        
        // get the folder
        $results["folders"][] = & Folder::get($folderID);
        if ($results["folders"][0]) {
            
            // now find all the child folders relative to this one 
            // FIXME: in the same unit?
            
            // if we're sorting by name then sort folders in the appropriate direction
            $folderQuery = "SELECT id FROM $default->owl_folders_table WHERE parent_id=" . $folderID . " ORDER BY name " . ($sSortField == "name" ? $sSortDirection : "ASC");
            $default->log->debug("DocumentBrowser::browseByFolder child folder query=$folderQuery");
            if ($sql->query($folderQuery)) {
                while ($sql->next_record()) {
                    // add the child folders to the array
                    $results["folders"][] = & Folder::get($sql->f("id"));
                }
            }
            
            // create query to retrieve documents in this folder
            $documentQuery  = "SELECT d.id as id FROM $default->owl_documents_table d ";                       
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$sSortField=lt.id ";
            }            
            $documentQuery .= "WHERE d.folder_id=$folderID ";            
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $sSortField $sSortDirection";
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
        } else {
            return false;
        }
    }

    /**
     * Browse the documents by category
     *
     * @param string the category to browse
     */    
    function browseByCategory($category = "", $sSortField = "name", $sSortDirection = "asc") {
        global $default;
        
        // TODO: add this to default inserts
        $categoryField = "Category";
        $results = array();
        $sql = $default->db;

        // lookup document_fields id for category
        $categoryFieldID = lookupID($default->owl_fields_table, "name", "$categoryField");
        $default->log->debug("DocumentBrowser::browseByCategory categoryFieldID=$categoryFieldID");

        if ($category == "") {
            $default->log->debug("DocumentBrowser::browseByCategory no category supplied, returning list");
            // no category value supplied, so return a list of categories
            // set the first value to "categories"
            $results["categories"][] = "Categories";
           
            // get a list of category values
            $query = "SELECT DISTINCT value FROM $default->owl_document_fields_table WHERE document_field_id=$categoryFieldID ORDER BY value ASC";
            $default->log->debug("DocumentBrowser::browseByCategory category listing query=$query");
            $sql->query($query);
            // loop through resultset, build array and return
            while ($sql->next_record()) {
                $results["categories"][] = $sql->f("value");
            }
            // its ok if we return an empty array- the UI's responsibility to check and print an error
            return $results;
        } else {
            $results["categories"][] = $category;
            
            // we have a category to use, so find all the documents
            // with this category value
            $query = "SELECT df.document_id FROM $default->owl_document_fields_table df " .
                     "INNER JOIN $default->owl_documents_table d ON df.document_id = d.id ";
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$query .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$sSortField=lt.id ";
            }                    
			$query .= "WHERE df.document_field_id = $categoryFieldID AND value='$category' ";
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$query .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $sSortDirection";
            } else {
            	$query .= "ORDER BY d.$sSortField $sSortDirection";
            }                    
                     
            $sql->query($query);
            // loop through resultset and add to array
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                // check permissions
                if (Permission::userHasDocumentReadPermission($sql->f("document_id"))) {
                    $results["documents"][] = & Document::get($sql->f("document_id"));
                } else {
                    $results["accessDenied"] = true;
                }
             }
            
            return $results;
        }
    }
    
    /**
     * Browse the documents by document type
     *
     * @param int the document type ID to browse
     */    
    function browseByDocumentType($documentTypeID = -1, $sSortField = "name", $sSortDirection = "asc") {
        global $default;
        
        $results = array();
        $sql = $default->db;
        
        if ($documentTypeID == -1) {
            // no document type idsupplied, so return a list of document types
            // set the first value to "Document Types"
            $results["documentTypes"][] = array("name" => "Document Types");
            
            // return a list of document types
            $query = "SELECT * FROM $default->owl_document_types_table";
            $sql->query($query);
            while ($sql->next_record()) {
                $results["documentTypes"][] = array ("id" => $sql->f("id"), "name" => $sql->f("name"));
            }
            return $results;
        } else {
            // lookup document type name from the passed in id
            $documentTypeName = lookupField($default->owl_document_types_table, "name", "id", $documentTypeID);
            $results["documentTypes"][] = array("id" => $documentTypeID, "name" => $documentTypeName);
                       
            // create query to retrieve documents with this document type
            $documentQuery  = "SELECT d.id as id FROM $default->owl_documents_table d ";
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$sSortField=lt.id ";
            }
            $documentQuery .= "WHERE document_type_id=$documentTypeID ";
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $sSortField $sSortDirection";
            }
                        
            // loop through resultset and populate array with document objects
            $sql->query($documentQuery);
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                // check permission
                if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                    $results["documents"][] = & Document::get($sql->f("id"));
                } else {
                    $results["accessDenied"] = true;
                }
            }
            $default->log->debug("DocumentBrowser::browseByDocumentType: results=" . arrayToString($results));
            
            return $results;
        }
    }
}
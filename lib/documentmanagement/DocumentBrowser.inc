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
    
    /**
     * Browse the documents by folder
     *
     * @param int the folder to browse from
     * @return array folders and documents
     */
    function browseByFolder($folderID = -1) {
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
                $default->log->debug("DocumentBrowser::browseByFolder: unitID=$unitID; unitName=$unitName");               

                // the unit root folder has the same name as the unit
                // FIXME: dodgy i know, but its easy
                $unitRootFolderName = $unitName;
               
                // now lookup the folderID
                $folderID = lookupID($default->owl_folders_table, "name", $unitRootFolderName);
                $default->log->info("DocumentBrowser::browseByFolder looked up unit root folderID=$folderID; unit root foldername=$unitRootFolderName");                    
            } else {
                // else just start at the root
                $folderID = lookupID($default->owl_folders_table, "parent_id", 0);
                $default->log->info("DocumentBrowser::browseByFolder: starting at docroot folderID=$folderID");
             }
        } else {
            $default->log->info("DocumentBrowser::browseByFolder starting at passed in folderID=$folderID");
        }
        
        $default->log->debug("DocumentBrowser::browseByFolder: folderID=$folderID");
        
        // get the folder
        $results["folders"][] = & Folder::get($folderID);
        if ($results["folders"][0]) {
            
            // now find all the child folders relative to this one 
            // FIXME: in the same unit?
            $folderQuery = "SELECT id FROM $default->owl_folders_table WHERE parent_id=" . $folderID . " ORDER BY name ASC";
            $default->log->debug("DocumentBrowser::browseByFolder child folder query=$folderQuery");
            if ($sql->query($folderQuery)) {
                while ($sql->next_record()) {
                    // add the child folders to the array
                    $results["folders"][] = & Folder::get($sql->f("id"));
                }
            }
            
            // create query to retrieve documents in this folder
            $documentQuery = "SELECT id FROM $default->owl_documents_table WHERE folder_id=$folderID ORDER BY name ASC";
            $default->log->debug("DocumentBrowser::browseByFolder about to execute $documentQuery");
            if ($sql->query($documentQuery)) {
                while ($sql->next_record()) {
                    // check permissions
                    if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                        // add documents to array
                        // set file attributes
                        $results["documents"][] = & Document::get($sql->f("id")); 
                    } else {
                        $default->log->debug("DocumentBrowser::browseByFolder: read permission denied for document id=" . $sql->f("id"));
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
    function browseByCategory($category = "") {
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
            
            $default->log->debug("DocumentBrowser::browseByCategory get documents for category $category");
            // we have a category to use, so find all the documents
            // with this category value
                        
            $query = "SELECT document_id FROM $default->owl_document_fields_table WHERE document_field_id = $categoryFieldID " . 
                     "AND value='$category' ORDER BY value ASC";
            $default->log->debug("DocumentBrowser::browseByCategory documents in category query=$query");
            $sql->query($query);
            // loop through resultset and add to array
            while ($sql->next_record()) {
                // check permissions
                if (Permission::userHasDocumentReadPermission($sql->f("document_id"))) {
                    $results["documents"][] = & Document::get($sql->f("document_id"));
                } else {
                    $default->log->debug("DocumentBrowser::browseByCategory permission denied for documentID=" . $sql->f("document_id"));
                }
             }
            $default->log->debug("DocumentBrowser::browseByCategory results=" . arrayToString($results));
            
            return $results;
        }
    }
    
    /**
     * Browse the documents by document type
     *
     * @param int the document type ID to browse
     */    
    function browseByDocumentType($documentTypeID = -1) {
        global $default;
        
        $results = array();
        $sql = $default->db;
        
        if ($documentTypeID == -1) {
            // no category value supplied, so return a list of categories
            // set the first value to "categories"
            $results["documentTypes"][] = array("name" => "Document Types");
            
            // return a list of document types
            $query = "SELECT * FRMO $default->owl_document_types_table";
            $sql->query($query);
            while ($sql->next_record()) {
                $results["documentTypes"][] = array ("id" => $sql->f("id"), "name" => $sql->f("name"));
            }
            return $results;
        } else {
            // lookup document type name from the passed in id
            $documentTypeName = lookupField($default->owl_document_types_table, "name", "id", $documentTypeID);
            $results["documentTypes"][] = array("id" => $documentTypeID, "name" => $documentTypeName);
            
            // find all documents with this document type
            $query = "SELECT DISTINCT id FROM $default->owl_documents_table WHERE document_type_id=$documentTypeID";
            $default->log->debug("DocumentBrowser::browseByDocumentType: documents from doc type query=$query");
            // loop through resultset and build array of documentIDs
            $sql->query($query);
            while ($sql->next_record()) {
                // check permission
                if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                    $results["documents"][] = & Document::get($sql->f("id"));
                } else {
                    $default->log->debug("DocumentBrowser::browseByDocumentType: permission denied for documentID=" . $sql->f("document_id"));
                }
            }
            $default->log->debug("DocumentBrowser::browseByDocumentType: results=" . arrayToString($results));
            
            return $results;
        }
    }
 
    /**
     * Checks whether the required group is one of the
     * groups in the $groupIDs array
     *
     * @param string the group name to check for
     * @param array an array of groupIDs to check
     * @return boolean true if $requiredGroupName is in the $groupIDs array, else false
     */
    function checkGroup($requiredGroupName, $groupIDs) {
        global $default;
        $sql = $default->db;
        // lookup the id of $requiredGroupName
        $requiredGroupID = lookupID($default->owl_groups_table, "name", "$requiredGroupName");
        $default->log->debug("DocumentBrowser::checkGroup reqGrpID=$requiredGroupID");
        if ($requiredGroupID) {
            // now loop through the array and check if we're in the group
            $default->log->debug("DocumentBrowser::checkGroup req=$requiredGroupName; ids=" . arrayToString($groupIDs)); 
            if (in_array($requiredGroupID, $groupIDs)) {
                return true;
            } else {
                return false;
            }
        } else {
            // this group doesn't exist
            // TODO: add this to language
            $_SESSION["errorMessage"] = "group $requiredGroupName doesn't exist";
            return false;
        }
    }
    
    /**
     * Returns an array of folder details for the specified folder
     *
     * @param string the sql query to retrieve the correct folder
     * @return array the folder details
     */
    function retrieveFolderDetails($folderQuery) {
        global $default;
        
        $folders = array();
        $folderSql = $default->db;
        // perform folder details query and loop through results
        if ($folderSql->query($folderQuery)) {
            // should only be one result
            if ($folderSql->next_record()) {
               $default->log->debug("DocumentBrowser::retrieveFolderDetails got results for $folderQuery");
               // add the folder details to the array
               $folderName = $folderSql->f("name");
               $folderID = $folderSql->f("id");

               // set folder attributes
               $folders[$folderName] = & new Folder($folderName, $folderSql->f("description"), $folderSql->f("parent_id"),
                                                  $folderSql->f("creator_id"), $folderSql->f("unit_id"), 
												  $folderSql->f("is_public"));
            } else {
                $_SESSION["errorMessage"] = "could not retrieve folder details: $folderQuery";
                $default->log->debug("DocumentBrowser::retrieveFolderDetails error=" . $_SESSION["errorMessage"]);
            }
        } else {
            $_SESSION["errorMessage"] = "could not retrieve folder details: $folderQuery";
        }
        return $folders;
    }
   
    /**
     * Lookup document details for all the document_ids in the input array
     *
     * @param array the documentIDs to retrieve details for
     * @return array the details of all the documents
     */
    function lookupDocumentDetails($documentIDs) {
        global $default;
        
        $documents = array();
        $sql = $default->db;
        
        // convert array to a comma separated string
        $documentIDStr = arrayToCSS($documentIDs);
        $default->log->debug("DocumentBrowser::lookupDocumentDetails documentIDStr=$documentIDStr; documentIDs=" . arrayToString($documentIDs));
        
        // create query to retrieve the details of the specified documents
        $documentQuery = "SELECT * FROM $default->owl_documents_table WHERE id in ($documentIDStr)";
        if ($sql->query($documentQuery)) {
            // TODO: check return status of query and die appropriately
            while ($sql->next_record()) {
                // set attributes
                $documents[$sql->f("name")] &= Document::get($sql->f("id"));
            }
        } else {
            $_SESSION["errorMessage"] = "document query failed";
        }
        return $documents;
    }
}
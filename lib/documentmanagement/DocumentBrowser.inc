<?php

require_once("$default->owl_fs_root/lib/security/permission.inc");

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
        $folders = array();
        $sql = new Owl_DB();
        
        if ($folderID == -1) {
            // no folder specified, so start at the root for this users organisation

            // lookup this users groups
            $groupIDs = lookupGroupIDs($_SESSION["userID"]);
            $default->log->debug("DocumentBrowser::browseByFolder: groupIDs=" . arrayToString($groupIDs));
            
            // look up this users unit
            // FIXME: what if the user belongs to multiple units?
            $unitID = lookupID($default->owl_groups_units_table, "group_id", $groupIDs[0]);
            // lookup the unit name
            $unitName = lookupField($default->owl_units_table, "name", "id", $unitID);
            $default->log->debug("DocumentBrowser::browseByFolder: unitID=$unitID; unitName=$unitName");
           
            // look up the organisation name for this user
            $organisationID = lookupField($default->owl_units_table, "organisation_id", "id", $unitID);
            $organisationName = lookupField($default->owl_organisations_table, "name", "id", $organisationID);
            $default->log->debug("DocumentBrowser::browseByFolder: organisationID=$organisationID; orgName=$organisationName");
            
            // construct the folder name from the organisation
            $rootFolderName = $organisationName . " Document Root";
          
            // lookup the id of the root folder
            $folderID = lookupID($default->owl_folders_table, "name", "$rootFolderName");
            $default->log->debug("DocumentBrowser::browseByFolder: folderID=$folderID");
            
            // if this is a system administrator, start her at the ROOT
            // TODO: add to default->sysadmin_group
            if ($this->checkGroup("System Administrators", $groupIDs)) {
               $folderQuery = "SELECT * FROM $default->owl_folders_table WHERE name='$rootFolderName'";
            } else {
               // otherwise start everyone relative to their unit
               
               // FIXME: actually need to lookup the unit root folder- which should map to the unitname
               //        and descend directly from the organisation document root

               $default->log->debug("DocumentBrowser::browseByFolder: unitName=$unitName");
               
               $unitRootFolder = $unitName . " Document Root";
               
               // lookup descendant folders with the appropriate unit set
               $folderQuery = "SELECT * from $default->owl_folders_table " . 
                              "WHERE name='$unitRootFolder'"; 
            }
        } else {
            // start from the specified folder
            $folderQuery = "SELECT * FROM $default->owl_folders_table WHERE id=$folderID";
        }
        
        // retrieve folder details
        $folders = $this->retrieveFolderDetails($folderQuery);
        
        // check if the user has access to this folder
        if (Permission::userHasFolderReadPermission($folderID)) {
            
            // lookup the name of the root folder
            $folderName = lookupField($default->owl_folders_table, "name", "id", $folderID);
            
            $default->log->debug("DocumentBrowser::browseByFolder folderID=$folderID; folderName=$folderName");
            $default->log->debug("DocumentBrowser::browseByFolder folders=" . arrayToString($folders));
            
            // now find all the child folders relative to this one
            $folderQuery = "SELECT * from $default->owl_folders_table WHERE parent_id=" . $folderID;
            $default->log->debug("DocumentBrowser::browseByFolder child folder query=$folderQuery");
            $childFolders = $this->retrieveFolderDetails($folderQuery);
            $default->log->debug("DocumentBrowser::browseByFolder childFolders=" . arrayToString($childFolders));
            
            // add children to array
            $folders[$folderName]["folders"] = $childFolders;
            
            // create query to retrieve documents in this folder
            $documentQuery = "SELECT * FROM $default->owl_documents_table WHERE folder_id=$folderID";
            $default->log->debug("DocumentBrowser::browseByFolder about to execute $documentQuery");
            if ($sql->query($documentQuery)) {
                while ($sql->next_record()) {
                    // check permissions
                    if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                        // add documents to array
                        // set file attributes
                        $folders[$folderName]["documents"][$sql->f("name")] = 
                                                                 array("id" => $sql->f("id"),
                                                                       "document_type_id" => $documentID,
                                                                       "name" => $documentName,
                                                                       "filename" => $sql->f("filename"),
                                                                       "size" => $sql->f("size"),
                                                                       "creator_id" => $sql->f("creator_id"),
                                                                       "modified" => $sql->f("modified"),
                                                                       "description" => $sql->f("description"),
                                                                       "mime_id" => $sql->f("mime_id"),
                                                                       "folder_id" => $sql->f("folder_id"),
                                                                       "major_version" => $sql->f("major_version"),
                                                                       "minor_version" => $sql->f("minor_version"),                                                            
                                                                       "is_checked_out" => $sql->f("is_checked_out"));
                    }
                }
            } else {
                $_SESSION["errorMessage"] = "documents table select failed";
            }
            
            return $folders;
            
        } else {
            // permission to view this folder denied
            $_SESSION["errorMessage"] = "you do not have permission to view this folder (" . $_SESSION["errorMessage"] . ")";
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
        $categories = array();
        $sql = new Owl_DB();

        // lookup document_fields id for category
        $categoryFieldID = lookupID($default->owl_fields_table, "name", "$categoryField");
        $default->log->debug("DocumentBrowser::browseByCategory categoryFieldID=$categoryFieldID");

        if ($category == "") {
            $default->log->debug("DocumentBrowser::browseByCategory no category supplied, returning list");
            // no category value supplied, so return a list of categories
            $categories = array();
            
            // now get a list of category values
            $query = "select value from $default->owl_document_fields_table where document_field_id=$categoryFieldID";
            $default->log->debug("DocumentBrowser::browseByCategory category listing query=$query");
            $sql->query($query);
            // loop through resultset, build array and return
            while ($sql->next_record()) {
                $categories[] = $sql->f("value");
            }
            // its ok if we return an empty array- the UI's responsibility to check and print an error
            return $categories;
        } else {
            $default->log->debug("DocumentBrowser::browseByCategory get documents for category $category");
            // we have a category to use, so find all the documents
            // with this category value
            
            // first lookup the document_field_id of this
            $query = "select document_id from $default->owl_document_fields_table where document_field_id = $categoryFieldID " . 
                     "and value='$category'";
            $default->log->debug("DocumentBrowser::browseByCategory documents in category query=$query");
            $sql->query($query);
            // loop through resultset and build comma separated list of documentIDs
            $documentIDs = array();
            while ($sql->next_record()) {
                // check permissions
                if (Permission::userHasDocumentReadPermission($sql->f("document_id"))) {
                    $documentIDs[] = $sql->f("document_id");
                }
            }
            $default->log->debug("DocumentBrowser::browseByCategory documentIDs=" . arrayToString($documentIDs));
            // use lookup function to retrieve details
            $documents = $this->lookupDocumentDetails($documentIDs);
            // add to array and return
            $categories[$category]["documents"] = $documents;
            return $categories;
        }
    }
    
    /**
     * Browse the documents by document type
     *
     * @param int the document type ID to browse
     */    
    function browseByDocumentType($documentTypeID = -1) {
        global $default;
        
        $documentTypes = array();
        $sql = new Owl_DB();
        
        if ($documentTypeID == -1) {
            // return a list of document types
            $query = "select * from $default->owl_document_types_table";
            $sql->query($query);
            while ($sql->next_record()) {
                $documentTypes[$sql->f("id")] = $sql->f("name");
            }
            return $documentTypes;
        } else {
            // lookup document type name
            $documentTypeName = lookupField($default->owl_document_types_table, "name", "id", $documentTypeID);
            
            // find all documents with this document type
            $query = "select id from $default->owl_documents_table where document_type_id=$documentTypeID";
            $default->log->debug("DocumentBrowser::browseByCategory documents from doc type query=$query");
            // loop through resultset and build array of documentIDs
            $documentIDs = array();
            $sql->query($query);
            while ($sql->next_record()) {
                // check permission
                if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                    $documentIDs[] = $sql->f("id");
                }
            }
            $default->log->debug("DocumentBrowser::browseByCategory documentIDs=" . arrayToString($documentIDs));
            // use lookup function to retrieve details
            $documents = $this->lookupDocumentDetails($documentIDs);
            // add to array and return
            $documentTypes[$documentTypeName]["documents"]= $documents;
            return $documentTypes;
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
        $sql = new Owl_DB();
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
        $folderSql = new Owl_DB();
        // perform folder details query and loop through results
        if ($folderSql->query($folderQuery)) {
            // should only be one result
            if ($folderSql->next_record()) {
               $default->log->debug("DocumentBrowser::retrieveFolderDetails got results for $folderQuery");
               // add the folder details to the array
               $folderName = $folderSql->f("name");
               $folderID = $folderSql->f("id");

               // set folder attributes
               $folders[$folderName] = array("id" => $folderID,
                                             "description" => $folderSql->f("description"),
                                             "parent_id" => $folderSql->f("parent_id"),
                                             "creator_id" => $folderSql->f("creator_id"),
                                             "document_type_id" => $folderSql->f("document_type_id"),
                                             "unit_id" => $folderSql->f("unit_id"),
                                             "is_public" => $folderSql->f("is_public"));
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
        $sql = new Owl_DB();
        
        // convert array to a comma separated string
        $documentIDStr = arrayToCSS($documentIDs);
        $default->log->debug("DocumentBrowser::lookupDocumentDetails documentIDStr=$documentIDStr; documentIDs=" . arrayToString($documentIDs));
        
        // create query to retrieve the details of the specified documents
        $documentQuery = "select * from $default->owl_documents_table where id in ($documentIDStr)";
        if ($sql->query($documentQuery)) {
            // TODO: check return status of query and die appropriately
            while ($sql->next_record()) {
                // set attributes
                $documents[$sql->f("name")] = array("id" => $sql->f("id"),
                                                  "document_type_id" => $sql->f("id"),
                                                  "name" => $sql->f("name"),
                                                  "filename" => $sql->f("filename"),
                                                  "size" => $sql->f("size"),
                                                  "creator_id" => $sql->f("creator_id"),
                                                  "modified" => $sql->f("modified"),
                                                  "description" => $sql->f("description"),
                                                  "mime_id" => $sql->f("mime_id"),
                                                  "folder_id" => $sql->f("folder_id"),
                                                  "major_version" => $sql->f("major_version"),
                                                  "minor_version" => $sql->f("minor_version"),                                                            
                                                  "is_checked_out" => $sql->f("is_checked_out")); 
            }
        } else {
            $_SESSION["errorMessage"] = "document query failed";
        }
        return $documents;
    }
}
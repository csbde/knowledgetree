<?php

/**
 * $Id$
 * 
 * Contains document browsing business logic.
 *
 * @version $Revision$ 
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
class DocumentBrowser {
    
    // TODO: need separation of logic from presentation
    
    /**
     * Browse the documents by folder
     *
     * @param $folderID    the folder to browse from
     */
    function browseByFolder($folderID = -1) {
        global $default;

        // instantiate and initialise
        $folders = array();
        $folderSql = new Owl_DB();
        $documentSql = new Owl_DB();
        // TODO: put this in defaults?
        $rootFolderName = "Root Document Folder";
        
        if ($folderID == -1) {
            // no folder specified, so start at the root
            // TODO: unitID should be on session
            
            // if this is a system administrator, start her at the ROOT
            // FIXME: implement checkGroup
            if (checkGroup("System Administrators", $_SESSION["group_id"])) {
               $folderQuery = "select * from $default->owl_folders_table where description='$rootFolderName'";
            } else {
               // otherwise start everyone relative to their unit
               $folderQuery = "select * from $default->owl_folders_table " . 
                        "where unit_id=" . $_SESSION["unit_id"] . 
                        " and description='$rootFolderName'";
            }
        } else {
            // start from the specified folder
            // TODO: check that the user has access to this folder?
            //       should be done in checkPermissions routine.
            $folderQuery = "select * from $default->owl_folders_table where id=$folderID";
        }
        
        // perform query and loop through results
        $folderSql->query($query);
        // TODO: check return status of query and die appropriately
        //$numrows = $folderSql->num_rows($sql);
        while($folderSql->next_record()) {
           // for each folder, add it to the array
           $folderName = $folderSql->f("name");
           $folders[$folderName] = array();
            
           // for convenience and reuse down below
           $folderID = $folderSql->f("id");
           // set folder attributes
           // FIXME: should the ui do a lookup before displaying the id information
           //        or should the additional lookups happen here and pass back names instead of ids?
           //       or both?
           $folders[$folderName] = array("id" => $folderID,
                                         "description" => $folderSql->f("description"),
                                         "parent_id" => $folderSql->f("parent_id"),
                                         "creator_id" => $folderSql->f("creator_id"),
                                         "document_type_id" => $folderSql->f("document_type_id"),
                                         "unit_id" => $folderSql->f("unit_id"),
                                         "is_public" => $folderSql->f("is_public"));                                         
           // create query to retrieve documents in this folder
           $documentQuery = "select * from default->owl_documents_table where folder_id=$folderID";
           $documentSql->query($documentQuery);
           // TODO: check return status of query and die appropriately
           while ($documentSql->next_record()) {
               // add documents to array
               $documentName = $documentSql->f("name");
               // set file attributes
               $folders[$folderName][$documentName] = array("id" => $documentSql->f("id"),
                                                            "document_type_id" => $documentSql->f("id"),
                                                            "name" => $documentName,
                                                            "filename" => $documentSql->f("filename"),
                                                            "size" => $documentSql->f("size"),
                                                            "creator_id" => $documentSql->f("creator_id"),
                                                            "modified" => $documentSql->f("modified"),
                                                            "description" => $documentSql->f("description"),
                                                            "mime_id" => $documentSql->f("mime_id"),
                                                            //"folder_id" => $documentSql->f("folder_id"),
                                                            "major_version" => $documentSql->f("major_version"),
                                                            "minor_version" => $documentSql->f("minor_version"),                                                            
                                                            "is_checked_out" => $documentSql->f("is_checked_out")); 
           }
        }
        return $folders;        
    }

    /**
     * Browse the documents by category
     *
     * @param $category    the category to browse
     */    
    function browseByCategory($category = "") {
        $global default;
        
        // TODO: add this to default inserts
        $categoryField = "Category";
        $categories = new array();
        $categorySql = new Owl_DB();

        // lookup document_fields id for category
        $query = "select id from $default->owl_fields_table where name='$categoryField'";
        // TODO: if there are no categories then die
        $categorySql->query($query);
        // TODO: check return status of query and die appropriately
        $numrows = $categorySql->num_rows($sql);
        if ($numrows == 1) {
            $categorySql->next_record();
            $category_fieldID = $categorySql->f("id");
        } else {
            // error
            die("no category field in the db.  contact sysadmin.");
        }

        if ($category == "") {
            // no category supplied, so return a list of categories
            $categories = array();
            
            // now get a list of category values
            $query = "select value from $default->owl_document_fields_table where document_field_id=$category_fieldID";
            $categorySql->query($query);
            // loop through resultset, build array and return
            while ($categorySql->next_record()) {
                $categories[] = $categorySql->f("value");
            }
            // its ok if we return an empty array- the UI's responsibility to check and print an error
            return $categories;
        } else {
            // we have a category to use, so find all the documents
            // with this category value
            
            // first lookup the document_field_id of this
            $query = "select document_id from $default->owl_document_fields_table where document_field_id = $category_fieldID " . 
                     "and value='$category'";
            $categorySql->query($query);
            // loop through resultset and build comma separated list of documentIDs
            $documentIDs = "";
            while ($categorySql->next_record()) {
                $documentIDs = $categorySql->f("document_id") . ",";
                //$categories[] = $categorySql->f("document_id");
            }
            // trim the last comma
            $documentIDs = substr("$documentIDs", 0, -1);
            // use lookup function to retrieve details
            $documents = $this->lookupDocumentDetails($documentIDs);
            // add to array and return
            $categories[$category] = $documents;
            return $categories;
        }
    }
    
    /**
     * Lookup document details for all the document_ids in the comma separated input string
     *
     * @param    $documentIDs
     * @return an array containing the details of all the documents
     */
    function lookupDocumentDetails($documentIDs) {
        global $default;
        
        $documents = array();
        $sql = new Owl_DB();
        
        // create query to retrieve the details of the specified documents
        $documentQuery = "select * from $default->owl_documents_table where document_id in ($documentIDs)";
        $sql->query($documentQuery);
        // TODO: check return status of query and die appropriately
        while ($sql->next_record()) {
            // set attributes
            $documents[$sql->f("id")] = array("id" => $sql->f("id"),
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
        return $folders;
    }
    /**
     * Browse the documents by document type
     *
     * @param $documentTypeID    the document type ID to browse
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
            // find all documents with this document type
            $query = "select document_id from $default->owl_documents_table where document_type_id=$documentTypeID";
            // loop through resultset and build comma separated list of documentIDs
            $documentIDs = "";
            while ($sql->next_record()) {
                $documentIDs = $sql->f("document_id") . ",";
            }
            // trim the last comma
            $documentIDs = substr("$documentIDs", 0, -1);
            // use lookup function to retrieve details
            $documents = $this->lookupDocumentDetails($documentIDs);
            // add to array and return
            $documentTypes[$documentTypeID] = $documents;
            return $documentTypes;
        }
    }
}

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
class CategoryBrowser extends Browser {
    
	/**
	 * Construct a new FolderBrowser instance with the specified sort criteria
	 *
	 * @param string the field to sort the results by
	 * @param string the direction to sort the results
	 */
	function CategoryBrowser($sSortField = "name", $sSortDirection = "asc", $aNewSortCriteria = array()) {
		Browser::Browser($sSortField, $sSortDirection, $aNewSortCriteria);
	}
	    
    /**
     * Browse the documents by category
     *
     * @return array categories and documents     
     */    
    function browse() {
        global $default, $fCategoryName;
        
        // browsing by category
        $this->setBrowseStart($fCategoryName);
        $sCategoryName = $fCategoryName;
        
        // TODO: add this to default inserts
        $categoryField = "Category";
        $results = array();
        $sql = $default->db;

        // lookup document_fields id for category
        $categoryFieldID = lookupID($default->owl_fields_table, "name", "$categoryField");
        $default->log->debug("CategoryBrowser::browse() categoryFieldID=$categoryFieldID");

        if ($sCategoryName == "") {
            $default->log->debug("CategoryBrowser::browse() no category supplied, returning list");
            // no category value supplied, so return a list of categories
            // set the first value to "categories"
            $results["categories"][] = "Categories";
           
            // get a list of category values
            $query = "SELECT DISTINCT value FROM $default->owl_document_fields_table WHERE document_field_id=$categoryFieldID ORDER BY value ASC";
            $default->log->debug("CategoryBrowser::browse() category listing query=$query");
            $sql->query($query);
            // loop through resultset, build array and return
            while ($sql->next_record()) {
                $results["categories"][] = $sql->f("value");
            }
            // its ok if we return an empty array- the UI's responsibility to check and print an error
            return $results;
        } else {
            $results["categories"][] = $sCategoryName;
            
            // we have a category to use, so find all the documents
            // with this category value
            $query = "SELECT df.document_id FROM $default->owl_document_fields_table df " .
                     "INNER JOIN $default->owl_documents_table d ON df.document_id = d.id ";
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$query .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$this->sSortField=lt.id ";
            }                    
			$query .= "WHERE df.document_field_id = $categoryFieldID AND value='$sCategoryName' ";
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$query .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $this->sSortDirection";
            } else {
            	$query .= "ORDER BY d.$this->sSortField $this->sSortDirection";
            }                    
                     
            $sql->query($query);
            // loop through resultset and add to array
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                // check permissions
                if (Permission::userHasDocumentReadPermission($sql->f("document_id"))) {
                	$oDocument = & Document::get($sql->f("document_id"));
                    if ($oDocument->getStatusID() == lookupStatusID("Live")) {
                    	$results["documents"][] = $oDocument;
                    } 
                } else {
                    $results["accessDenied"] = true;
                }
             }
            
            return $results;
        }
    }
    
    function getSectionName() {
    	return "Manage Categories";
    }    
}
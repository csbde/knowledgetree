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
class DocumentTypeBrowser extends Browser {    

	/**
	 * Construct a new FolderBrowser instance with the specified sort criteria
	 *
	 * @param string the field to sort the results by
	 * @param string the direction to sort the results
	 */
	function DocumentTypeBrowser($sSortField = "name", $sSortDirection = "asc", $aNewSortCriteria = array()) {
		Browser::Browser($sSortField, $sSortDirection, $aNewSortCriteria);
	}
	
    /**
     * Browse the documents by document type
     *
     * @return array document types and documents     
     */    
    function browse() {
        global $default, $fDocumentTypeID;

        // browsing by document type id
        $this->setBrowseStart($fDocumentTypeID);
        $iDocumentTypeID = $fDocumentTypeID;
        
        $results = array();
        $sql = $default->db;
        
        if ($iDocumentTypeID == "") {
            // no document type id supplied, so return a list of document types
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
            $documentTypeName = lookupField($default->owl_document_types_table, "name", "id", $iDocumentTypeID);
            $results["documentTypes"][] = array("id" => $iDocumentTypeID, "name" => $documentTypeName);
                       
            // create query to retrieve documents with this document type
            $documentQuery  = "SELECT d.id as id FROM $default->owl_documents_table d ";
            if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "INNER JOIN " .  $this->aSortCriteria[$sSortField]["lookup"]["lookupTable"] . " lt ON d.$this->sSortField=lt.id ";
            }
            $documentQuery .= "WHERE document_type_id=$iDocumentTypeID ";
			if ( isset($this->aSortCriteria[$sSortField]["lookup"]) ) {
            	$documentQuery .= "ORDER BY lt." . $this->aSortCriteria[$sSortField]["lookup"]["lookupField"] . " $this->sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $this->sSortField $this->sSortDirection";
            }
                        
            // loop through resultset and populate array with document objects
            $sql->query($documentQuery);
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                // check permission
                if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
                	$oDocument = & Document::get($sql->f("id"));
                    if ($oDocument->isLive()) {
                    	$results["documents"][] = $oDocument;
                    }
                } else {
                    $results["accessDenied"] = true;
                }
            }
            $default->log->debug("DocumentTypeBrowser::browse() results=" . arrayToString($results));
            
            return $results;
        }
    }
    
    function getSectionName() {
    	return "Manage Document Types";
    }    
}
<?php
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
/**
 * $Id$
 *
 * Contains document type view document browsing business logic.
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
        global $default;

        // XXX: Shouldn't be getting this from $_REQUEST
        $iDocumentTypeID = $_REQUEST['fDocumentTypeID'];

        // browsing by document type id
        $this->setBrowseStart($iDocumentTypeID);
        $iDocumentTypeID = $iDocumentTypeID;
        
        $results = array();
        $sql = $default->db;
        
        if ($iDocumentTypeID == "") {
            // no document type id supplied, so return a list of document types
            // set the first value to "Document Types"
            $results["documentTypes"][] = array("name" => "Document Types");
            
            // return a list of document types
            $query = "SELECT * FROM $default->document_types_table ORDER BY name " . ($this->sSortField == "name" ? $this->sSortDirection : "ASC");
             
            $sql->query($query);
            while ($sql->next_record()) {
                $results["documentTypes"][] = array ("id" => $sql->f("id"), "name" => $sql->f("name"));
            }
            return $results;
        } else {
        	$aLookupCriteria = $this->aSortCriteria[$this->sSortField]["lookup"];
        	
            // lookup document type name from the passed in id
            $documentTypeName = lookupField($default->document_types_table, "name", "id", $iDocumentTypeID);
            $results["documentTypes"][] = array("id" => $iDocumentTypeID, "name" => $documentTypeName);
                       
            // create query to retrieve documents with this document type
            $documentQuery  = "SELECT d.id as id FROM $default->documents_table d ";
            if ( isset($aLookupCriteria) ) {
            	//$documentQuery .= "INNER JOIN " .  $aLookupCriteria["table"] . " lt ON d.$this->sSortField=lt.id ";
            	$documentQuery .= "INNER JOIN " .  $aLookupCriteria["table"] . " lt ON ";
            	$documentQuery .= "d.$this->sSortField" . "=lt." . (isset($aLookupCriteria["joinColumn"]) ? $aLookupCriteria["joinColumn"] : "id");
            	
            }
            //$documentQuery .= "WHERE document_type_id=$iDocumentTypeID ";
            $documentQuery .= " WHERE document_type_id=$iDocumentTypeID " . (isset($aLookupCriteria["whereClause"]) ? "AND lt." . $aLookupCriteria["whereClause"] : "") . " ";           
			if ( isset($aLookupCriteria) ) {
            	$documentQuery .= "ORDER BY lt." . $aLookupCriteria["field"] . " $this->sSortDirection";
            } else {
            	$documentQuery .= "ORDER BY $this->sSortField $this->sSortDirection";
            }
            
            $default->log->debug("docTypeQuery=$documentQuery");            
                        
            // loop through resultset and populate array with document objects
            $sql->query($documentQuery);
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                $oDocument = & Document::get($sql->f("id"));
                // proceed if the document is live                
                if ($oDocument->isLive()) {                
                    if (Permission::userHasDocumentReadPermission($oDocument)) {
                      	$results["documents"][] = $oDocument;
                    } else {
                        $results["accessDenied"] = true;
                    }
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

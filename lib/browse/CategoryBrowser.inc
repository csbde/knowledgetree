<?php

require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
/**
 * $Id$
 *
 * Contains category view document browsing business logic.
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
        $categoryFieldID = lookupID($default->document_fields_table, "name", "$categoryField");
        $default->log->debug("CategoryBrowser::browse() categoryFieldID=$categoryFieldID");

        if ($sCategoryName == "") {
            if ($categoryFieldID) {            
	            // no category value supplied, so return a list of categories
	            // set the first value to "categories"
	            $results["categories"][] = "Categories";
	            // get a list of category values
	            $query = "SELECT DISTINCT value FROM $default->document_fields_link_table WHERE document_field_id=$categoryFieldID ORDER BY value " . ($this->sSortField == "name" ? $this->sSortDirection : "ASC");
	            $default->log->info("CategoryBrowser::browse() category listing query=$query; $this->sSortField");
	            $sql->query($query);
	            // loop through resultset, build array and return
	            while ($sql->next_record()) {
	                $results["categories"][] = $sql->f("value");
	            }
            }
            // its ok if we return an empty array- the UI's responsibility to check and print an error
            return $results;
        } else {
			$aLookupCriteria = $this->aSortCriteria[$this->sSortField]["lookup"];
			        	
            $results["categories"][] = $sCategoryName;
            
            // we have a category to use, so find all the documents
            // with this category value
            $categoryQuery = "SELECT df.document_id FROM $default->document_fields_link_table df " .
                     "INNER JOIN $default->documents_table d ON df.document_id = d.id ";
            if ( isset($aLookupCriteria) ) {
            	$categoryQuery .= "INNER JOIN " .  $aLookupCriteria["table"] . " lt ON ";
            	$categoryQuery .= "d.$this->sSortField" . "=lt." . (isset($aLookupCriteria["joinColumn"]) ? $aLookupCriteria["joinColumn"] : "id");
            	
            }                    
			$categoryQuery .= " WHERE df.document_field_id = $categoryFieldID AND value='$sCategoryName' " . (isset($aLookupCriteria["whereClause"]) ? "AND lt." . $aLookupCriteria["whereClause"] : "") . " ";;
			if ( isset($aLookupCriteria) ) {
            	$categoryQuery .= "ORDER BY lt." . $aLookupCriteria["field"] . " $this->sSortDirection";
            } else {
            	$categoryQuery .= "ORDER BY d.$this->sSortField $this->sSortDirection";
            }                    
              
            $default->log->debug("categoryQuery=$categoryQuery");                                        
                     
            $sql->query($categoryQuery);
            // loop through resultset and add to array
            $results["accessDenied"] = false;
            while ($sql->next_record()) {
                // check permissions
                $oDocument = & Document::get($sql->f("document_id"));
                
                if (Permission::userHasDocumentReadPermission($oDocument)) {
                	if ($oDocument->isLive()) {
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
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
 * @package lib.browse
 */
class Browser {

    /**
     * The point we're browsing from
     */
    var $sBrowseStart;
    /**
     * The criteria to sort the browse results by- maps the database field to display name and lookup table
     */
    var $aSortCriteria;
    
    /**
     * The field to sort the browse results by
     */
    var $sSortField;
    
    /**
     * The direction to sort the results in
     */
    var $sSortDirection;
    
    /**
     * Constructs a new Browser instance
     *
     * @param string the field to sort by
     * @param string the direction to sort in
     * @param array the sort criteria
     */
    function Browser($sNewSortField, $sNewSortDirection, $aNewSortCriteria = array()) {
    	// default sort criteria
    	if (count($aNewSortCriteria) == 0) {
	    	$aNewSortCriteria = array ( "name"       		=> array ("display" => "Title"), 	
		 					  	 	    "filename"   		=> array ("display" => "Filename"),
								 	 	"creator_id" 		=> array ("display" => "Creator", 
								 	 						      	  "lookup"  => array ("table" => "users",
								 	 										   		  	  "field" => "name")),
								 	 										   		  	  
									 	"id"                => array ("display" => "Date Created",
									 			 					  "lookup"  => array("table" => "document_transactions",
									 								 					 "field" => "datetime",
									 								 					 "joinColumn" => "document_id",
									 								 					 "whereClause" => "transaction_id=1")),
									 	"document_type_id"  => array ("display" => "Document Type",
								 	 						      	  "lookup"  => array ("table" => "document_types_lookup",
								 	 										   		  	  "field" => "name")) );
    	}
							 	 										   		      	
    	$this->aSortCriteria = $aNewSortCriteria;
    	$this->sSortField = $sNewSortField;
    	$this->sSortDirection = $sNewSortDirection;
    }
    
	/**
	 * Returns the browse start point
	 */					
	function getBrowseStart() {
		return $this->$sBrowseStart;
	}

	/**
	 * Set the browse start point
	 */
	function setBrowseStart($aNewBrowseStart) {
		$this->$sBrowseStart = $aNewBrowseStart;
	}
      
	/**
	 * Returns the document sort criteria
	 */					
	function getSortCriteria() {
		return $this->aSortCriteria;
	}

	/**
	 * Set the document sort criteria
	 */
	function setSortCriteria($aNewSortCriteria) {
		$this->aSortCriteria = $aNewSortCriteria;
	}
	    
    /**
     * Return the current sort field
     */
    function getSortField() {
    	return $this->sSortField;
    }
     
    /**
     * Set the current sort field
     */
    function setSortField($sNewSortField) {
    	$this->sSortField = $sNewSortField;
    }
          
    /**
     * Return the current sort direction
     */
    function getSortDirection() {
    	return $this->sSortDirection;
    }      	 						
     
    /**
     * Set the current sort direction
     */     				   		 
	function setSortDirection($sNewSortDirection) {
		$this->sSortDirection = $sNewSortDirection;
	}
	 
    /**
     * [Abstract] Browse the documents
     *
     * @return array documentCollections and documents
     */
    function browse() {
    }

}
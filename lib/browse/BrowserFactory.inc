<?php

require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/browse/FolderBrowser.inc");
require_once("$default->fileSystemRoot/lib/browse/CategoryBrowser.inc");
require_once("$default->fileSystemRoot/lib/browse/DocumentTypeBrowser.inc");

/**
 * 
 * $Id$
 * 
 * Responsible for generating the appropriate browser class instance.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.browse
 */
class BrowserFactory {    

    /**
     * Creates and returns the appropriate browser class
     *
     * @param string what we're browsing by
     * @param string the field to sort by
     * @param string the direction to sort in    
     * @return Browser the correct Browser implementation class
     */
    function create($sBrowseBy, $sSortField, $sSortDirection) {
    	
	    switch ($sBrowseBy) {
	        case "folder" : // retrieve folderID if present
	        				return new FolderBrowser($sSortField, $sSortDirection);
	        				
	                        break;
	                        
	        case "category" :
							return new CategoryBrowser($sSortField, $sSortDirection);
							break;
	                        
	        case "documentType" :
							return new DocumentTypeBrowser($sSortField, $sSortDirection);
							break;
			default :
							return false;
	    }    	
    }

}
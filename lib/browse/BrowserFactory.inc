<?php

require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/browse/FolderBrowser.inc");
require_once("$default->fileSystemRoot/lib/browse/CategoryBrowser.inc");
require_once("$default->fileSystemRoot/lib/browse/DocumentTypeBrowser.inc");
/**
 * $Id$
 *
 * Responsible for generating the appropriate browser class instance.
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
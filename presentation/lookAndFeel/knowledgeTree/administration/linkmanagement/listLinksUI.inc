<?php
/**
 * $Id$
 *
 * List quicklinks UI functions.
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
 * @author Omar Rahbeeni, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.linkmanagement
 */

function getQuickLinks() {
	global $default;
	$sQuery = 	"SELECT id as LinkID, name as LinkName, url LinkURL, rank as LinkRank, " . 
				"'Edit', 'Delete' " .
				"FROM " . $default->quicklinks_table . " " .
				"ORDER BY name";
	
    $aColumns = array("LinkName", "LinkURL", "LinkRank", "Edit", "Delete");
    $aColumnNames = array("Link Name","URL", "Rank", "Edit", "Delete");
    $aColumnTypes = array(1,1,1,3,3);
    $aDBColumnArray = array("LinkID");
    $aQueryStringVariableNames = array("fLinkID");
    	    
    $aHyperLinkURL = array(	3=> "$default->rootUrl/control.php?action=editLink",                       			
                     			4=> "$default->rootUrl/control.php?action=removeLink");
                   			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("QuickLinks Management");

	// add user link
	$sToRender .= getAddLink("addLink", "Add A QuickLink");
	$sToRender .= getQuickLinks();
	return $sToRender;
}
?>
<?php
/**
 * $Id$
 *
 * List document link types UI functions.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

function getLinkTypes() {
	global $default;
	$sQuery = 	"SELECT id as LinkID, name as LinkName, description as LinkDescr, " . 
				"'Edit', 'Delete' " .
				"FROM " . $default->document_link_types_table . " " .
				"ORDER BY name";
	
    $aColumns = array("LinkName", "LinkDescr", "Edit", "Delete");
    $aColumnNames = array("Linktype Name","Description", "Edit", "Delete");
    $aColumnTypes = array(1,1,3,3);
    $aDBColumnArray = array("LinkID");
    $aQueryStringVariableNames = array("fLinkID");
    	    
    $aHyperLinkDescr = array(	2=> "$default->rootUrl/control.php?action=editLinkType",                       			
                     			3=> "$default->rootUrl/control.php?action=removeLinkType");
                   			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkDescr,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading(_("Link Types Management"));

	// add user link
	$sToRender .= getAddLink("addLinkType", "Add A Link Type");
	$sToRender .= getLinkTypes();
	return $sToRender;
}
?>

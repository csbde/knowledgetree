<?php
/**
 * $Id$
 *
 * List websites UI functions.
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
 * @package administration.websitemanagement
 */
 
function getWebsites() {
	global $default;
	$sQuery = 	"SELECT id as WebsiteID, web_site_name as WebsiteName, web_site_url WebsiteURL, web_master_id as WebmasterID, " . 
				"'Edit', 'Delete' " .
				"FROM " . $default->web_sites_table . " " .
				"ORDER BY web_site_name";
	
    $aColumns = array("WebsiteName", "WebsiteURL", "Edit", "Delete");
    $aColumnNames = array(_("Link Name"), _("URL"), _("Edit"), _("Delete"));
    $aColumnTypes = array(1,1,3,3);
    $aDBColumnArray = array("WebsiteID");
    $aQueryStringVariableNames = array("fWebSiteID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editWebSite",                       			
                     		3=> "$default->rootUrl/control.php?action=removeWebSite");
                   			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);	

    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading(_("Website Management"));

	// add user link
	$sToRender .= getAddLink("addWebsite", "Add A Website");
	$sToRender .= getWebsites();
	return $sToRender;
}
?>

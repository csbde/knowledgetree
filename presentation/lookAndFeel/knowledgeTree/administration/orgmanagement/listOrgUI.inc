<?php
/**
 * $Id$
 *
 * List organisations UI functions.
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
 * @package administration.orgmanagement
 */

function getOrganisations() {
	global $default;
	/*ok*/ $sQuery = "SELECT org.id as orgID, org.name as name, " . 
				//"'Edit', 'Delete' " .
				"'Edit'" .
				"FROM organisations_lookup as org " .
				"ORDER BY org.name";
    //$aColumns = array("name", "Edit", "Delete");
    $aColumns = array("name", "Edit");
    //$aColumnNames = array(_("Name"), _("Edit"), _("Delete"));
    $aColumnNames = array(_("Name"), _("Edit"));
    //$aColumnTypes = array(1,3,3);
    $aColumnTypes = array(1,3);    
    $aDBColumnArray = array("orgID");
    $aQueryStringVariableNames = array("fOrgID");
    $aHyperLinkURL = array(	1=> "$default->rootUrl/control.php?action=editOrg");                       			
                     		//2=> "$default->rootUrl/control.php?action=removeOrg");
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading(_("Organisation Management"));

	// add user link
	//$sToRender .= getAddLink("addOrg", "Add An Organisation");
	$sToRender .= getOrganisations();
	return $sToRender;
}
?>

<?php
/**
 * $Id$
 *
 * List units UI functions.
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
 * @package administration.unitmanagement
 */
function getOrganisationDisplay($fOrganisationID) {
	global $default;
	$oPatternListBox = & new PatternListBox($default->organisations_table, "name", "id", "fOrganisationID");
	$oPatternListBox->setPostBackOnChange(true);
	if ($fOrganisationID != 0) {
		$oPatternListBox->setSelectedValue($fOrganisationID);
	}
	return "<table><tr><td><b>" . _("Filter By Organisation") . " </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";	        
}

function getUnits($fOrganisationID) {
	global $default;
	
	$sQuery = 	"SELECT units_lookup.id as unitID, units_lookup.name as name, " . 
				"'Edit', 'Delete', 'Edit Organisations', " .
				"CASE  WHEN organisations_lookup.name Is Null THEN '<font color=darkgrey>* No Organisation</font>' ELSE  organisations_lookup.name END AS OrgName " .
				"FROM (units_lookup " .
				"LEFT JOIN units_organisations_link ON units_lookup.id = units_organisations_link.unit_id) " . 
				"LEFT JOIN organisations_lookup ON units_organisations_link.organisation_id = organisations_lookup.id " .
				($fOrganisationID ? "WHERE units_organisations_link.organisation_id = $fOrganisationID " : "") .					
				"ORDER BY units_lookup.name";
		
    $aColumns = array("name", "OrgName", "Edit", "Delete");//, "Edit Organisations");
    $aColumnNames = array(_("Unit Name"), _("Organisation"), _("Edit"), _("Delete"));//, "Edit Organisations");
    $aColumnTypes = array(1,1,3,3);//,3);
    $aDBColumnArray = array("unitID");
    $aQueryStringVariableNames = array("fUnitID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editUnit",                       			
                     			3=> "$default->rootUrl/control.php?action=removeUnit");
                     			//4=> "$default->rootUrl/control.php?action=editUnitOrg"); 
	    	    
	$oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage($fOrganisationID) {
	global $default;
	$sToRender .= renderHeading(_("Unit Management"));

	$sToRender .= getAddLink("addUnit", _("Add A Unit"));
	//$sToRender .= getOrganisationDisplay($fOrganisationID);
	$sToRender .= getUnits($fOrganisationID);
	return $sToRender;
}
?>

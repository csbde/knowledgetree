<?php
/**
 * $Id$
 *
 * List groups UI functions.
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
 * @package administration.groupmanagement
 */

// display the listbox initially ..then just display the text
function getUnitDisplay($iUnitID) {
    global $default;
    
    // #3519 only allow unit filters for system administrators
    // unit administrators only see their units.
    if (Permission::userIsSystemAdministrator()) {
	    $oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
		$oPatternListBox->setPostBackOnChange(true);    
		if ($iUnitID != 0) {
			$oPatternListBox->setSelectedValue($iUnitID);
		}    
	    return "<table><tr><td><b>" . _("Filter By Unit") . " </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";
    }
}

function getGroups($aUnitIDs) {
	global $default;
	$sQuery = 	"SELECT GL.id AS groupID, UL.name AS UnitNameB4, GL.name AS name, 'Edit' , 'Delete', 'Edit Unit', " .
				"CASE WHEN UL.name IS NULL THEN '<font color=darkgrey>No Unit Assigned</font>' ELSE UL.name END AS UnitName " . 
				"FROM ($default->groups_table GL LEFT JOIN $default->groups_units_table GUL ON GL.id = GUL.group_id) " . 
				"LEFT JOIN $default->units_table UL ON UL.id = GUL.unit_id ";
				
	// #2978 don't display system admin groups if you're not a sys admin
	// filter by unit
    $sUnitIDs = implode(",", $aUnitIDs);
	if ($sUnitIDs <> "") {
		$sWhereClause = "WHERE GUL.unit_id IN (" . $sUnitIDs . ") ";
	}
	// #2978 don't display sys admin groups if you're not a sysadmin	
	if (!Permission::userIsSystemAdministrator()) {
		$sRestrictGroups = " GL.is_sys_admin = 0 ";
		if (strlen($sWhereClause) > 0) {
			$sWhereClause .= " AND $sRestrictGroups";
		} else {
			$sWhereClause = "WHERE $sRestrictGroups";
		}
	}

	$sQuery .= $sWhereClause . "ORDER BY GL.name";
	$default->log->info("groupQuery: $sQuery");	
				
    $aColumns = array("name", "UnitName", "Edit", "Delete", "Edit Unit");
    $aColumnNames = array(_("Name"), _("Unit Name"), _("Edit"), _("Delete"), _("Edit Unit"));
    $aColumnTypes = array(1,1,3,3,3);
    $aDBColumnArray = array("groupID");
    $aQueryStringVariableNames = array("fGroupID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editGroup",                       			
                     		3=> "$default->rootUrl/control.php?action=removeGroup",
                     		4=> "$default->rootUrl/control.php?action=editGroupUnit"); 
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);	
    return $oSearchResults->render() ;	
}

function getPage($aUnitIDs) {
	global $default;
	$sToRender .= renderHeading(_("Group Management"));

	$sToRender .= getAddLink("addGroup", _("Add A Group"));
	$sToRender .= getUnitDisplay($aUnitIDs[0]);
	$sToRender .= getGroups($aUnitIDs);
	return $sToRender;
}
?>

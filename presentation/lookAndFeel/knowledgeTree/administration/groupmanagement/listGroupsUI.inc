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
    
    if (Permission::userIsUnitAdministrator()) {
        $iUnitID = User::getUnitID($_SESSION["userID"]);
    }
    $oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
	$oPatternListBox->setPostBackOnChange(true);    
	if ($iUnitID != 0) {
		$oPatternListBox->setSelectedValue($iUnitID);
	}    
    return "<table><tr><td><b>Filter By Unit </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";
}

function getGroups($fUnitID) {
	global $default;
	$sQuery = 	"SELECT groups_lookup.id as groupID, units_lookup.name as UnitNameB4, groups_lookup.name as name, 'Edit' , 'Delete', 'Edit Unit', " .
				"CASE  WHEN units_lookup.name Is Null THEN '<font color=darkgrey>No Unit Assigned</font>' ELSE  units_lookup.name END AS UnitName " . 
				"FROM (groups_lookup LEFT join groups_units_link on groups_lookup.id = groups_units_link.group_id) " . 
				"LEft join units_lookup on units_lookup.id = groups_units_link.unit_id " .
				($fUnitID ? "WHERE groups_units_link.unit_id =$fUnitID " : "") .
				"ORDER BY groups_lookup.name ";
			
    $aColumns = array("name", "UnitName", "Edit", "Delete", "Edit Unit");
    $aColumnNames = array( "Name", "Unit Name", "Edit", "Delete", "Edit Unit");
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

function getPage($fUnitID) {
	global $default;
	$sToRender .= renderHeading("Group Management");

	$sToRender .= getAddLink("addGroup", "Add A Group");
	$sToRender .= getUnitDisplay($fUnitID);
	$sToRender .= getGroups($fUnitID);
	return $sToRender;
}
?>
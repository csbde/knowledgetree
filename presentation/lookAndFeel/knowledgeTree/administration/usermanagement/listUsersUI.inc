<?php
/**
 * $Id$
 *
 * List users UI functions.
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
 * @package administration.usermanagement
 */
function getGroupDisplay($iGroupID) {
	global $default;
	if (Permission::userIsSystemAdministrator()) {
	    // if this is the system administrator, prepend group names with unit name
	    $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
	    $oPatternListBox->setFromClause("LEFT OUTER JOIN groups_units_link GUL on ST.id=GUL.group_id " .
	                                    "LEFT OUTER JOIN units_lookup UL on GUL.unit_id=UL.id");
	    $oPatternListBox->setCompositeDisplayName("DISTINCT COALESCE(CONCAT(CONCAT(UL.name, '-'),ST.name),ST.name)");
	} else if (Permission::userIsUnitAdministrator()) {
	    // else if this is a unit administrator, only display the groups in your unit
	    $oPatternListBox = & new PatternListBox($default->groups_table, "name", "id", "fGroupID");
	    $oPatternListBox->setFromClause("INNER JOIN $default->groups_units_table GUL on ST.id=GUL.group_id");
        $oPatternListBox->setWhereClause("GUL.unit_id IN (" . implode(",", User::getUnitIDs($_SESSION["userID"])) . ")");
	    $oPatternListBox->setIncludeDefaultValue(false);
	}
	$oPatternListBox->setPostBackOnChange(true);
	if ($iGroupID != 0) {
		$oPatternListBox->setSelectedValue($iGroupID);
	}
	        
	return "<table><tr><td><b>" . _("Filter By Group") . " </b></td><td>" . $oPatternListBox->render() . "</td></tr></table>";	        
}

function getNameDisplay($sName) {
       return "<table><tr><td><b>" . _("Filter By Name") . " </b></td><td><input type=\"text\" size=\"20\" name=\"fName\" value=\"$sName\" /> <input type=\"button\" value=\"Go\" onCLick=\"document.MainForm.submit()\"></td></tr></table>";
}

function getUsers($fGroupID, $sName) {
	global $default;
	// changed from inner to outer joins to include users that aren't in any groups (Stefano Ciancio [s.ciancio@pisa.iol.it]) 
	$sQuery = 	"SELECT DISTINCT U.id as userID, U.name as name, U.username, " . 
				"'Edit', 'Delete', 'Edit Groups' " .
				"FROM $default->users_table U " . 
				"LEFT OUTER JOIN $default->users_groups_table UGL ON U.id = UGL.user_id " .
				"LEFT OUTER JOIN $default->groups_table GL ON UGL.group_id = GL.id ";
	// filter by group				
	if ($fGroupID) {
		$sWhereClause = "WHERE UGL.group_id = $fGroupID ";
	}
	// filter by name				
	if ($sName) {
		$sWhereClause = "WHERE U.name like '%$sName%' ";
	}
	// #2978 don't display sys admin accounts if you're not a sysadmin	
	if (!Permission::userIsSystemAdministrator()) {
		$sRestrictUsers = " GL.is_sys_admin = 0 ";
		if (strlen($sWhereClause) > 0) {
			$sWhereClause .= " AND $sRestrictUsers";
		} else {
			$sWhereClause = "WHERE $sRestrictUsers";
		}
	}
	$sQuery .= $sWhereClause . "ORDER BY U.username";
	
    $aColumns = array("name", "username",  _("Edit"), _("Delete"), _("Edit Groups"));
    $aColumnNames = array(_("Name"), _("Username"), _("Edit"), _("Delete"), _("Edit Groups"));
    $aColumnTypes = array(1,1,3,3,3);
    $aDBColumnArray = array("userID");
    $aQueryStringVariableNames = array("fUserID");
    	    
    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editUser",                       			
                     		3=> "$default->rootUrl/control.php?action=removeUser",
                     		4=> "$default->rootUrl/control.php?action=editUserGroups");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage($fGroupID, $sName) {
	global $default;
	$sToRender .= renderHeading(_("User Management"));

	// add user link if you're a sysadmin
	if (Permission::userIsSystemAdministrator()) {
		$sToRender .= getAddLink("addUser", _("Add A User"));
	}
	$sToRender .= getGroupDisplay($fGroupID);
	$sToRender .= getNameDisplay($sName);
	$sToRender .= getUsers($fGroupID, $sName);
	return $sToRender;
}
?>

<?php
/**
 * $Id$
 *
 * List roles UI functions.
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
 * @package administration.rolemanagement
 */
function getRoles() {
	global $default;
	$sQuery = 	"SELECT id as roleID, name as name, can_read as reader, can_write as writer, " . 
				"'Edit', 'Delete' " .
				"FROM roles " .
				"ORDER BY name";				
	
    $aColumns = array("name", "reader", "writer", "Edit", "Delete");
    $aColumnNames = array(_("Name"), _("Read"), _("Write"), _("Edit"), _("Delete"));
    $aColumnTypes = array(1,2,2,3,3);
    $aDBColumnArray = array("roleID");
    $aQueryStringVariableNames = array("fRoleID");
    	    
    $aHyperLinkURL = array(	3=> "$default->rootUrl/control.php?action=editRole",                       			
                     		4=> "$default->rootUrl/control.php?action=removeRole"); //"$default->rootUrl/control.php?action=removeUserFromGroup");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);	

    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading(_("Role Management"));

	// add user link
	$sToRender .= getAddLink("addRole", _("Add A Role"));
	$sToRender .= getRoles();
	return $sToRender;
}
?>

<?php
/**
 * $Id$
 *
 * Add a document field UI functions.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.groupmanagement
 */

/**
 * Builds the group addition form
 */
function getPage() {
	global $default;
	$sToRender .= renderHeading("Add A New Group");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Group Name") . ":</td>\n";
    $sToRender .= "<td><input size = \"40\" type=\"text\" name=\"fGroupName\" /></td>\n";    
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr/>\n";
    $sToRender .= "<tr><td><b>" . _("Unit") . "</b></td><td>" . getUnitListBox() . "</td></tr>\n";    
	$sToRender .= "<td align=\"right\"><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/>";
	$sToRender .= getCancelButton("listGroups") . "</td>\n";	
	$sToRender .= "</table>\n";	
	return $sToRender;
}

/**
 * Builds the unit selection list box
 */
function getUnitListBox() {
    global $default;
    if (Permission::userIsSystemAdministrator()) {
        // if this is the system administrator, show all the units
        $oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
    } else if (Permission::userIsUnitAdministrator()) {
        // else if this is a unit administrator, only display the units you're an administrator for
        $oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
        $oPatternListBox->setWhereClause("ST.id in (" .  implode(",", User::getUnitIDs($_SESSION["userID"])) . ")");
    }

    // javascript validation
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";
    $sToRender .= "\tif (!(validRequired(theForm.fUnitID, 'Initial Unit'))) {\n";
    $sToRender .= "\t\treturn false;\n\t}\n";    
    $sToRender .= "return true;\n}\n";
    $sToRender .= "//-->\n</script>\n\n";    
    return $oPatternListBox->render() . $sToRender;  
}
?>

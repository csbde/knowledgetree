<?php
/**
 * $Id$
 *
 * Edit unit UI functions.
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
 * @package administration.unitmanagement
 */
// if its the manual edit page..then display normally
function getEditPage($iUnitID) {	
	global $default;
	$oUnit = null;
	if (isset($iUnitID)) {
		$oUnit = Unit::get($iUnitID);
    }
	
	$sToRender .= renderHeading("Edit Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><strong>" . _("Please edit the Unit name") . "</strong></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Unit Name") . ": </td><td>" . getUnitDisplay($oUnit) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td colspan=\"2\" align = right>" . getUpdateButton($oUnit) . getCancelButton($oUnit) . "</td></tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= renderHeading("Edit Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Unit was successfully modified") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
	$sToRender .= renderHeading("Edit Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . strtoupper(_("Failure")) . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("Unit modification Failed!") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

// show the list box when no unit selected
// then shows a text box with the unit's name
function getUnitDisplay($oUnit) {
	global $default;
	if (!isset($oUnit)) {
		$oPatternListBox = & new PatternListBox($default->units_table, "name", "id", "fUnitID");
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fUnitID\" value=\"" . $oUnit->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fUnitName\" value=\"" . $oUnit->getName() . "\">";
	}
}


// gets the updatebutton when a unit is selected
function getUpdateButton($oUnit) {
	global $default;
	if (isset($oUnit)) {
		return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/>\n";
	}
}

// gets the cancel button when a unit is selected
function getCancelButton($oUnit) {
	global $default;
	if (!isset($oUnit)) {
		return "<a href=\"$default->rootUrl/control.php?action=unitManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" border=\"0\"/></a>\n";
	} else {
		return "<a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
	}
}
?>

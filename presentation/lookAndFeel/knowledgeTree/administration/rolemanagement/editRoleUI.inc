<?php
/**
 * $Id$
 *
 * Edit a role UI functions.
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
 * @package administration.rolemanagement
 */
// get hte edit page if it comes from the add page
function getCreatePage($iRoleID) {
	
	global $default;
	
	$sQuery =  "SELECT * FROM $default->roles_table WHERE id = $iRoleID";
	$aDisplayColumns = array("name", "can_read", "can_write");
	$aStoreColumns = array("name", "can_read","can_write");
	$aColumnNames = array(_("Name"), _("Read"), _("Write"));
	$aDisplayColumnTypes = array(1,2,2);
	$aDatabaseColumnTypes = array(1,2,2);
	
	// get list of role properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->roles_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes) ;
	$oPattern->setUniqueName("roleEdit");
	$oPattern->setColumnsRequired(array(1,0,0));
		
    $sToRender .= renderHeading("Edit Role");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Update\" border=\"0\"/><a href=\"$default->rootUrl/control.php?action=addRole\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</table>\n";
    
	return $sToRender;
}

// if fails ..
function getAddFailPage() {
	
	global $default;
			
    $sToRender .= renderHeading("Edit Role");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Sorry, Role Already Exists!") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=addRole\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
		
	return $sToRender;
}

// if its the manual edit page..then display normally
function getEditPage($iRoleID) {	
	global $default;
	$oRole = null;
	if (isset($iRoleID)) {
		$oRole = Role::get($iRoleID);
	}
	
    $sToRender .= renderHeading("Edit Role");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Role Name:") . " </td><td>" . getRoleDisplay($oRole) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a Role selected...show checkboxes
	if (isset($iRoleID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>" . _("Readable:") . " </td><td>" . getReadableCheckBox($oRole) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>" . _("Writable:") . " </td><td>" . getWriteableCheckBox($oRole) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "</tr>\n";
	}

	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oRole);	
	$sToRender .= getCancelButton($oRole);	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(theForm.fRoleName, 'Role Name'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
    $sToRender .= renderHeading("Edit Role");
	$sToRender .= "<b>" . _("Success") . "!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Role was successfully modified") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listRoles\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
    $sToRender .= renderHeading("Edit Role");
	$sToRender .= "<b>" . _("Success") . "!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Role modification Failed!") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listRole\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// show the list box when no Role selected
// then shows a text box with the role's name
function getRoleDisplay($oRole) {
	global $default;
	if (!isset($oRole)) {
		$oPatternListBox = & new PatternListBox($default->roles_table, "name", "id", "fRoleID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fRoleID\" value=\"" . $oRole->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fRoleName\" value=\"" . $oRole->getName() . "\">";
	}
	
}

// returns the checkbox ticked or unticked
function getActiveCheckBox($oRole) {
	if (!isset($oRole)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fActive\" " . ($oRole->getActive() ? "CHECKED " : " ") . " />";
	}
}


// returns the checkbox ticked or unticked
function getReadableCheckBox($oRole) {
	if (!isset($oRole)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fReadable\" " . ($oRole->getReadable() ? "CHECKED " : " ") . " />";
	}
}

// returns the checkbox ticked or unticked
function getWriteableCheckBox($oRole) {
	if (!isset($oRole)) {
		return "";
	} else {
		return "<input type=\"checkbox\" name=\"fWriteable\" " . ($oRole->getWriteable() ? "CHECKED " : " ") . " />";
	}
	
}

// gets the updatebutton when a group is selected
function getUpdateButton($oRole) {
	global $default;
	if (!isset($oRole)) {
		return _("Please select a Role:") . " ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// gets the cancel button when a group is selected
function getCancelButton($oRole) {
	global $default;
	if (!isset($oRole)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=groupManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=listRoles\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>

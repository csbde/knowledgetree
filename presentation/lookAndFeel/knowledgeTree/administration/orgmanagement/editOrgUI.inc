<?php
/**
 * $Id$
 *
 * Edit organisation UI functions.
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
 * @package administration.orgmanagement
 */
 
// if its the manual edit page..then display normally
function getEditPage($iOrgID) {	
	global $default;
	$oOrg = null;
	if (isset($iOrgID)) {
		$oOrg = Organisation::get($iOrgID);
	}
	
	$sToRender .= renderHeading(_("Edit Organisation"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>" . _("Please select an Organisation") . ":</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Organisation Name") . ": </td><td>" . getOrgDisplay($oOrg) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oOrg);	
	$sToRender .= getCancelButton($oOrg);
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
	$sToRender .= renderHeading(_("Edit Organisation"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Organisation was successfully modified") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
    $sToRender .= renderHeading(_("Edit Organisation"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . strtoupper(_("Failure")) . "!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("Organisation modification Failed!") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no Org selected
// then shows a text box with the Org's name
function getOrgDisplay($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		$oPatternListBox = & new PatternListBox($default->organisations_table, "name", "id", "fOrgID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fOrgName\" value=\"" . $oOrg->getName() . "\">";
	}
	
}


// gets the updatebutton when a Org is selected
function getUpdateButton($oOrg) {
	global $default;
	if (isset($oOrg)) {
		return "<td align = right><input type=\"image\" src =\"" . KTHtml::getUpdateButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// gets the cancel button when a Org is selected
function getCancelButton($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=orgManagement\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" border=\"0\"/></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
	}
}
?>

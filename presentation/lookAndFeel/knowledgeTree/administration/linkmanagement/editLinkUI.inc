<?php
/**
 * $Id$
 *
 * Edit a quicklink UI functions.
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
 * @package administration.linkmanagement
 */
// get hte edit page if it comes from the add page

// if its the manual edit page..then display normally
function getEditPage($iLinkID) {	
	global $default;
	$oLink = null;
	if (isset($iLinkID)) {
		$oLink = Link::get($iLinkID);
	} else {
    	$sHeading = _("Please select a Link:");
	} 
    $sToRender .= renderHeading("Edit QuickLink");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>$sHeading</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Link Name") . ": </td><td>" . getLinkDisplay($oLink) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a Link selected...show checkboxes
	if (isset($iLinkID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>" . _("URL") . ": </td><td>" . getURL($oLink) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>" . _("Rank") . ": </td><td>" . getRank($oLink) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "</tr>\n";
	}


	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td>" . getUpdateButton($oLink);	
	$sToRender .= getCancelButton($oLink);	
	$sToRender .= "</table>\n";
	
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(theForm.fLinkName, 'Link Name'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	$sToRender .= "\tif (!(validRequired(theForm.fUrl, 'URL'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	$sToRender .= "\tif (!(validRequired(theForm.fRank, 'Rank'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";    
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
    
	return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {	
	global $default;
			
    $sToRender .= renderHeading("Edit QuickLink");
	$sToRender .= "<b>" . _("Success") . "!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Link was successfully modified") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
    $sToRender .= renderHeading("Edit QuickLink");
	$sToRender .= "<b>" . _("Failure") . "!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>" . _("The Link modification Failed!") . "</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no Link selected
// then shows a text box with the Link's name
function getLinkDisplay($oLink) {
	global $default;
	if (!isset($oLink)) {
		$oPatternListBox = & new PatternListBox($default->quicklinks_table, "name", "id", "fLinkID");
		//$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fLinkID\" value=\"" . $oLink->iId . "\">\n" .
		"<input size = \"40\" type=\"text\" name=\"fLinkName\" value=\"" . $oLink->getName() . "\">";
	}
	
}


// returns the checkbox ticked or unticked
function getURL($oLink) {
	if (!isset($oLink)) {
		return "";
	} else {
		return "<input size = \"40\" type=\"text\" name=\"fUrl\" value=\"" . $oLink->getUrl() . "\">";
	}
}

// returns the checkbox ticked or unticked
function getRank($oLink) {
	if (!isset($oLink)) {
		return "";
	} else {
		return "<input size = \"40\" type=\"text\" name=\"fRank\" value=\"" . $oLink->getRank() . "\">";
	}
	
}

// gets the updatebutton when a Link is selected
function getUpdateButton($oLink) {
	global $default;
	if (isset($oLink)) {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/></td>\n";
	}
}

// gets the cancel button when a Link is selected
function getCancelButton($oLink) {
	global $default;
	if (!isset($oLink)) {
		//return "<td align = right><a href=\"$default->rootUrl/control.php?action=LinkManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
	} else {
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=listLinks\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>

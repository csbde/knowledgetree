<?php
/**
 * $Id$
 *
 * UI functions for requesting the creation of a new document that
 * will be linked to an existing one.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getPage($iFolderID, $iDocumentID, $iUnitID, $iUserID, $sDocumentTitle, $sTemplateDocument) {
	global $default;
	$sToRender = renderHeading("Create a new dependant document");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>Document title</td><td><input type=\"text\" name=\"fDocumentTitle\" value=\"$sDocumentTitle\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User's Unit</td><td>" . getUnitDropDown($iDocumentID, $iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . getUserDropDown($iUnitID, $iUserID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";

	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	if (!$default->bNN4) {
		//for any other browser but netscape 4.7 do this		
		$sToRender .= "<td>Template document</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"$sTemplateDocument\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
	} else {
		//for netscape 4.7 do this		
		$sToRender .= "<td>Template document</td><td><input type=\"text\"  name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/done.gif\" onClick='return validateForm(document.MainForm);' border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$iDocumentID&fShowSection=linkedDocuments\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript();
}

function getUserDropDown($iUnitID, $iUserID) {
	global $default;	
	if (!$iUnitID) {
		$iUnitID = -1;		
	}
	$oPatternListBox = & new PatternListBox($default->users_table, "Name", "id", "fUserID", "GUL.unit_id = $iUnitID");
	$sFromClause = "INNER join users_groups_link AS UGL ON UGL.user_id = ST.id " .
					"INNER JOIN groups_units_link AS GUL ON GUL.group_id = UGL.group_id ";
	$oPatternListBox->setFromClause($sFromClause);
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	return $oPatternListBox->render();
	
	
}

function getUnitDropDown($iDocumentID, $iUnitID) {
	global $default;
	if (!$iUnitID) {
		$iUnitID = -1;
	}
	$oPatternListBox = & new PatternListBox($default->units_table, "Name", "id", "fUnitID");	
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID')");
	if (isset($iUnitID)) {
		$oPatternListBox->setSelectedValue($iUnitID);
	}
	return $oPatternListBox->render();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fDocumentTitle,'Document title') && validRequired(theForm.fUserID,'User'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}

function getBrowseJavaScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function newWindow(file,window) {\n ";	
    $sToRender .= "\tmsgWindow=open(file,window,'resizable=yes,scrollbars=yes, width=400,height=600');\n ";
    $sToRender .= "\tif (msgWindow.opener == null) msgWindow.opener = self; \n ";
    $sToRender .= "}\n";
    $sToRender .= "//--></script>\n";	
    return $sToRender;
}
?>
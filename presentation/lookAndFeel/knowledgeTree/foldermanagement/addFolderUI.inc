<?php
/**
 * $Id$
 *
 * Presentation page that provides HTML for adding a new folder (used by addFolderBL.php)
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
 * @package foldermanagement
 */

function renderBrowseAddPage($iFolderID) {
    global $default;
	$sToRender = "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, generateControllerUrl("addFolder", "", false)) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
	$sToRender .= "<td valign=\"top\" width=\"300\">\n";
	$sToRender .= wrapInTable(renderFolderList($iFolderID, generateControllerUrl("addFolder", "", false)));
	$sToRender .= "</td>\n";
	$sToRender .= "<td valign=\"top\">\n";
	$sToRender .= getRightSide($iFolderID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
    return $sToRender;
}

function getRightSide($iFolderID) {
	global $default;
	$sToRender .= "<table border=\"0\">\n";    
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>" . _("Folder Name" ) . ": </b></td>\n";
    $sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>" . _("Document Type") . ": </b></td>\n";
	$sToRender .= "<td>" . getDocumentTypeDropDown() . "</td>\n";
	$sToRender .= "</tr>\n";    
	$sToRender .= "<td><table border=\"0\"><tr><td><input type=\"image\" src=\"" . KTHtml::getAddButton() . "\" border=\"0\"></td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$iFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr></table></td><td>&nbsp</td>\n";    
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(theForm.fFolderName, '" . _("Folder Name") . "'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	$sToRender .= "\tif (!(validRequired(theForm.fDocumentTypeID, '" . _("Document Type") . "'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "//-->\n</script>\n\n";

    return $sToRender;
}

function getDocumentTypeDropDown() {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->document_types_table", "name", "id", "fDocumentTypeID");
	$oPatternListBox->setIncludeDefaultValue(false);
	return $oPatternListBox->render();
}

function renderBrowsePage($iFolderID) {
    global $default;
    $sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, generateControllerUrl("addFolder", "", false)) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderList($iFolderID, generateControllerUrl("addFolder", "", false)) . "<td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table><br>\n";

    return $sToRender;
}

function wrapInTable($sHtml) {
	return "\n\t\t<table border = 0, width = 100%><tr><td>$sHtml</td></tr></table>\n";	
}

?>

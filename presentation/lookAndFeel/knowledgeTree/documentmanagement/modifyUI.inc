<?php
/**
 * $Id$
 *
 * Presentation data used to modify documents (will be used by modifyBL.inc)
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

function renderEditableDocumentData($oDocument, $iDocumentTypeID) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	
	$sToRender;
	if ($oDocument) {
		$sToRender .= "<table>\n";
		$sToRender .= "<caption><strong>Document Data</strong></caption>\n";
		$sToRender .= "<tr>\n";		
		$sToRender .= "<td bgcolor=\"$sTDBGColour\">\n";
		$sToRender .= "<b>Document Title </b>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "<td>\n";
		$sToRender .= "<input type=\"text\" name=\"fDocumentName\" value=\"" . $oDocument->getName() . "\" />\n";
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";	
		$sToRender .= "<td bgcolor=\"$sTDBGColour\">\n";
		$sToRender .= "<b>Document Type </b>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "<td>\n";
		$sToRender .= getDocumentType($oDocument->getFolderID(), $iDocumentTypeID);
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "</table>\n";
		
		return $sToRender;
	}
	return "";
}

function getDocumentType($iFolderID, $iDocumentTypeID) {
	global $default;
	$sWhereClause = "FDL.folder_id = $iFolderID";
	$oPatternListBox = & new PatternListBox("$default->document_types_table", "name", "id", "fDocumentTypeID",$sWhereClause);
	$oPatternListBox->setIncludeDefaultValue(false);
	$oPatternListBox->setFromClause("INNER JOIN $default->folder_doctypes_table AS FDL ON ST.id = FDL.document_type_id");	
	if (isset($iDocumentTypeID)) {
		$oPatternListBox->setSelectedValue($iDocumentTypeID);
	}	
	return $oPatternListBox->render();
}



function renderPage($oDocument, $iDocumentTypeID, $fFirstEdit) {
    global $default;	
	$sToRender = renderHeading("Edit Document Details");
	$sToRender .= displayDocumentPath($oDocument->getID());	
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= renderEditableDocumentData($oDocument, $iDocumentTypeID) . "\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</table>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/><td>\n";	
	if (!isset($fFirstEdit)) {
		//can't this action on a first time upload, you must fill out the necessary fields
		$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a><td>\n";
	}
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
		
    return $sToRender . getValidationJavaScript();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(document.MainForm.fDocumentName, 'Document Title'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "//-->\n</script>\n\n";
	
	return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}?>

<?php
/**
 * $Id$
 *
 * Add a link between a document type and document field UI functions.
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
 * @package administration.doctypemanagement
 */
 
function getOptionPage($iDocTypeID = null){
	global $default;
	
	$sToRender .= renderHeading("Add Type Specific Field to Document Type");
	$sToRender .= "<br>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>" . _("New type specific field?") . "</td><td><a href=\"$default->rootUrl/control.php?action=addDocTypeFieldsLink&fNewField=1&fDocTypeID=$iDocTypeID\"><img src =\"$default->graphicsUrl/widgets/select.gif\" border = \"0\" /></a></td></tr>\n";
	$sToRender .= "<tr><td>" . _("Choose from existing type specific fields") . "</td><td><a href=\"$default->rootUrl/control.php?action=addDocTypeFieldsLink&fFromList=1&fDocTypeID=$iDocTypeID\"><img src =\"$default->graphicsUrl/widgets/select.gif\" border = \"0\" /></a></td></tr>\n";		
	$sToRender .= "<tr><td></td><td>" . getCancelButton($iDocTypeID) . "</td></tr>\n";
	$sToRender .= "</table>\n";
		
	return $sToRender;
}


function genericMessage($sMsg, $iDocTypeID = null){
	global $default;
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>$sMsg</td></tr>\n";
	$sToRender .= "<tr><td></td><td>" . getBackButton($iDocTypeID) . "</td></tr>\n";		
	$sToRender .= "</table>\n";
		
	return $sToRender;
}

function getCreateNewFail($iDocTypeID){	
	$sToRender = genericMessage(_("Error creating new Type Specific Field and Linking it"), $iDocTypeID);
	return $sToRender;
}


function getCreateNewSuccess($iDocTypeID){
	$sToRender = genericMessage(_("New Type specific Field Created and Linked."), $iDocTypeID);
	return $sToRender;	
}

function getFail_NoFieldID($iDocTypeID = null){	
	$sToRender = genericMessage(_("Linking cannot be done. Please select a field to link to."), $iDocTypeID);
	return $sToRender;	
}

function addSuccessPage($iDocTypeID = null){
	$sToRender = genericMessage(_("Field is successfully linked to the Document Type."), $iDocTypeID);
	return $sToRender;	
}

function addFailPage($iDocTypeID = null ){
	$sToRender = genericMessage(_("Error") . ": " . _("Field Could not be linked to the Document Type."), $iDocTypeID);
	return $sToRender;	
}

function getMissingDocTypeIDPage($iDocTypeID = null ){
	$sToRender = genericMessage(_("Error") . ": " . _("Page expects a Document Type ID."), $iDocTypeID);
	return $sToRender;		
}


function getListFailPage($iDocTypeID = null){
	$sToRender = genericMessage(_("There are no non-generic fields in the database."), $iDocTypeID);
	return $sToRender;	
}

// get add pages
function getFirstPage($sHtmlListBox = null, $iDocTypeID, $bIsNew = null) {
	global $default;
		
	$sToRender .= renderHeading(_("Add Type Specific Field to Document Type"));	
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	
	if ($bIsNew) {		
		$sNewTableName = $default->data_types_table;
	    $sNewDisplayColumn = "name";
	    $sNewValueColumn = "name";
	    $sNewSelectName = "fDataType";	    
	    $bNewOrderAsc = true;
	   
	    $oSelectBox = & new PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $sNewWhereClause , $bNewOrderAsc );
		$oSelectBox->setIncludeDefaultValue(false); 
		$sToRender .= "<tr>";
		$sToRender .= "<td>" . _("New non-generic field:") . " </td><td><input type=text name=fNewField></td>";		
		$sToRender .= "</tr>";		
		$sToRender .= "<tr>";
		$sToRender .= "<td>" . _("Field data type:") . " </td><td>" . $oSelectBox->render() . "</td>";		
		$sToRender .= "</tr>";
		$sToRender .= "<tr>";
		$sToRender .= "<td align=right>" . _("Has Lookup? ") . " </td><td><input type=checkbox name=bHasLookup></td>";		
		$sToRender .= "</tr>";		
				
	} else {
		$sToRender .= "<tr>";
		$sToRender .= "<td>" . _("Non-generic fields:") . " </td><td>" . $sHtmlListBox . "</td>";
		$sToRender .= "</tr>";
	}
	
	$sToRender .= "<tr><td align=right>" . _("Mandatory") . "? </td><td><input type=checkbox name=bMandatory> </td></tr>\n";	
	$sToRender .= "<tr><td></td><td align=right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" border=0 value=\"submit\"/></td>";
	 
	//$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td></tr>\n";
	$sToRender .= "<td>" . getCancelButton($iDocTypeID) . "</td></tr>\n";
	
	$sToRender .= "</table>\n";
	
	return $sToRender;	
}

// gets the cancel button
function getCancelButton($iDocTypeID = null) {
    global $default;    
    //return "<a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";    
	return "<a href=\"$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1&fDocTypeID=" . $iDocTypeID . "\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";
}

// gets the cancel button
function getBackButton($iDocTypeID = null) {
    global $default;    
    return "<a href=\"$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1&fDocTypeID=" . $iDocTypeID . "\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>\n";    
}
?>

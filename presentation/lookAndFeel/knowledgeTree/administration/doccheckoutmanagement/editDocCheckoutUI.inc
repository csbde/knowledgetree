<?php
/**
 * $Id$
 *
 * Edit Document CheckOut Status UI Functions.
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
 * @package administration.doccheckoutmanagement
 */
 
function getEditCheckoutSuccessPage(){
	global $default;
	return statusPage("Edit Document Checkout", "Succesfully updated document checkout.", "", "listDocs");
}

function getErrorPage($sMessage) {
	return statusPage("Edit Document Checkout", "Error checking in document", $sMessage, "listDocs");
}	

function getEditCheckoutPage($fDocumentID){
	global $default;
	
	$oDocument = Document::get($fDocumentID);
	$oUser = User::get($oDocument->getCheckedOutUserID());
	$sToRender .= renderHeading("Edit Document Checkout");	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td colspan=\"2\">Are you sure you want to Check In the document:</tr></td>";
	$sToRender .= "<tr><td width=\"20%\">Path</td><td><b>" . $oDocument->getDisplayPath() . "</b></td></tr>";
	$sToRender .= "<tr><td>Checked Out By</td><td><b>" . ($oUser ? $oUser->getName() : "Unknown" ) . "</b></td></tr>";
 	$sToRender .= "<input type=\"hidden\" name=\"fUpdate\" value=\"1\">\n";
 	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td colspan=\"2\" align=right>\n";	
	$sToRender .= getUpdateButton();	
	$sToRender .= getCancelButton();	
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

// gets the updatebutton when a unit is selected
function getUpdateButton() {
	global $default;
	return "<input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" border=\"0\"/>\n";	
}

// gets the cancel button when a unit is selected
function getCancelButton() {
	global $default;
	return "<a href=\"$default->rootUrl/control.php?action=listDocs\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
}

?>
<?php
/**
* Presentation information for adding a link
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
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
	$sToRender .= "<tr><td colspan=\"2\">Are you sure you want to Check In this document:</tr></td>";
	$sToRender .= "<tr><td width=\"20%\">Path</td><td><b>" . $oDocument->getDisplayPath() . "</b></td></tr>";
	 $sToRender .= "<tr><td>Checked Out By</td><td><b>" . $oUser->getName() . "</b></td></tr>";
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
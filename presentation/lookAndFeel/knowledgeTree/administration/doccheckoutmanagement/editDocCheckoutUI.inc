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
	
	$sToRender .= "Succesfully updated document checkout.\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=listDocs\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>";
	
	return $sToRender;
	
}

function getEditCheckoutPage($fDocID){
	global $default;
	
	$oDoc = Document::get($fDocID);
		
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<b>Document Name: </b>" . $oDoc->getName();
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<b>Checked out?: </b>";
	
	if($oDoc->getIsCheckedOut() > 0){
		$bCheckedOut = "checked";	
	} 	
	$sToRender .= "<input type=checkbox $bCheckedOut name=fDocCheckout>";
	
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align=right>\n";	
	$sToRender .= getUpdateButton();	
	$sToRender .= "</td>\n";
	$sToRender .= "<td >\n";	
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
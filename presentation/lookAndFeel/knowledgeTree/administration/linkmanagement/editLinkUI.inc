<?php
/**
* Presentation information for adding a link
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get hte edit page if it comes from the add page

// if its the manual edit page..then display normally
function getEditPage($iLinkID) {	
	global $default;
	$oLink = null;
	if (isset($iLinkID)) {
		$oLink = Link::get($iLinkID);
	} else {
    	$sHeading = "Please select a Link:";
	} 
    $sToRender .= "<table width=\"600\">" . renderHeading("Edit QuickLink") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>$sHeading</td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Link Name: </td><td>" . getLinkDisplay($oLink) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	
	// if a Link selected...show checkboxes
	if (isset($iLinkID)) {
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>URL: </td><td>" . getURL($oLink) . "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>Rank: </td><td>" . getRank($oLink) . "</td>\n";
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
			
    $sToRender .= "<table width=\"600\">" . renderHeading("Edit QuickLink") . "</table>";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Link was successfully modified</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editLink\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {	
	global $default;
			
    $sToRender .= "<table width=\"600\">" . renderHeading("Edit QuickLink") . "</table>";
	$sToRender .= "<b>Success!</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Link modification Failed!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editLink\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// show the list box when no Link selected
// then shows a text box with the Link's name
function getLinkDisplay($oLink) {
	global $default;
	if (!isset($oLink)) {
		$oPatternListBox = & new PatternListBox($default->owl_links_table, "name", "id", "fLinkID");
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
		return "<td align = right><a href=\"$default->rootUrl/control.php?action=editLink\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>
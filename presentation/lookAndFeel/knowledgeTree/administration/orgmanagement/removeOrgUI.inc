<?php

/**
* Presentation information for Deleting a Org 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iOrgID) {	
	global $default;
	$oOrg = null;
	if (isset($iOrgID)) {
		$oOrg = Organisation::get($iOrgID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this Organisation?:</b>\n";
	}
	else{
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove Organisation From System:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Organisation Name: " . getOrgDisplay($oOrg) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getDeleteButton($oOrg);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {	
	
global $default;

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Organisation SuccessFully Removed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td align = right><a href=\"$default_rootUrl/control.php?action=removeOrg\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
$sToRender .= "</tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// get deletion failed page
function getDeleteFailPage() {	

global $default;	

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Deletion Failed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr><td>Please Ensure that ALL Units has been Removed the Organisation it belongs to</td></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "</tr>\n";
$sToRender .= "<tr><td align = right><a href=\"$default_rootUrl/control.php?action=removeOrg\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// show listbox...or the text name
function getOrgDisplay($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		$oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrgID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
		"<b>" . $oOrg->getName() . "</b>";
	}
	
}


// show delete button
function getDeleteButton($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		return "Please select a Org: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/Delete.gif\" value=\"submit\" /></td>\n".
		"<td align = right><a href=\"$default_rootUrl/control.php?action=removeOrg\"><img src =\"$default->graphicsUrl/widgets/Cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>

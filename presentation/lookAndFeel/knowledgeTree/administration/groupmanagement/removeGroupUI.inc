<?php

/**
* Presentation information for Deleting a group 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iGroupID) {	
	global $default;
	$oGroup = null;
	if (isset($iGroupID)) {
		$oGroup = Group::get($iGroupID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this Group?:</b>\n";
	}
	else{
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove Group From System:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group Name: " . getGroupDisplay($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getUnitAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getSysAdminCheckBox($oGroup) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getDeleteButton($oGroup);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {	
	
global $default;

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Group SuccessFully Removed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=removeGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
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
$sToRender .= "<tr><td>Please Ensure that The Group has been Removed from any Unit it belongs to</td></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "</tr>\n";
$sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=removeGroup\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// show listbox...or the text name
function getGroupDisplay($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		$oPatternListBox = & new PatternListBox($default->owl_groups_table, "name", "id", "fGroupID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fGroupID\" value=\"" . $oGroup->iId . "\">\n" .
		"<b>" . $oGroup->getName() . "</b>";
	}
	
}

// show check box
function getUnitAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "Unit Administrator: <input type=\"checkbox\" name=\"fGroupUnitAdmin\" " . ($oGroup->getUnitAdmin() ? "CHECKED " : " ") . " />";
	}
}

// show text box
function getSysAdminCheckBox($oGroup) {
	if (!isset($oGroup)) {
		return "";
	} else {
		return "System Administrator: <input type=\"checkbox\" name=\"fGroupSysAdmin\" " . ($oGroup->getSysAdmin() ? "CHECKED " : " ") . " />";
	}
	
}

// show delete button
function getDeleteButton($oGroup) {
	global $default;
	if (!isset($oGroup)) {
		return "Please select a group: ";
	} else {
		return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" /></td>\n".
		"<td align = right><a href=\"$default->rootUrl/control.php?action=removeGroup\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>

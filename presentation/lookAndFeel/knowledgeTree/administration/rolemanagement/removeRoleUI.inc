<?php

/**
* Presentation information for Deleting a Role 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getDeletePage($iRoleID) {	
	global $default;
	$oRole = null;
	if (isset($iRoleID)) {
		$oRole = Role::get($iRoleID);
		$sToRender .= "<br></br>\n";
		$sToRender .= "<b>Are you sure you wish to Remove this Role?:</b>\n";
	}
	else{
		$sToRender .= "<br></br>\n";		
		$sToRender .= "<b>Remove Role From System:</b>\n";
	}
	
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role Name: </td><td>" . getRoleDisplay($oRole) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .=  getReadableCheckBox($oRole) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= getWriteableCheckBox($oRole) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= getDeleteButton($oRole);	
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {	
	
global $default;

$sToRender  = "<br></br>\n" ;
$sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td><b>Role SuccessFully Removed!</b></td>\n";
$sToRender .= "</tr>\n";	
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr>\n";
$sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=removeRole\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
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
$sToRender .= "<tr><td>Please Ensure that The Role has not been assigned to any Folder</td></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "<tr></tr>\n";
$sToRender .= "</tr>\n";
$sToRender .= "<tr><td align = right><a href=\"$default->rootUrl/control.php?action=removeRole\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td></tr>\n";
$sToRender .= "</table>\n";
		
return $sToRender;
}

// show listbox...or the text name
function getRoleDisplay($oRole) {
	global $default;
	if (!isset($oRole)) {
		$oPatternListBox = & new PatternListBox($default->owl_roles_table, "name", "id", "fRoleID");
		$oPatternListBox->setIncludeDefaultValue(true);
		$oPatternListBox->setPostBackOnChange(true);		
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fRoleID\" value=\"" . $oRole->iId . "\">\n" .
		"<b>" . $oRole->getName() . "</b>";
	}
	
}

// show check box
function getReadableCheckBox($oRole) {
	if (!isset($oRole)) {
		return "";
	} else {
		return "<td>Readable: </td><td><input type=\"checkbox\" name=\"fReadable\" " . ($oRole->getReadable() ? "CHECKED " : " ") . " />";
	}
}

// show text box
function getWriteableCheckBox($oRole) {
	if (!isset($oRole)) {
		return "";
	} else {
		return "<td>Writable: </td><td><input type=\"checkbox\" name=\"fWritable\" " . ($oRole->getWriteable() ? "CHECKED " : " ") . " />";
	}
	
}

// show delete button
function getDeleteButton($oRole) {
	global $default;
	if (!isset($oRole)) {
		return "Please select a Role: ";
	} else {
		return "<td></td><td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\" /></td>\n".
		"<td align = right><a href=\"$default->rootUrl/control.php?action=removeRole\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
	}
}
?>

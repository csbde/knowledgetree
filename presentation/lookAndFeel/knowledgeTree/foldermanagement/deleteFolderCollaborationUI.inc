<?php
/**
* Presentation information for deletion of folder collaboration entry. 
* Will be used by deleteFolderCollaborationBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 6 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getPage($iFolderID, $iGroupID, $iRoleID, $iSequenceNumber) {
	global $default;
	$sToRender = "<table border=\"1\" width=\"100%\"><tr><td>" . getFolderPath($iFolderID) . "</td></tr></table>\n";
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroup($iGroupID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role</td><td>" . getRole($iRoleID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sequence No.</td><td>$iSequenceNumber</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->owl_graphics_url/widgets/delete.gif\" /></td><td><a href=\"$default->owl_root_url/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"$default->owl_graphics_url/widgets/cancel.gif\" border=\"0\" /></a></td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<table>\n";
	
	/*$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(confirmMsg('Are you sure you wish to delete this folder collaboration step?'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";*/
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\t return confirm('Are you sure you wish to delete this folder collaboration step?');}\n";	
	$sToRender .= "//-->\n</script>\n\n";
	
	return $sToRender;
}

function getFolderPath($iFolderID) {
	global $default;
	return displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), "$default->owl_root_url/control.php?action=browse");		
}


function getGroup($iGroupID) {
	$oGroup = Group::get($iGroupID);
	return $oGroup->getName();
}

function getRole($iRoleID) {	
	$oRole = Role::get($iRoleID);
	return $oRole->getName();
}


?>

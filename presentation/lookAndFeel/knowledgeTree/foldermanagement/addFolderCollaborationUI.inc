<?php
/**
* Presentation information for adding a new step to the folder collaboration flow
* Used by addFolderCollaborationBL.php
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getPage($iFolderID, $iGroupID, $iRoleID, $iSequenceNumber) {
	global $default;
	$sToRender = "<table border=\"1\" width=\"100%\"><tr><td>" . getFolderPath($iFolderID) . "</td></tr></table>\n";
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroupDropDown($iGroupID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Role</td><td>" . getRoleDropDown($iRoleID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Sequence No.</td><td><input type=\"text\" name=\"fSequenceNumber\" size=\"5\" " . (isset($iSequenceNumber) ? "value=\"$iSequenceNumber\" " : " ") . "/></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" /></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fSequenceNumber,'Sequence no.'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
				
	
	return $sToRender;	
	
}

function getFolderPath($iFolderID) {
	global $default;
	return displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");		
}

function getGroupDropDown($iGroupID) {
	global $default;
    if (Permission::userIsSystemAdministrator()) {
        $oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID");
    } else {
        $oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID", "GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        $oPatternListBox->setFromClause("INNER JOIN $default->owl_groups_units_table AS GUL ON ST.id = GUL.group_id");
    }                                    
	$oPatternListBox->setEmptyErrorMessage("There are no groups");
	$oPatternListBox->setIncludeDefaultValue(false);
	if (isset($iGroupID)) {
		$oPatternListBox->setSelectedValue($iGroupID);
	}
	return $oPatternListBox->render();
}

function getRoleDropDown($iRoleID) {
	global $default;	
	$oPatternListBox = & new PatternListBox("$default->owl_roles_table", "name", "id", "fRoleID");
	$oPatternListBox->setEmptyErrorMessage("There are no roles");
	$oPatternListBox->setIncludeDefaultValue(false);
	if (isset($iRoleID)) {
		$oPatternListBox->setSelectedValue($iRoleID);
	}
	return $oPatternListBox->render();
}
?>

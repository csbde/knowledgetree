<?php
/**
 * Presentation information for adding folder access
 * Used by addFolderAccessBL.php
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

 
function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">$sHeading</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getDeletePage($oGroupFolderLink, $iFolderID) {
	global $default;
    $sToRender  = renderHeading("Delete Folder Access");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . Group::getGroupName($oGroupFolderLink->getGroupID()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Access</td><td>" . getAccessCheckBoxes(true, $oGroupFolderLink->getCanRead(), $oGroupFolderLink->getCanWrite()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/delete.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=" . $oGroupFolderLink->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<table>\n";

	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\t return confirm('Are you sure you wish to delete this folder access entry?');}\n";	
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getEditPage($oGroupFolderLink, $iFolderID) {
	global $default;
    $sToRender  = renderHeading("Edit Folder Access");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroupDropDown($oGroupFolderLink->getGroupID()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Access</td><td>" . getAccessCheckBoxes(false, $oGroupFolderLink->getCanRead(), $oGroupFolderLink->getCanWrite()) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=" . $oGroupFolderLink->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
    $sToRender .= "\tif (!theForm.fCanRead.checked && !theForm.fCanWrite.checked) {\n";
	$sToRender .= "\t\talert('You must select either read or write access'); return false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getAddPage($iFolderID) {
	global $default;
    $sToRender  = renderHeading("Add Folder Access");
    $sToRender .= getFolderPath($iFolderID);
    
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Group</td><td>" . getGroupDropDown() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Access</td><td>" . getAccessCheckBoxes(false) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr></table></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
    $sToRender .= "\tif (!theForm.fCanRead.checked && !theForm.fCanWrite.checked) {\n";
	$sToRender .= "\t\talert('You must select either read or write access'); return false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";

	return $sToRender;
}

function getAccessCheckBoxes($bReadOnly, $fCanRead = false, $fCanWrite = false) {
    return "<input name=\"fCanRead\" type=\"checkbox\" " . ($bReadOnly ? "disabled " : "") . ($fCanRead ? "checked" : "") . "/>Read\n" .
           "<input name=\"fCanWrite\" type=\"checkbox\" " . ($bReadOnly ? "disabled " : "") . ($fCanWrite ? "checked" : "") . "/>Write\n";
}

function getGroupDropDown($iGroupID = -1) {
	global $default;
    if (Permission::userIsSystemAdministrator()) {
        $oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID");
    } else {
        $oPatternListBox = & new PatternListBox("$default->owl_groups_table", "name", "id", "fGroupID", "GUL.unit_id=" . User::getUnitID($_SESSION["userID"]));
        $oPatternListBox->setFromClause("INNER JOIN $default->owl_groups_units_table AS GUL ON ST.id = GUL.group_id");
    }
	$oPatternListBox->setEmptyErrorMessage("There are no groups");
	$oPatternListBox->setIncludeDefaultValue(false);
    if ($iGroupID != -1) {
        $oPatternListBox->setWhereClause("ST.id = $iGroupID");
    }
	return $oPatternListBox->render();
}

function renderErrorPage($sErrorMessage, $fFolderID) {
    global $default;
    return "<p class=\"errorText\">$sErrorMessage</p>\n" .
           "<a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n";
}
?>

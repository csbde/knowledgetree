<?php


function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getPage($iFolderID, $iDocumentID, $iUnitID, $iUserID, $sDocumentTitle, $sTemplateDocument) {
	global $default;
	$sToRender = renderHeading("Create a new dependant document");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>Document title</td><td><input type=\"text\" name=\"fDocumentTitle\" value=\"$sDocumentTitle\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User's Unit</td><td>" . getUnitDropDown($iDocumentID, $iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . getUserDropDown($iUnitID, $iUserID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";

	//had a slight problem with netscape 4.7x - it doesn't support disabled.  So I had to use
	//javascript to set the onFocus attribute to blur 
	$bAddNetscapeScript = false;
	if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
		//for any other browser but netscape 4.7 do this		
		$sToRender .= "<td>Template document</td><td><input type=\"text\" DISABLED name=\"fTargetDocument\" value=\"$sTemplateDocument\" /><input type=\"button\" value=\"Browse\" onClick=\"newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
	}else {
		//for netscape 4.7 do this		
		$sToRender .= "<td>Template document</td><td><input type=\"text\"  name=\"fTargetDocument\" onblur=\"disable(document.MainForm.fTargetDocument);\" /><input type=\"button\" value=\"Browse\" onClick=\"disable(document.MainForm.fTargetDocument);newWindow('$default->rootUrl/control.php?action=templateBrowse','window2')\"></td>\n";
		$bAddNetscapeScript = true;
	}		
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/done.gif\" onClick='return validateForm(document.MainForm);' border=\"0\"/></td><td><a href=\"" . $default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/></a></td></tr></table>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<input type=\"hidden\" name=\"fTargetDocumentID\" value=\"-1\" />\n";	
	
	return $sToRender . "\n\n" . getValidationJavaScript() . "\n\n" . getBrowseJavaScript() . ($bAddNetscapeScript ? getNetscapeDisableScript() : "");
}

function getUserDropDown($iUnitID, $iUserID) {
	global $default;	
	if (!isset($iUnitID)) {
		$iUnitID = -1;		
	}
	$oPatternListBox = & new PatternListBox($default->owl_users_table, "Name", "id", "fUserID", "GUL.unit_id = $iUnitID");
	$sFromClause = "INNER join users_groups_link AS UGL ON UGL.user_id = ST.id " .
					"INNER JOIN groups_units_link AS GUL ON GUL.group_id = UGL.group_id ";
	$oPatternListBox->setFromClause($sFromClause);
	if (isset($iUserID)) {
		$oPatternListBox->setSelectedValue($iUserID);
	}
	return $oPatternListBox->render();
	
	
}

function getUnitDropDown($iDocumentID, $iUnitID) {
	global $default;
	if (!isset($iUnitID)) {
		$iUnitID = -1;
	}
	$oPatternListBox = & new PatternListBox($default->owl_units_table, "Name", "id", "fUnitID");	
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID')");
	if (isset($iUnitID)) {
		$oPatternListBox->setSelectedValue($iUnitID);
	}
	return $oPatternListBox->render();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fDocumentTitle,'Document title') && validRequired(theForm.fUserID,'User'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}

function getBrowseJavaScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function newWindow(file,window) {\n ";	
    $sToRender .= "\tmsgWindow=open(file,window,'resizable=yes,scrollbars=yes, width=400,height=600');\n ";
    $sToRender .= "\tif (msgWindow.opener == null) msgWindow.opener = self; \n ";
    $sToRender .= "}\n";
    $sToRender .= "//--></script>\n";	
    return $sToRender;
}

function getNetscapeDisableScript() {
	$sToRender = "<script language=\"JavaScript\"><!--\n ";
	$sToRender .= "function disable(elem) {\n";
  	$sToRender .= "\telem.onfocus=elem.blur;\n";
	$sToRender .= "}\n";
	$sToRender .= "//--></script>\n\n";
	return $sToRender;
	
}

	


?>
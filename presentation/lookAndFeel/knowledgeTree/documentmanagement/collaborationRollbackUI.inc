<?php

function getDocumentPath($iFolderID, $iDocumentID, $sDocumentName) {
    global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID), "$default->rootUrl/control.php?action=browse") . " > <a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $iDocumentID . "&fForInlineView=1\" target=\"View document\">" . $sDocumentName . "</a>";
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}

function getPage($iFolderID, $iDocumentID, $sDocumentName) {
	global $default;
	$sToRender = getDocumentPath($iFolderID, $iDocumentID, $sDocumentName);	
	$sToRender .= "<p>You have chosen to reject the previous collaboration step for the document, '$sDocumentName'. " .
					"Please enter a valid reason</p>\n";	
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><b>Comment<b></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><textarea cols=\"40\" rows=\"5\" name=\"fComment\"></textarea></td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/done.gif\" border=\"0\" />\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a>\n";
	
	
	
	return $sToRender . getValidationJavaScript();
}

function getValidationJavaScript() {
	$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fComment, 'Comment'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\tdocument.MainForm.submit();\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";
	return $sToRender;

}

?>

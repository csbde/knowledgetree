<?php
/**
* Presentation information used by addFolderDocTypeBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 37 February 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

function getPage($iFolderID, $iDocumentTypeID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sToRender = "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, "$default->uiUrl/foldermanagement/addFolderBL.php") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
	$sToRender .= "<br>";
    $sToRender .= "<table border=\"1\">\n";
    $sToRender .= "<tr>\n";	
	$sToRender .= "<td bgcolor=\"$sTDBGColour\">Document type</td>\n";
	$sToRender .= "<td>" . getDocumentTypeDropDown($iDocumentTypeID) . "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table border=\"0\"><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\"></td><td><a href=\"$default->rootUrl/control.php?action=editFolder&fFolderID=$iFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td></tr></table></td><td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
	return $sToRender;
}

function getDocumentTypeDropDown($iDocumentTypeID) {
	global $default;
	$oPatternListBox = & new PatternListBox("$default->owl_document_types_table", "name", "id", "fDocumentTypeID");
	$oPatternListBox->setIncludeDefaultValue(false);
	if (isset($iDocumentTypeID)) {
		$oPatternListBox->setSelectedValue($iDocumentTypeID);
	}
	return $oPatternListBox->render();
}

?>

<?php
/**
 * $Id$
 *
 * Presentation information used by addFolderDocTypeBL.php
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

function getPage($iFolderID, $iDocumentTypeID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
    $sToRender = renderHeading("Add Folder Document Type");
	$sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td width=\"100%\">" . renderFolderPath($iFolderID, "/control.php?action=browse	") . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";
    $sToRender .= "<tr>\n";	
	$sToRender .= "<td bgcolor=\"$sTDBGColour\">Document type</td>\n";
	$sToRender .= "<td>" . getDocumentTypeDropDown($iDocumentTypeID, $iFolderID) . "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table border=\"0\"><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"></td><td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=documentTypes", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr></table></td><td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
	return $sToRender;
}

function getDocumentTypeDropDown($iDocumentTypeID, $iFolderID) {
	global $default;
	// cunningly joined query courtesy of R. Cherry Esq.
	$oPatternListBox = & new PatternListBox(null, null, null, null);
	$oPatternListBox->setQuery("SELECT DTL.name AS display, DTL.id AS value " .  
							   "FROM $default->owl_document_types_table AS DTL " .
							   "LEFT OUTER JOIN $default->owl_folder_doctypes_table AS FDL ON FDL.document_type_id = DTL.id AND FDL.folder_id = $iFolderID " .
							   "WHERE folder_id IS NULL");
	$oPatternListBox->setSelectName("fDocumentTypeID");
	$oPatternListBox->setIncludeDefaultValue(false);
	if (isset($iDocumentTypeID)) {
		$oPatternListBox->setSelectedValue($iDocumentTypeID);
	}
	return $oPatternListBox->render();
}

?>

<?php

/**
* Presentation functionality used to searchDocumentBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 20 February 2003
* @package presentation.lookAndFeel.documentmanagement
*/

function getSearchByFolderPage($iFolderID, $sSearchText) {
	global $default;
	//get the individual words in the search text
	$aSearchWords = explode(" ", $sSearchText);
	$sQuery = "SELECT D.id, D.name, COUNT(D.id) AS cnt " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_document_words_table AS DWL ON DWL.document_id = D.ID " .
			"INNER JOIN $default->owl_words_table AS WLU ON WLU.id = DWL.word_id" .
			"WHERE D.folderID = $iFolderID " .
			"AND WLU.word IN (";
	for ($i = 0; $i < count($aSearchWords) -1; $i++) {
		$sQuery .= $aSearchWords[$i] . ", ";
	}
	$sQuery .= $aSearchWords[count($aSearchWords) -1] . ") " .
			"GROUP BY D.id, D.name " .
			"ORDER BY D.name, cnt DESC";
	
	$aColums = array("name");
	$aColumnTypes = array(3);
	$aColumnHeaders = array("Document Name");
	$aLinkURLs = array("$default->owl_root_url/control.php?action=viewDocument");
	$aTmpDBQueryStringColumns = array("id");
	$aNewQueryStringVariableNames = array("fDocumentID");
	
	$oPatternTableSqlQuery = & PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	
	return $oPatternTableSqlQuery->render();
}

function getPage($sSearchText, $sBrowseType, $iFolderID, $iDocumentID, $sCategoryName, $iDocType, $sFolderIDs, $iStartIndex, $sSQLSearchString) {
	return getSearchResults($sFolderIDs, $sSQLSearchString, $iStartIndex) . getSearchVariablesHtml($sSearchText, $sBrowseType, $iFolderID, $iDocumentID, $sCategoryName, $iDocType);
}

function getSearchResults($sFolderIDs,$sSQLSearchString, $iStartIndex) {
	global $default;	 
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id folder_id, D.id document_id, D.name AS document_name " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_folders_table AS F ON D.folder_id = F.id " .
			"INNER JOIN $default->owl_document_words_table AS DWL ON DWL.document_id = D.id " .
			"INNER JOIN $default->owl_words_lookup_table AS WL ON WL.id = DWL.word_id " .
			"WHERE F.id IN ($sFolderIDs) " .
			"AND ($sSQLSearchString)";	
			"ORDER BY D.name ASC";	
	$aColumns = array("folder_image_url", "document_name");
	$aColumnTypes = array(4,3);
	$aColumnHeaders = array("","Document");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
	return $oPatternBrowse->render();
}

function getSearchVariablesHtml($sSearchText, $sBrowseType, $iFolderID, $iDocumentID, $sCategoryName, $iDocType) {	
	$sToRender = "<input type=\"hidden\" name=\"fSearchText\" value=\"$sSearchText\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"fBrowseType\" value=\"$sBrowseType\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"fFolderID\" value=\"$iFolderID\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$iDocumentID\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"fCategoryName\" value=\"$sCategoryName\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"fDocType\" value=\"$iDocType\" />\n";
	return $sToRender;
}

?>

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

?>

<?php

/**
* Presentation functionality used to searchDocumentBL.php
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 20 February 2003
* @package presentation.lookAndFeel.documentmanagement
*/

/*function renderHeading() {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Standard Search</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}*/

function getPage($sSearchText, $sBrowseType, $iFolderID, $iDocumentID, $sCategoryName, $iDocType, $sFolderIDs, $iStartIndex, $sSQLSearchString, $bSearchByDocument = false) {
    $sRefreshMessage = "<table><tr><td align=\"center\">If your browser displays a 'Warning: Page has Expired' message when you attempt to return to these search results, please click your browser's 'Refresh' button</td></tr></table>";
    return "<table width=\"600\">" . renderHeading("Standard Search") . "</table>" . getSearchResults($sFolderIDs, $sSQLSearchString, $iStartIndex, $bSearchByDocument) . $sRefreshMessage . getSearchVariablesHtml($sSearchText, $sBrowseType, $iFolderID, $iDocumentID, $sCategoryName, $iDocType);
}

/*function getSearchResults($sIDs,$sSQLSearchString, $iStartIndex, $bSearchByDocument = false) {
	global $default;	 
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id folder_id, D.id document_id, D.name AS document_name " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_folders_table AS F ON D.folder_id = F.id " .
			"INNER JOIN $default->owl_document_words_table AS DWL ON DWL.document_id = D.id " .
			"INNER JOIN $default->owl_words_lookup_table AS WL ON WL.id = DWL.word_id ";
            if (strlen($sIDs) > 0) {
                if ($bSearchByDocument) {
                    $sQuery .= "WHERE D.id IN ($sIDs) ";
                } else {
                    $sQuery .= "WHERE F.id IN ($sIDs) ";
                }
                $sQuery .= "AND ($sSQLSearchString)";
            } else {
                $sQuery .= "WHERE ($sSQLSearchString)";
            }
				
			$sQuery .= "ORDER BY D.name ASC";
	
	$aColumns = array("folder_image_url", "document_name");
	$aColumnTypes = array(4,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
	return $oPatternBrowse->render();
}*/

function getSearchResults($sFolderIDs, $sKeywords, $iStartIndex) {
	global $default;
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id AS folder_id, D.id AS document_id, D.name AS document_name, MATCH(DT.document_text) AGAINST ('" . $sKeywords . "') AS score " . 
				"FROM search_document_user_link AS SDUL INNER JOIN document_text AS DT ON SDUL.document_id = DT.document_id " .
				"INNER JOIN documents AS D ON D.id = SDUL.document_id " .
				"INNER JOIN folders AS F on F.ID = D.folder_id " .
				"WHERE MATCH(DT.document_text) AGAINST ('" . $sKeywords . "') " .
				"AND SDUL.user_id = " . $_SESSION["userID"] . " " .
				"AND F.id IN ($sFolderIDs) " .
				"ORDER BY score DESC";
	
	$aColumns = array("folder_image_url", "document_name","score");
	$aColumnTypes = array(4,3,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>","<font color=\"ffffff\">Score</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id","score");
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

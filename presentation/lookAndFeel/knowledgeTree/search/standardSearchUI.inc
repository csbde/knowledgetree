<?php
/**
 * $Id$
 *
 * Presentation functionality used to searchDocumentBL.php
 * 
 * @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
 * @date 20 February 2003
 * @package presentation.lookAndFeel.documentmanagement
 */

function getHeading() {
    return renderHeading("Standard Search");
}

function getMessage() {
	    $sRefreshMessage = "<table><tr><td align=\"center\">If your browser displays a 'Warning: Page has Expired' message when you attempt to return to these search results, please click your browser's 'Refresh' button</td></tr></table>";
}

function getSearchResultsByCategory($iFolderID, $sKeywords, $iStartIndex, $sCategory) {
	global $default;
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id AS folder_id, D.id AS document_id, D.name AS document_name, ROUND(MATCH(DT.document_text) AGAINST ('" . $sKeywords . "'),3) AS score " . 
				"FROM documents AS D INNER JOIN document_text AS DT ON D.id = DT.document_id " .				
				"INNER JOIN $default->owl_fields_table AS DF ON DF.id = DFL.document_field_id " .
				"INNER JOIN status_lookup AS SL ON D.status_id = SL.id " .
				"INNER JOIN folders AS F on F.ID = D.folder_id " .
				"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.id " .
				"WHERE MATCH(DT.document_text) AGAINST ('" . $sKeywords . "') " .
				"AND SDUL.user_id = " . $_SESSION["userID"] . " " .
				"AND SL.name='Live' ";
				//only check in the parent_folder_ids if we're not searching from the
				//root folder down
				if ($iFolderID != 0) { 
					$sQuery .= "AND (F.parent_folder_ids LIKE '%,$iFolderID,%' OR F.id = $iFolderID) ";
				}				
				$sQuery .= "AND DF.name LIKE 'Category' ";
				if (isset($sCategory)) {
					$sQuery .= "AND DFL.value LIKE '$sCategory' ";
				}
				$sQuery .= "ORDER BY score DESC";
				
	
	//var_dump($sQuery);
	
	$aColumns = array("folder_image_url", "document_name","score");
	$aColumnTypes = array(4,3,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>","<font color=\"ffffff\">Score</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id","score");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);	
	return getHeading() . $oPatternBrowse->render() . getSearchVariablesHtml($sKeywords, "category", $iFolderID, "", $sCategory, "") . getMessage();
}

function getSearchResultsByDocumentType($iFolderID, $sKeywords, $iStartIndex, $iDocTypeID) {
	global $default;
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id AS folder_id, D.id AS document_id, D.name AS document_name, ROUND(MATCH(DT.document_text) AGAINST ('" . $sKeywords . "'),3) AS score " . 
				"FROM documents AS D INNER JOIN document_text AS DT ON D.id = DT.document_id " .								 
				"INNER JOIN status_lookup AS SL ON D.status_id = SL.id " .
				"INNER JOIN folders AS F on F.ID = D.folder_id " .
				"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.id " .
				"WHERE MATCH(DT.document_text) AGAINST ('" . $sKeywords . "') " .
				"AND SDUL.user_id = " . $_SESSION["userID"] . " ";
				if (isset($iDocTypeID)) {
					$sQuery .= "AND D.document_type_id = $iDocTypeID ";
				}					 
				$sQuery .= "AND SL.name='Live' "; 
				//only check in the parent_folder_ids if we're not searching from the
				//root folder down
				if ($iFolderID != 0) { 
					$sQuery .= "AND (F.parent_folder_ids LIKE '%,$iFolderID,%' OR F.id = $iFolderID) ";
				}				
				$sQuery .= "ORDER BY score DESC";
				
	//var_dump($sQuery);
	
	$aColumns = array("folder_image_url", "document_name","score");
	$aColumnTypes = array(4,3,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>","<font color=\"ffffff\">Score</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id","score");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
	return getHeading() . $oPatternBrowse->render() . getSearchVariablesHtml($sKeywords, "documentType", $iFolderID, "", "", $iDocTypeID) . getMessage();
	
}

function getSeachResultsByFolder($iFolderID, $iStartIndex, $sKeywords) {
	global $default;
	$sQuery = "SELECT DISTINCT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id AS folder_id, D.id AS document_id, D.name AS document_name, ROUND(MATCH(DT.document_text) AGAINST ('" . $sKeywords . "'),3) AS score " . 
				"FROM documents AS D INNER JOIN document_text AS DT ON D.id = DT.document_id " .				
				"INNER JOIN status_lookup AS SL ON D.status_id = SL.id " .
				"INNER JOIN folders AS F on F.ID = D.folder_id " .
				"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.id " .
				"WHERE MATCH(DT.document_text) AGAINST ('" . $sKeywords . "') " .
				"AND SDUL.user_id = " . $_SESSION["userID"] . " " .
				"AND SL.name='Live' "; 
				//only check in the parent_folder_ids if we're not searching from the
				//root folder down
				if ($iFolderID != 0) { 
					$sQuery .= "AND (F.parent_folder_ids LIKE '%,$iFolderID,%' OR F.id = $iFolderID) ";
				}				
				$sQuery .= "ORDER BY score DESC";
				
	//var_dump($sQuery);
	
	$aColumns = array("folder_image_url", "document_name","score");
	$aColumnTypes = array(4,3,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>","<font color=\"ffffff\">Score</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id","score");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
	return getHeading() . $oPatternBrowse->render() . getSearchVariablesHtml($sKeywords, "", $iFolderID, "", "", "") . getMessage();
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

<?php

/**
* Presentation logic used by advancedSearchBL.php
*
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 26 February 2003 
* @package presentation.lookAndFeel.knowledgeTree.search
* 
*/

function renderHeading() {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">Advanced Search</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}

function getMetaData($aMetaTagIDs) {
	global $default;	
	$sQuery = "SELECT name, id FROM $default->owl_fields_table ORDER BY name ASC";
	
	$sql = $default->db;
	$sql->query($sQuery);
	
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_start\" value=\"\" />\n";
	
    $iRecordCount = 0;
	while ($sql->next_record()) {
        $sMetaDataCheckBox = "<input type=\"checkbox\" " . wasSelected($sql->f("id"), $aMetaTagIDs) . " name=\"f_adv_" . $sql->f("name") . "\" value=\"" . $sql->f("id") . "\"></td><td>" . $sql->f("name");
        $iRecordCount++;
        if (($iRecordCount % 3) == 1) {
            $sToRender .= "<tr><td>$sMetaDataCheckBox</td>\n";
        } else if (($iRecordCount % 3) == 0) {
            $sToRender .= "<td>$sMetaDataCheckBox</td></tr>\n";
        } else {
            $sToRender .= "<td>$sMetaDataCheckBox</td>\n";
        }
	}
	
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_end\" value=\"\" />\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

function getSearchPage($sSearchString = "", $aMetaTagIDs = array()) {
	global $default;
	$sToRender = "<table width=\"600\">\n";
    $sToRender .= renderHeading();
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Search text: <input type=\"text\" size=\"60\" name=\"fSearchString\" value=\"$sSearchString\" />\n";
	//$sToRender .= "<td><input type=\"button\" src=\"$default->graphicsUrl/search.gif\" value=\"search\" onClick=\"validateAdvancedSearchText()\"/></td>\n";
	$sToRender .= "<input type=\"button\" value=\"search\" onClick=\"validateAdvancedSearch()\"/></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getMetaData($aMetaTagIDs) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender . getSearchValidationJavaScript();;
}

//function getSearchResults($sSQLSearchString, $iStartIndex, $sSearchText, $sMetaTagIDs) {
function getSearchResults($sMetaTagIDs, $sSQLSearchString, $iStartIndex) {
	global $default;	
	$sQuery = "SELECT '" . "$default->graphicsUrl/widgets/dfolder.gif" . "' AS folder_image_url, F.id folder_id, D.id document_id, D.name AS document_name, COUNT(D.id) AS doc_count " .
			"FROM $default->owl_documents_table AS D INNER JOIN $default->owl_folders_table AS F ON D.folder_id = F.id " .
			"INNER JOIN document_fields_link AS DFL ON DFL.document_id = D.id " .
			"INNER JOIN document_fields AS DF ON DF.id = DFL.document_field_id " . 
			"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.ID " .			
			//"WHERE D.id IN ($sSQLSearchString) " .
			"WHERE DF.ID IN ($sMetaTagIDs) " .
			"AND " . $sSQLSearchString . " " .
			"AND SDUL.user_id = " . $_SESSION["userID"] . " " .
			"GROUP BY D.id ";
			"ORDER BY doc_count DESC ";
			
	//echo $sQuery;	
		
	$aColumns = array("folder_image_url", "document_name", "doc_count");
	$aColumnTypes = array(4,3,3);
	$aColumnHeaders = array("<font color=\"ffffff\">Folder</font>","<font color=\"ffffff\">Document</font>", "<font color=\"ffffff\">Matches</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults($sQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
    
    $sRefreshMessage = "<table><tr><td align=\"center\">If your browser displays a 'Warning: Page has Expired' message when you attempt to return to these search results, please click your browser's 'Refresh' button</td></tr></table>";
	return "<table width=\"600\">" . renderHeading() . "</table>" . $oPatternBrowse->render() . $sRefreshMessage . getSearchVariablesHtml($sSearchText, $sMetaTagIDs);
}

function getSearchValidationJavaScript() {
	$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateAdvancedSearch() {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fSearchString, 'Search text'))) {\n";
	$sToRender .= "\t\treturn;\n\t}\n";
	$sToRender .= "\tdocument.MainForm.submit();\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";
	return $sToRender;

}

function wasSelected($iID, $aMetaTagIDs) {
	if (in_array($iID, $aMetaTagIDs)) {
		return "CHECKED";
	}	
	return "";
}

function getSearchVariablesHtml($sSearchText, $sMetaTagIDs) {
	$aMetaTagIDs = explode(",", $sMetaTagIDs);
	$sToRender = "\n\n<input type=\"hidden\" name=\"fSearchString\" value=\"$sSearchText\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_start\" value=\"\" />\n";
	for ($i = 0; $i < count($aMetaTagIDs); $i++) {
		$sToRender .= "<input type=\"hidden\" name=\"fMetaTag_$i\" value=\"" . $aMetaTagIDs[$i] . "\" />\n";
		
	}
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_end\" value=\"\" />\n";
	return $sToRender;
}

?>


<?php

/**
* Presentation logic used by advancedSearchBL.php
*
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 26 February 2003 
* @package presentation.lookAndFeel.knowledgeTree.search
* 
*/

function getMetaData($aMetaTagIDs) {
	global $default;
	$sQuery = "SELECT name, id FROM $default->owl_fields_table ORDER BY name ASC";
	
	$sql = $default->db;
	$sql->query($sQuery);
	
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_start\" value=\"\" />\n";
	
	while ($sql->next_record()) {
		$sToRender .= "<tr><td><input type=\"checkbox\" " . wasSelected($sql->f("id"), $aMetaTagIDs) . " name=\"f_adv_" . $sql->f("name") . "\" value=\"" . $sql->f("id") . "\"></td><td>" . $sql->f("name") . "</td></tr>\n";		
		
	}
	
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_end\" value=\"\" />\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

function getSearchPage($sSearchString = "", $aMetaTagIDs = array()) {
	global $default;
	$sToRender = "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Search text: <input type=\"text\" size=\"60\" name=\"fSearchString\" value=\"$sSearchString\" /></td>\n";
	$sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/search.gif\" /></td>\n";
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
	
	return $sToRender;
}

function getSearchResults($sFolderIDs,$sSQLSearchString, $iStartIndex) {
	
}

function wasSelected($iID, $aMetaTagIDs) {
	if (in_array($iID, $aMetaTagIDs)) {
		return "CHECKED";
	}	
	return "";
}

?>


<?php
/**
 * $Id$
 *
 * Business logic used to perform advanced search.  Advanced search allows
 * users to search by meta data types
 * 
 * @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
 * @date 26 February 2003
 * @package presentation.knowledgeTree.search
 */

/**
 * Interrogates the posted variables for the chosen metadata tags
 */
function getChosenMetaDataTags() {
	$aKeys = array_keys($_POST);
	$aTagIDs = array();
	for ($i = 0; $i < count($aKeys); $i++) {	
		$sRowStart = $aKeys[$i];		
		$pos = strcmp("adv_search_start", $sRowStart);
		if ($pos == 0) {
			$i++;
			$sRowStart = $aKeys[$i];
			while ((strcmp("adv_search_end", $sRowStart) != 0)  && ($i < count($aKeys))) {				
				$aTagIDs[count($aTagIDs)] = $_POST[$aKeys[$i]];
				$i++;
				$sRowStart = $aKeys[$i];
			}
			
		}
	}
	if (count($aTagIDs) > 1) {
		return implode(",",$aTagIDs);
	}
	return $aTagIDs[0];
}

/*
* Generate a string that can be used in a SQL query
* from the list of documents the user is allowed to see
*/
function getSQLSearchString($sSearchString) {
	$aWords = explode(" ", $sSearchString);
	$sSQLSearchString;
	for ($i = 0; $i < count($aWords) - 1; $i++) {
		$sSQLSearchString .= "(DFL.value LIKE '%" . $aWords[$i] . "%') OR ";
	}
	$sSQLSearchString .= "(DFL.value LIKE '%" . $aWords[count($aWords) -1] . "%')";
	return $sSQLSearchString;
}
?>
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

/**
* Generate a string consisting of all documents that match the search criteria
* and that the user is allowed to see
*/
function getApprovedDocumentString($sMetaTagIDs, $sSQLSearchString, $sStatus = "Live") {	
	global $default;
	$aApprovedDocumentIDs = array();
	$sQuery = "SELECT DISTINCT D.id " .
				"FROM documents AS D INNER JOIN document_fields_link AS DFL ON DFL.document_id = D.id " .
				"INNER JOIN document_fields AS DF ON DF.id = DFL.document_field_id " .
				"INNER JOIN search_document_user_link AS SDUL ON SDUL.document_id = D.ID " .
				"INNER JOIN status_lookup AS SL on D.status_id=SL.id " .			
				"WHERE DF.ID IN ($sMetaTagIDs) " .
				"AND (" . $sSQLSearchString . ") " .
				"AND SL.name='$sStatus' " .
				"AND SDUL.user_id = " . $_SESSION["userID"];
	$sql = $default->db;
	$sql->query($sQuery);
	while ($sql->next_record()) {
		$aApprovedDocuments[count($aApprovedDocuments)] = $sql->f("id");
	}
	if (count($aApprovedDocuments) > 1) {
		return implode(",",$aApprovedDocuments);
	}
	return $aApprovedDocuments[0];

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
<?php
/**
* Business logic used to perform advanced search.  Advanced search allows
* users to search by meta data types
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 26 February 2003
* @package presentation.knowledgeTree.search
*/

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("advancedSearchUI.inc");
	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

	if (isset($fForSearch)) {		
		if (strlen($fSearchString) > 0) {
			//display search results
			$sMetaTagIDs = getChosenMetaDataTags();	
			
			if (strlen($sMetaTagIDs) > 0) {
				$sSQLSearchString = getSQLSearchString($fSearchString);
				$sDocument = getApprovedDocumentString($sMetaTagIDs, $sSQLSearchString);
				echo $sDocument;
				
			} else {
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getSearchPage($fSearchString));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("Please select at least one criteria to search by");
				$main->setFormAction("advancedSearchBL.php?fForSearch=1");                                
				$main->render();
			}
		} else {
				$sMetaTagIDs = getChosenMetaDataTags();				
				$aMetaTagIDs = explode(",", $sMetaTagIDs);				
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getSearchPage($fSearchString, $aMetaTagIDs));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("Please enter text to search on");
				$main->setFormAction("advancedSearchBL.php?fForSearch=1");                                
				$main->render();
		}
		
	} else {	
		//display search criteria
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getSearchPage($fSearchString));
		$main->setCentralPayload($oPatternCustom);                                
		$main->setFormAction("advancedSearchBL.php?fForSearch=1");                                
		$main->render();
	}

		
}

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
	return implode(",",$aTagIDs);
}

function getApprovedDocumentString($sMetaTagIDs, $sSQLSearchString) {
	global $default;
	$aApprovedDocumentIDs = array();
	$sQuery = "SELECT DISTINCT D.id " .
				"FROM documents AS D INNER JOIN document_fields_link AS DFL ON DFL.document_id = D.id " .
				"INNER JOIN document_fields AS DF ON DF.id = DFL.document_field_id " .
				"WHERE DF.ID IN (1,2,3,4) " .
				"AND " . $sSQLSearchString;
				
	$sql = $default->db;
	$sql->query($sQuery);	
	while ($sql->next_record()) {
		if (Permission::userHasDocumentReadPermission($sql->f("id"))) {
				$aApprovedDocuments[count($aApprovedDocuments)] = $sql->f("id");
		}
	}
	return implode(",",$aApprovedDocuments);	
}

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

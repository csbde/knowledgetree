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

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("advancedSearchUI.inc");
	require_once("advancedSearchUtil.inc");	
	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

	if (strlen($fForSearch)) {		
		if (strlen($fSearchString) > 0) {
			$oPatternCustom = & new PatternCustom();
			
			//display search results
			$sMetaTagIDs = getChosenMetaDataTags();	
			
			if (strlen($sMetaTagIDs) > 0) {
				$sSQLSearchString = getSQLSearchString($fSearchString);
				
				if (!isset($fStartIndex)) {
					$fStartIndex = 0;
				}
				$oPatternCustom->setHtml(getSearchResults($sMetaTagIDs,$sSQLSearchString, $fStartIndex, $fToSearch));
				$main->setCentralPayload($oPatternCustom);				                                
				$main->render();
			} else {
				
				$oPatternCustom->setHtml(getSearchPage($fSearchString));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("Please select at least one criteria to search by");
				$main->setHasRequiredFields(true);
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
				$main->setHasRequiredFields(true);
				$main->setFormAction("advancedSearchBL.php?fForSearch=1");                                
				$main->render();
		}
		
	} else {	
		//display search criteria
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getSearchPage($fSearchString));
		$main->setHasRequiredFields(true);
		$main->setCentralPayload($oPatternCustom);                                
		$main->setFormAction("advancedSearchBL.php?fForSearch=1");                                
		$main->render();
	}	
}
?>
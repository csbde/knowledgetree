<?php
/**
* Business logic used to perform document searches
* 
* Expected form variables
*	o	fStandardSearchString - text to search on
*	o	fBrowseType - current browse type
*	o	fFolderID - folder currently being browsed (if a folder is being browsed)
*	o	fDocumentID - document currently being browsed (if a document is being browsed)
*	o	fCategoryName - name of category being browsed (if a category is being browsed)
*	o	fDocTypeID - name of document type being browsed (if a doc type is being browsed)
*
*
*/
require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");	
	require_once("standardSearchUI.inc");		
	
	if (!isset($fStartIndex)) {
		$fStartIndex = 0;
	}
	//escape the search string
	$fStandardSearchString = addslashes($fStandardSearchString);
	
		if (strlen($fBrowseType) > 0) {
			echo "browse type";
			//the user was browsing by a specific type
			switch ($fBrowseType) {
			case "folder" : 
							//user was browsing a specific folder - search that folder							
							if (!$fFolderID) {
								//start at the root folder
								$fFolderID = 0;
							}								
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
							$oPatternCustom = & new PatternCustom();                                
                            $oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fStandardSearchString));
                            $main->setCentralPayload($oPatternCustom);
                            $main->render();
							break;
			case "category" :
							//user was browsing by category - search all documents in that category
							if (!$fCategoryName) {
								//no category name specified, so just start at the root folder								
								$fFolderID = 0;
							}								
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
							$oPatternCustom = & new PatternCustom();                                
                            $oPatternCustom->setHtml(getSearchResultsByCategory($fFolderID, $fStandardSearchString, $fStartIndex, $fCategoryName));
                            $main->setCentralPayload($oPatternCustom);
                            $main->render();
							break;							
			case "documentType" :
							//user was browsing by document type - search all documents in that doc type
							if (!$fDocTypeID) {
								//no document type specified, so just start at the root folder
								$fFolderID = 0;
							}							
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
							$oPatternCustom = & new PatternCustom();                                
                            $oPatternCustom->setHtml(getSearchResultsByDocumentType($fFolderID, $fStandardSearchString, $fStartIndex, $fDocTypeID));
                            $main->setCentralPayload($oPatternCustom);
                            $main->render();
							break;
			default:
				//search from the root folder down i.e. all documents
				break;
			}
		} else if (strlen($fFolderID) > 0) {
			//the user was browsing a folder, search that folder			
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();            
            $oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fStandardSearchString));
            $main->setCentralPayload($oPatternCustom);
            $main->render();
			
		} else  if (strlen($fDocumentID) > 0) {
			//the user was viewing a document, search in that document's folder
			$oDocument = Document::get($fDocumentID);
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();			
			$oPatternCustom->setHtml(getSeachResultsByFolder($oDocument->getFolderID(), $fStartIndex, $fStandardSearchString));
			$main->setCentralPayload($oPatternCustom);
			$main->render();												
		} else {
			//search from the root folder down i.e. all documents			
			$fFolderID = 0;
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fStandardSearchString));
			$main->setCentralPayload($oPatternCustom);
			$main->render();							
		}
}
?>


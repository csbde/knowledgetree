<?php
/**
* Business logic used to perform document searches
* 
* Expected form variables
*	o	fSearchText - text to search on
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

	if (strlen($fBrowseType) > 0) {			
		//the user was browsing by a specific type
		switch ($fBrowseType) {
		case "folder" :
						//user was browsing a specific folder - search that folder							
						if (!$fFolderID) {
							//start at the root folder
							$fFolderID = 1;
						}								
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
						$oPatternCustom = & new PatternCustom();                                
                        $oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fSearchText));
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
						break;
		case "category" :
						//user was browsing by category - search all documents in that category
						if (!$fCategoryName) {
							//no category name specified, so just start at the root folder								
							$fFolderID = 1;
						}
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
						$oPatternCustom = & new PatternCustom();                                
                        $oPatternCustom->setHtml(getSearchResultsByCategory($fFolderID, $fSearchText, $fStartIndex, $fCategoryName));
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
						break;							
		case "documentType" :
						//echo "searching by documentType browseType";
						//user was browsing by document type - search all documents in that doc type
						if (!$fDocTypeID) {
							//no document type specified, so just start at the root folder
							$fFolderID = 1;
						}							
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
						$oPatternCustom = & new PatternCustom();                                
                        $oPatternCustom->setHtml(getSearchResultsByDocumentType($fFolderID, $fSearchText, $fStartIndex, $fDocTypeID));
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
						break;
		default:
			//search from the root folder down i.e. all documents
			break;
		}
	} else if (strlen($fFolderID) > 0) {
		//the user was browsing a folder, search that folder
		//echo "searching by folder id";			
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();            
          $oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fSearchText));
          $main->setCentralPayload($oPatternCustom);
          $main->render();
		
	} else  if (strlen($fDocumentID) > 0) {
		//echo "searching by document id";
		//the user was viewing a document, search in that document's folder
		$oDocument = Document::get($fDocumentID);
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();			
		$oPatternCustom->setHtml(getSeachResultsByFolder($oDocument->getFolderID(), $fStartIndex, $fSearchText));
		$main->setCentralPayload($oPatternCustom);
		$main->render();												
	} else {
		//echo "searching by folder";
		//search from the root folder down i.e. all documents			
		$fFolderID = 1;
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getSeachResultsByFolder($fFolderID, $fStartIndex, $fSearchText));
		$main->setCentralPayload($oPatternCustom);
		$main->render();							
	}
}
//echo "not searching"
?>


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
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("standardSearchUI.inc");		
		if (strlen($fBrowseType) > 0) {
			//the user was browsing by a specific type
			switch ($fBrowseType) {
			case "folder" : 
							//user was browsing a specific folder - search that folder
							if (!$fFolderID) {
							
								
							} else {
								$sChildString = implode(",", Folder::getChildren($fFolderID));
							}
							break;
							
			case "category" :
							//user was browsing by category - search all documents in that category
							if (!$fCategoryName) {
								
							} else {
								
							}
							break;
							
			case "documentType" :
							//user was browsing by document type - search all documents in that doc type
							if (!$fDocumentTypeID) {
								
							} else {
								
							}
							break;
			default:
				//search from the root folder down i.e. all documents
				break;
			} 
		} else if (strlen($fFolderID) > 0) {
			//the user was browsing a folder, search that folder			
			//var_dump(Folder::getChildren($fFolderID));
			echo count(Folder::getChildren($fFolderID));
			
		} else  if (strlen($fDocumentID) > 0) {
			//the user was viewing a document, search in that document's folder
					
		} else {
			//search from the root folder down i.e. all documents
			
		}
}


function searchByFolder($iFolderID, $sSearchText) {
	global $default;
	//get a list of documents in the folder
	//TODO - CHECK THAT USER HAS READ RIGHTS TO THIS FOLDER
	echo getSearchByFolderPage($iFolderID, $sSearchText);
	
	
}

function searchByCategory($sCategoryName) {
	
}

function searchByDocType($iDocTypeID) {
	
}



?>


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
								$fFolderID = 0;
								$sFolderString = getApprovedFolderString($fFolderID);
								require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
								$oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
                                $main->setCentralPayload($oPatternCustom);                                
                                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                                $main->setHasRequiredFields(true);
                                $main->render();								
							} else {
								$sFolderString = getApprovedFolderString($fFolderID);
								require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
								$oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
                                $main->setCentralPayload($oPatternCustom);                                
                                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                                $main->setHasRequiredFields(true);
                                $main->render();
							}
							break;
			case "category" :
							//user was browsing by category - search all documents in that category
							if (!$fCategoryName) {
								//no category name specified, so just start at the root folder								
								$fFolderID = 0;
								$sFolderString = getApprovedFolderString($fFolderID);
								require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
								$oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
                                $main->setCentralPayload($oPatternCustom);                                
                                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                                $main->setHasRequiredFields(true);
                                $main->render();			
							} else {								 
								$sFolderString = getApprovedFolderStringFromCategory($fCategoryName);
								require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
								$oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
                                $main->setCentralPayload($oPatternCustom);                                
                                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                                $main->setHasRequiredFields(true);
                                $main->render();								
							}
							break;
							
			case "documentType" :
							//user was browsing by document type - search all documents in that doc type
							if (!$fDocumentTypeID) {
								//no document type specified, so just start at the root folder
								$fFolderID = 0;
								$sFolderString = getApprovedFolderString($fFolderID);
								require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
								$oPatternCustom = & new PatternCustom();
                                $oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
                                $main->setCentralPayload($oPatternCustom);                                
                                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                                $main->setHasRequiredFields(true);
                                $main->render();			
							} else {
								//TODO ONCE DOC TYPE/FOLDERS HAVE BEEN CHANGED
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
			$oDocument = Document::get($fDocumentID);
			$sFolderString = getApprovedFolderString($oDocument->getFolderID());
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
			$main->setCentralPayload($oPatternCustom);                                
			$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
			$main->setHasRequiredFields(true);
			$main->render();												
		} else {
			//search from the root folder down i.e. all documents
			$fFolderID = 0;
			$sFolderString = getApprovedFolderString($fFolderID);
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($fSearchText, $fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocType, $sFolderString, $fStartIndex, getSQLSearchString($fSearchText)));
			$main->setCentralPayload($oPatternCustom);                                
			$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
			$main->setHasRequiredFields(true);
			$main->render();							
		}
}


function searchByFolder($iFolderID, $sSearchText) {
	global $default;
	//get a list of documents in the folder
	//TODO - CHECK THAT USER HAS READ RIGHTS TO THIS FOLDER
	echo getSearchByFolderPage($iFolderID, $sSearchText);
	
	
}

function getApprovedFolderString($iFolderID) {
	$aChildren = Folder::getChildren($iFolderID);								
	$aApprovedChildren = array();
	//filter out all the folders the user does
	//not have permission to see
	for ($i = 0; $i < count($aChildren); $i++) {									
		$oFolder = Folder::get($aChildren[$i]);
		
		//if the folder is already approved, continue
		if (in_array($oFolder->getID(), $aApprovedChildren)) {
			//echo "Already in: " . $oFolder->getID() . "<br>";
			continue;
		}
		
		$aParentFolderIDs = explode(",",$oFolder->getParentFolderIDs());
		
		//if one of the folder's parents is already approved, add the folder
		for ($j = 0; $j < count($aParentFolderIDs); $j++) {										
			if (in_array($aParentFolderIDs[$j], $aApprovedChildren)) {											
				if (in_array($oFolder->getID(), $aApprovedChildren)) {
					$aApprovedChildren[count($aApprovedChildren)] = $oFolder->getID();
				}
				continue;
			}
		}
		
		//check if the user has read permission for this folder
		if (Permission::userHasFolderReadPermission($oFolder->getID())) {										 
			$aApprovedChildren[count($aApprovedChildren)] = $oFolder->getID();
			continue;
		}
		
	}
	return implode(",", $aApprovedChildren);
}

function getApprovedFolderStringFromCategory($sCategory) {
	global $default;
	$sQuery = "SELECT DISTINCT D.folder_id " . 
			"FROM $default->owl_documents_table AS D inner join $default->owl_document_fields_table AS DFL ON D.id = DFL.document_id " .
			"INNER JOIN $default->owl_fields_table AS DF ON DF.id = DFL.document_field_id " .
			"WHERE DF.name LIKE 'Category' " .
			"AND DFL.value LIKE '$sCategory'";
			
	$sql = $default->db;
	$sql->query($sQuery);
	if ($sql->next_record()) {
		//get all the folders in the category
		$aFolders = array($sql->f("folder_id"));
		while ($sql->next_record()) {
			$aFolders[count($aFolders)] = $sql->f("folder_id");
		}
		
		$aApprovedChildren = array();
		//filter out all the folders the user does
		//not have permission to see
		for ($i = 0; $i < count($aFolders); $i++) {									
			$oFolder = Folder::get($aFolders[$i]);
			
			//if the folder is already approved, continue
			if (in_array($oFolder->getID(), $aApprovedChildren)) {
				//echo "Already in: " . $oFolder->getID() . "<br>";
				continue;
			}
			
			$aParentFolderIDs = explode(",",$oFolder->getParentFolderIDs());
			
			//if one of the folder's parents is already approved, add the folder
			for ($j = 0; $j < count($aParentFolderIDs); $j++) {										
				if (in_array($aParentFolderIDs[$j], $aApprovedChildren)) {											
					if (in_array($oFolder->getID(), $aApprovedChildren)) {
						$aApprovedChildren[count($aApprovedChildren)] = $oFolder->getID();
					}
					continue;
				}
			}
			
			//check if the user has read permission for this folder
			if (Permission::userHasFolderReadPermission($oFolder->getID())) {										 
				$aApprovedChildren[count($aApprovedChildren)] = $oFolder->getID();
				continue;
			}
			
		}		
		return implode(",", $aApprovedChildren);		
	}
	return "0";
}

function getSQLSearchString($sSearchString) {
	$aWords = explode(" ", $sSearchString);
	$sSQLSearchString;
	for ($i = 0; $i < count($aWords) - 1; $i++) {
		$sSQLSearchString .= "(WL.word LIKE '%" . $aWords[$i] . "%') OR ";
	}
	$sSQLSearchString .= "(WL.word LIKE '%" . $aWords[count($aWords) -1] . "%')";
	return $sSQLSearchString;
}

function searchByCategory($sCategoryName) {
	
}

function searchByDocType($iDocTypeID) {
	
}





?>


<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php"); 
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc"); 
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc"); 

class DocumentCollection {
   // handle the sorting, etc.
   var $_sFolderJoinClause = null;
   var $_aFolderJoinParams = null;
   var $_sFolderSortField = null;
   var $_sDocumentJoinClause = null;
   var $_aDocumentJoinParams = null;
   var $_sDocumentSortField = null;
   var $_queryObj = null;
   
   // current documents (in _this_ batch.)
   var $activeset = null;

   var $_documentData = array();             // [docid] => array();
   var $_folderData = array();               // [folderid] => array();
   var $columns = array();                   // the columns in use
   
   var $returnURL = null;
   
   var $folderCount = 0;
   var $documentCount = 0;
   var $itemCount = 0;   
   var $batchStart = 0;                      // if batch specified a "start".
   var $batchPage = 0;
   var $batchSize = 20;                      // size of the batch   // FIXME make this configurable.
   
   
   var $sort_column;
   var $sort_order;

   /* initialisation */
   
   // columns should be added in the "correct" order (e.g. display order)
   function addColumn($oBrowseColumn) { array_push($this->columns, $oBrowseColumn); }   
   function setQueryObject($oQueryObj) { $this->_queryObj = $oQueryObj; }   

   /* fetch cycle */   
   
   // FIXME this needs to be handled by US, not browse / search.
   
   function setBatching($sReturnURL, $iBatchPage, $iBatchSize) {
      $this->returnURL = $sReturnURL;
	  $this->batchPage = $iBatchPage; 
	  $this->batchSize = $iBatchSize; 
	  $this->batchStart = $this->batchPage * $this->batchSize; 
   }      
   
   // column is the label of the column.
   
   function setSorting($sSortColumn, $sSortOrder) { 
      // FIXME affect the column based on this.
	  
	  // defaults
	  $this->_sDocumentSortField = "D.name";
	  $this->_sFolderSortField = "F.name";
	  
	  // then we start.
      $this->sort_column = $sSortColumn; 
	  $this->sort_order = $sSortOrder; 
	  
	  
	  // this is O(n).  Do this only after adding all columns.
	  foreach ($this->columns as $key => $oColumn) { 
		 if ($oColumn->name == $sSortColumn) { 
		    // nb: don't use $oColumn - its a different object (?)
			$this->columns[$key]->setSortedOn(true);
			$this->columns[$key]->setSortDirection($sSortOrder);
			
			// get the join params from the object.
			$aFQ = $this->columns[$key]->addToFolderQuery();
			$aDQ = $this->columns[$key]->addToDocumentQuery();
			
			$this->_sFolderJoinClause = $aFQ[0];
   			$this->_aFolderJoinParams = $aFQ[1];
			if ($aFQ[2]) { $this->_sFolderSortField = $aFQ[2]; }
   			$this->_sDocumentJoinClause = $aDQ[0];
   			$this->_aDocumentJoinParams = $aDQ[1];
			if ($aDQ[2]) { $this->_sDocumentSortField = $aDQ[2]; }
			
		 } else {
		    $oColumn->setSortedOn(false);
		 }
		 
	  }
	  
   }

   // finally, generate the results.  either (documents or folders) could be null/empty
   // FIXME handle column-for-sorting (esp. md?)
   function getResults() {
	  // we get back strings of numbers.
	  $this->folderCount = $this->_queryObj->getFolderCount();
      if (PEAR::isError($this->folderCount)) {
          $_SESSION['KTErrorMessage'][] = $this->folderCount->toString();
          $this->folderCount = 0;
      }
	  $this->documentCount = $this->_queryObj->getDocumentCount();
      if (PEAR::isError($this->documentCount)) {
          $_SESSION['KTErrorMessage'][] = $this->documentCount->toString();
          $this->documentCount = 0;
      }
	  $this->itemCount = $this->documentCount + $this->folderCount;
	  
	  // now we need the active set:  this is based on the batchsize,
	  // batchstart.  this is divided into folders/documents. (_no_ intermingling).
	  $folderSet = null;
	  $documentSet = null;

	  // assume we have not documents.  This impacts "where" our documents start.
	  // 
	  $no_folders = true;
	  $documents_to_get = $this->batchSize;
	  $folders_to_get = 0;

	  if ($this->batchStart < $this->folderCount) {
		 $no_folders = false;
		 $folders_to_get = $this->folderCount - $this->batchStart;
		 if ($folders_to_get > $this->batchSize) {
			$folders_to_get = $this->batchSize;
			$documents_to_get = 0;
		 } else {
		    $documents_to_get -= $folders_to_get; // batch-size less the folders.
		 }
		 
	  }
	  
	  
	  if ($no_folders) {
		 $documentSet = $this->_queryObj->getDocuments($documents_to_get, $this->batchStart, $this->_sDocumentSortField, $this->sort_order, $this->_sDocumentJoinQuery, $this->_aDocumentJoinParams);
	  } else {
	     $folderSet = $this->_queryObj->getFolders($folders_to_get, $this->batchStart, $this->_sFolderSortField, $this->sort_order, $this->_sFolderJoinQuery, $this->_aFolderJoinParams);
		 if ($documents_to_get > 0) {
	        $documentSet = $this->_queryObj->getDocuments($documents_to_get, 0, $this->_sDocumentSortField, $this->sort_order, $this->_sDocumentJoinQuery, $this->_aDocumentJoinParams);
		 }
		 
	  }

	  $this->activeset = array(
		 "folders" => $folderSet,
		 "documents" => $documentSet,
	  );	  
	  
	  
   }

   // stub:  fetch all relevant information about a document (that will reasonably be fetched).  
   function getDocumentInfo($iDocumentId) { 
      if (array_key_exists($iDocumentId, $this->_documentData)) {
         return $this->_documentData[$iDocumentId];  
      } else {
         $this->_documentData[$iDocumentId] = $this->_retrieveDocumentInfo($iDocumentId);
         return $this->_documentData[$iDocumentId];
      }
   }   
   function _retrieveDocumentInfo($iDocumentId) { 
      $row_info = array("docid" => $iDocumentId);
	  $row_info["type"] = "document";
	  $row_info["document"] =& Document::get($iDocumentId);
	  
	  return $row_info;
   }
   
   // FIXME get more document info.
   function getFolderInfo($iFolderId) { 
      if (array_key_exists($iFolderId, $this->_folderData)) {
         return $this->_folderData[$iFolderId];  
      } else {
         $this->_folderData[$iFolderId] = $this->_retrieveFolderInfo($iFolderId);
         return $this->_folderData[$iFolderId];
      } 
   }   
   
   // FIXME get more folder info.
   function _retrieveFolderInfo($iFolderId) { 
      $row_info = array("folderid" => $iFolderId);
	  $row_info["type"] = "folder";
	  $row_info["folder"] =& Folder::get($iFolderId);	  
	  
	  return $row_info;
   }
   
   // render a particular row.
   function renderRow($iDocumentId) { ; }
   // link url for a particular page.
   function pageLink($iPageNumber) { 
	  return $this->returnURL . "&page=" . $iPageNumber . "&sort_on=" . $this->sort_column . "&sort_order=" . $this->sort_order; 
   }
   
   function render() {
      // sort out the batch
      $pagecount = (int) floor($this->itemCount / $this->batchSize);
	  if (($this->itemCount % $this->batchSize) != 0) {
		 $pagecount += 1;
      }
	  // FIXME expose the current set of rows to the document.
	  
      $oTemplating = new KTTemplating;
	  $oTemplate = $oTemplating->loadTemplate("kt3/document_collection");
	  $aTemplateData = array(
         "context" => $this,
		 "pagecount" => $pagecount,
		 "currentpage" => $this->batchPage,
		 "returnURL" => $this->returnURL,
		 "columncount" => count($this->columns),
	  );
	  
	  // in order to allow OTHER things than batch to move us around, we do:
	  return $oTemplate->render($aTemplateData);
   }
}

?>

<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');

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

   var $is_advanced = false;

   var $empty_message;

   /* initialisation */

   function DocumentCollection() {
       $this->empty_message = _kt('No folders or documents in this location.');
   }

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
	  $this->_sDocumentSortField = 'DM.name';
	  $this->_sFolderSortField = 'F.name';

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
          $this->batchStart -= $this->folderCount;
		 $documentSet = $this->_queryObj->getDocuments($documents_to_get, $this->batchStart, $this->_sDocumentSortField, $this->sort_order, $this->_sDocumentJoinClause, $this->_aDocumentJoinParams);
	  } else {
	     $folderSet = $this->_queryObj->getFolders($folders_to_get, $this->batchStart, $this->_sFolderSortField, $this->sort_order, $this->_sFolderJoinClause, $this->_aFolderJoinParams);
		 if ($documents_to_get > 0) {
	        $documentSet = $this->_queryObj->getDocuments($documents_to_get, 0, $this->_sDocumentSortField, $this->sort_order, $this->_sDocumentJoinClause, $this->_aDocumentJoinParams);
		 }

	  }
	  //var_dump($folderSet);
	  $this->activeset = array(
		 'folders' => $folderSet,
		 'documents' => $documentSet,
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
      $row_info = array('docid' => $iDocumentId);
	  $row_info['type'] = 'document';
	  $row_info['document'] =& Document::get($iDocumentId);

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
      $row_info = array('folderid' => $iFolderId);
	  $row_info['type'] = 'folder';
	  $row_info['folder'] =& Folder::get($iFolderId);

	  return $row_info;
   }

   // render a particular row.
   function renderRow($iDocumentId) { ; }
   // link url for a particular page.
   function pageLink($iPageNumber) {
	  return $this->returnURL . '&page=' . $iPageNumber . '&sort_on=' . $this->sort_column . '&sort_order=' . $this->sort_order;
   }

   function render() {
      // sort out the batch
      $pagecount = (int) floor($this->itemCount / $this->batchSize);
	  if (($this->itemCount % $this->batchSize) != 0) {
		 $pagecount += 1;
      }
	  // FIXME expose the current set of rows to the document.

      $oTemplating =& KTTemplating::getSingleton();
	  $oTemplate = $oTemplating->loadTemplate('kt3/document_collection');
	  $aTemplateData = array(
         'context' => $this,
		 'pagecount' => $pagecount,
		 'currentpage' => $this->batchPage,
		 'returnURL' => $this->returnURL,
		 'columncount' => count($this->columns),
	  );

	  // in order to allow OTHER things than batch to move us around, we do:
	  return $oTemplate->render($aTemplateData);
   }
}

/*
 * Secondary class:  AdvancedCollection
 *
 * Handles slightly more details of how the collections should work.  Ultimately, this should
 * replace DocumentCollection everywhere
 */


class AdvancedCollection {

   // handle the sorting, etc.
    var $_sFolderJoinClause = null;
    var $_aFolderJoinParams = null;
    var $_sFolderSortField = null;
    var $_sDocumentJoinClause = null;
    var $_aDocumentJoinParams = null;
    var $_sDocumentSortField = null;
    var $_queryObj = null;
    var $sort_column;
    var $sort_order;

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

    var $aOptions = array();
    var $bShowFolders = true;
    var $bShowDocuments = true;

    var $_gotData = false;
    var $_sorted = false;

    var $is_browse = false;

    var $empty_message;

    /* initialisation */
    function setOptions($aOptions) {
        $this->aOptions = $aOptions;

        // batching
        $this->batchPage = KTUtil::arrayGet($aOptions, 'batch_page', 0);
        $this->batchSize = KTUtil::arrayGet($aOptions, 'batch_size', 25);
        $this->batchStart =  $this->batchPage * $this->batchSize;

        // visibility
        $this->bShowFolders = KTUtil::arrayGet($aOptions, 'show_folders', true, false);
        $this->bShowDocuments = KTUtil::arrayGet($aOptions, 'show_documents', true, false);

        $this->is_browse = KTUtil::arrayGet($aOptions, 'is_browse', false);

        // sorting
        $this->sort_column = KTUtil::arrayGet($aOptions, 'sort_on', 'ktcore.columns.title');
        $this->sort_order = KTUtil::arrayGet($aOptions, 'sort_order', 'asc');

        // url options
        $sURL = KTUtil::arrayGet($aOptions, 'return_url', false);
        if($sURL === false) {
            $sURL = KTUtil::arrayGet($aOptions, 'result_url', $_SERVER['PHP_SELF']);
        }
        $this->returnURL = $sURL;

        $this->empty_message = KTUtil::arrayGet($aOptions, 'empty_message', _kt('No folders or documents in this location.'));
    }


    // we use a lot of standard variable names for these (esp. in columns.)
    // no need to replicate the code everywhere.
    function getEnvironOptions() {
        $aNewOptions = array();

        // batching
        $aNewOptions['batch_page'] = (int) KTUtil::arrayGet($_REQUEST, 'page', 0);

        // evil with cookies.
        $batch_size = KTUtil::arrayGet($_REQUEST, 'page_size');
        if (empty($batch_size)) {
            // try for a cookie
            $batch_size = KTUtil::arrayGet($_COOKIE, '__kt_batch_size', 25);
        } else {
            setcookie('__kt_batch_size', $batch_size);
        }
        $aNewOptions['batch_size'] = (int) $batch_size;

        // ordering. (direction and column)
        $aNewOptions['sort_on'] = KTUtil::arrayGet($_REQUEST, 'sort_on', 'ktcore.columns.title');
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', 'asc');
        if ($displayOrder !== 'asc') { $displayOrder = 'desc'; }
        $aNewOptions['sort_order'] = $displayOrder;

        // probably URL
        $aNewOptions['result_url'] = $_SERVER['PHP_SELF'];

        // return the environ options
        return $aNewOptions;
    }

    function setColumnOptions($sColumnNamespace, $aOptions) {
        foreach ($this->columns as $key => $oColumn) {
            if ($oColumn->namespace == $sColumnNamespace) {
                $this->columns[$key]->setOptions($aOptions);
            }
        }
    }

    function getColumnOptions($sColumnNamespace) {
        foreach ($this->columns as $key => $oColumn) {
            if ($oColumn->namespace == $sColumnNamespace) {
                return $this->columns[$key]->getOptions();
            }
        }
    }

    // columns should be added in the "correct" order (e.g. display order)
    function addColumn($oBrowseColumn) { array_push($this->columns, $oBrowseColumn); }
    function addColumns($aColumns) { $this->columns = kt_array_merge($this->columns, $aColumns); }
    function setQueryObject($oQueryObj) { $this->_queryObj = $oQueryObj; }

    /* fetch cycle */
    function setSorting() {

        $this->_sorted = true;

        // defaults
        $this->_sDocumentSortField = 'DM.name';
        $this->_sFolderSortField = 'F.name';

        foreach ($this->columns as $key => $oColumn) {
            if ($oColumn->namespace == $this->sort_column) {
                $this->columns[$key]->setSortedOn(true);
                $this->columns[$key]->setSortDirection($this->sort_order);

                // get the join params from the object.
                $aFQ = $this->columns[$key]->addToFolderQuery();
                $aDQ = $this->columns[$key]->addToDocumentQuery();

                $this->_sFolderJoinClause = $aFQ[0];
                $this->_aFolderJoinParams = $aFQ[1];

                if ($aFQ[2]) { $this->_sFolderSortField = $aFQ[2]; }
                $this->_sDocumentJoinClause = $aDQ[0];
                $this->_aDocumentJoinParams = $aDQ[1];

                if ($aDQ[2]) {
                    $this->_sDocumentSortField = $aDQ[2]; }
                } else {
        		    $oColumn->setSortedOn(false);
                }
        }
    }


    // finally, generate the results.  either (documents or folders) could be null/empty
    // FIXME handle column-for-sorting (esp. md?)
    function getResults() {

        if ($this->_gotInfo == true) {
            return;
        }

        // this impacts the query used.
        if (!$this->_sorted) {
            $this->setSorting();
        }

        // work out how many of each item type we're going to expect.
        if ($this->bShowFolders) {
            $this->folderCount = $this->_queryObj->getFolderCount();
            if (PEAR::isError($this->folderCount)) {
                $_SESSION['KTErrorMessage'][] = $this->folderCount->toString();
                $this->folderCount = 0;
            }
        } else {
            $this->folderCount = 0;
        }

        if ($this->bShowDocuments) {
            $this->documentCount = $this->_queryObj->getDocumentCount();
            if (PEAR::isError($this->documentCount)) {
                $_SESSION['KTErrorMessage'][] = $this->documentCount->toString();
                $this->documentCount = 0;
            }
        } else {
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
        if ($this->bShowDocuments) {
            $documents_to_get = $this->batchSize;
        } else {
            $documents_to_get = 0;
        }
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
            $this->batchStart -= $this->folderCount;
            $documentSet = $this->_queryObj->getDocuments($documents_to_get,
                    $this->batchStart,
                    $this->_sDocumentSortField,
                    $this->sort_order,
                    $this->_sDocumentJoinClause,
                    $this->_aDocumentJoinParams);
        } else {
            $folderSet = $this->_queryObj->getFolders($folders_to_get,
                $this->batchStart,
                $this->_sFolderSortField,
                $this->sort_order,
                $this->_sFolderJoinClause, //_sFolderJoinQuery
                $this->_aFolderJoinParams);

            // if we're getting -any- documents this round, then get some.
            if ($documents_to_get > 0) {
                $documentSet = $this->_queryObj->getDocuments($documents_to_get,
                    0,
                    $this->_sDocumentSortField,
                    $this->sort_order,
                    $this->_sDocumentJoinClause,
                    $this->_aDocumentJoinParams);
            }
        }

        if (PEAR::isError($folderSet)) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt('Failed to retrieve folders: %s'), $folderSet->getMessage());
            $folderSet = array();
            $this->folderCount = 0;
        }

        if (PEAR::isError($documentSet)) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt('Failed to retrieve documents: %s'), $documentSet->getMessage());
            //var_dump($documentSet); exit(0);
            $documentSet = array();
            $this->documentCount = 0;

        }

        $this->itemCount = $this->documentCount + $this->folderCount;

        $this->activeset = array(
            'folders' => $folderSet,
            'documents' => $documentSet,
        );

        $this->_gotInfo = true; // don't do this twice ...
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
        $row_info = array('docid' => $iDocumentId);
        $row_info['type'] = 'document';
        $row_info['document'] =& Document::get($iDocumentId);
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
        $row_info = array('folderid' => $iFolderId);
        $row_info['type'] = 'folder';
        $row_info['folder'] =& Folder::get($iFolderId);

        return $row_info;
    }

    // render a particular row.
    function renderRow($iDocumentId) { ; }

    // link url for a particular page.
    function pageLink($iPageNumber) {
        $qs = sprintf('page=%s&sort_on=%s&sort_order=%s', $iPageNumber, $this->sort_column, $this->sort_order);
        return KTUtil::addQueryString($this->returnURL, $qs);
    }

    function render() {
        $this->setSorting();
        $this->getResults();

        // ensure all columns use the correct url
        //var_dump($this->returnURL); exit(0);
        $aOpt = array('return_url' => $this->returnURL);
        foreach ($this->columns as $k => $v) {
            $this->columns[$k]->setOptions($aOpt);
        }

        // sort out the batch
        $pagecount = (int) floor($this->itemCount / $this->batchSize);
        if (($this->itemCount % $this->batchSize) != 0) {
            $pagecount += 1;
        }

	    // ick.
	    global $main;
	    $main->requireJSResource('resources/js/browsehelper.js');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/document_collection');
        $aTemplateData = array(
            'context' => $this,
            'pagecount' => $pagecount,
		    'currentpage' => $this->batchPage,
            'returnURL' => $this->returnURL,
            'columncount' => count($this->columns),
            'bIsBrowseCollection' => $this->is_browse,
            'batch_size' => $this->batchSize,
        );

        // in order to allow OTHER things than batch to move us around, we do:
        return $oTemplate->render($aTemplateData);
    }
}

class ExtCollection {

    var $columns;
    var $folders;
    var $documents;

    /**
     * Add the column headers
     *
     * @param array $aColumns
     */
    function addColumns($aColumns) {
        $this->columns = $aColumns;
    }

    /**
     * Add the folders under the folder
     *
     * @param array $aFolders
     */
    function addFolders($aFolders) {
        $this->folders = $aFolders;
    }

    /**
     * Add the documents contained in the folder
     *
     * @param array $aDocuments
     */
    function addDocuments($aDocuments) {
        $this->documents = $aDocuments;
    }

    function render() {

        global $main;
	    $main->requireJSResource('thirdpartyjs/extjs/adapter/ext/ext-base.js');
	    $main->requireJSResource('thirdpartyjs/extjs/ext-all.js');
	    $main->requireJSResource('resources/js/browse_ext.js');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/ext_collection');
        $aTemplateData = array(
        );

        // in order to allow OTHER things than batch to move us around, we do:
        return $oTemplate->render($aTemplateData);
    }
}

?>

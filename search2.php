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
 */

// TODO: do we have to serialise/unserialise the results. this is not optimal!!!

session_start();
require_once("config/dmsDefaults.php");
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');

require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");
require_once(KT_LIB_DIR . "/actions/bulkaction.php");

require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');

function search2queryCompare($a, $b)
{
	global $search2queryColumn, $search2queryOrder;


	if ($a->$search2queryColumn == $b->$search2queryColumn)
	{
		return 0;
	}

    // convert to lowercase for comparison, else sorting will put all lowercase before/after all uppercase
	$result = (strtolower($a->$search2queryColumn) < strtolower($b->$search2queryColumn)) ? -1 : 1;

	if ($search2queryOrder == 'asc')
		return $result;
	else
		return - $result;
}


/**
 * Assists with old browse search results
 *
 * @param unknown_type $sSortColumn
 * @param unknown_type $sSortOrder
 */

function search2QuerySort($sSortColumn, $sSortOrder)
{
	$defaultSortColumn = $_SESSION['search2_sort_column'];
	$defaultSortOrder = $_SESSION['search2_sort_order'];

	if (($defaultSortColumn == $sSortColumn) && ($defaultSortOrder == $sSortOrder))
	{
		return;
	}

	global $search2queryColumn, $search2queryOrder;

	$search2queryOrder = strtolower($sSortOrder);

	switch(strtolower($sSortColumn))
	{
		case 'ktcore.columns.title':
			$search2queryColumn = 'Title';
			break;
		case 'ktcore.columns.workflow_state':
			$search2queryColumn = 'WorkflowAndState';
			break;
		case 'ktcore.columns.checkedout_by':
			$search2queryColumn = 'CheckedOutBy';
			break;
		case 'ktcore.columns.creationdate':
			$search2queryColumn = 'DateCreated';
			break;
		case 'ktcore.columns.modificationdate':
			$search2queryColumn = 'DateModified';
			break;
		case 'ktcore.columns.creator':
			$search2queryColumn = 'CreatedBy';
			break;
		case 'ktcore.columns.docid':
			$search2queryColumn = 'DocumentID';
			break;
		case 'ktcore.columns.document_type':
			$search2queryColumn = 'DocumentType';
			break;
		default:
			return;
	}

	$results = unserialize($_SESSION['search2_results']);

    // Loop through to find and sort all possible result item types
    foreach($results as $key => $result)
    {
        // force re-initialisation of $sortresults on each iteration
        $sortresults = array();
        $sortresults = $result;
        // NOTE: usort may be sufficient here.
        // uasort was used because results were disappearing,
        // but this may have been related to not using the loop
        uasort($sortresults, 'search2queryCompare');
        $results[$key] = $sortresults;
    }

    $_SESSION['search2_results'] = serialize($results);
}

/**
 * Search2Query is used to provide allow the old browse search to work
 *
 */
class Search2Query extends PartialQuery
{
    function _count($type)
    {
        $count = 0;
        $results = unserialize($_SESSION['search2_results']);

        switch ($type)
        {
            case 'Document':
                return count($results['docs']) + count($results['shortdocs']);
            case 'Folder':
                return count($results['folders']) + count($results['shortfolders']);
            default:
                return 0;
        }
    }

    function getFolderCount()
    {
        return $this->_count('Folder');
    }
    function getDocumentCount()
    {
        return $this->_count('Document');
    }

    function getItems($type, $iStart, $iSize, $sSortColumn, $sSortOrder)
    {
        // TODO: quick hack. do this more optimally!!!!
        $results = unserialize($_SESSION['search2_results']);

        switch ($type)
        {
            case 'Document':
                $type = 'docs';
                break;
            case 'Folder':
                $type = 'folders';
                break;
        }

        $resultArray = $results[$type];
        foreach($results['short' . $type] as $rec)
        {
            $resultArray[] = $rec;
        }

        $resultArray = array_slice($resultArray, $iStart, $iSize);
        $results = array();
        foreach($resultArray as $rec)
        {
            $results[] = array('id'=>$rec->Id);
        }

        return $results;
    }

    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null)
  	{
  	    return $this->getItems('Folder', $iBatchStart, $iBatchSize, $sSortColumn, $sSortOrder);
  	}

    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null)
    {
  	    return $this->getItems('Document', $iBatchStart, $iBatchSize, $sSortColumn, $sSortOrder);
    }
}


class SearchDispatcher extends KTStandardDispatcher {

	private $curUserId;
	private $sysAdmin;
	private $savedSearchId;

	const RESULTS_PER_PAGE = 25;
	const MAX_PAGE_MOVEMENT = 10;

	public function __construct()
	{
		parent::KTStandardDispatcher();

		$this->curUserId = $_SESSION['userID'];

		$this->sysAdmin=Permission::userIsSystemAdministrator();

		if (array_key_exists('fSavedSearchId',$_GET))
		{
			$this->savedSearchId = sanitizeForSQL($_GET['fSavedSearchId']);
		}
	}

    function do_main()
    {
    	redirect(KTBrowseUtil::getBrowseBaseUrl());
    }

    /**
     * This proceses any given search expression.
     * On success, it redirects to the searchResults page.
     *
     * @param string $query
     */
    private function processQuery($query)
    {
    	try
    	{
     		$expr = parseExpression($query);

    		// bit of a hack
    		// check for the isDeleted and isArchived keywords affecting status in the query
    		if(strpos($query, 'IsDeleted') !== false || strpos($query, 'IsArchived') !== false){
    		    $expr->setIncludeStatus(false);
    		}

    		$results = $expr->evaluate();
    		$results = resolveSearchShortcuts($results);

    		usort($results['docs'], 'rank_compare');

    		$_SESSION['search2_results'] = serialize($results);
    		$_SESSION['search2_query'] = $query;
    		$_SESSION['search2_sort'] = 'rank';

    		$this->redirectTo('searchResults');
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('guiBuilder', _kt('Could not process query.') . $e->getMessage());
    	}
    }

    function do_refreshLuceneStats()
    {
    	$indexer = Indexer::get();
    	$indexer->updateIndexStats();

    	redirect(KTUtil::kt_url().'/dashboard.php');
    }

    function do_refreshDashboardStatus()
    {
    	session_unregister('ExternalResourceStatus');
    	session_unregister('IndexingStatus');
    	redirect(KTUtil::kt_url().'/dashboard.php');
    }

    function do_refresh(){
        // Get query from session
        $query = $_SESSION['search2_query'];

        $this->processQuery($query);
        $this->redirectTo('searchResults');
    }

    /**
     * Processes a query sent by HTTP POST in searchQuery.
     *
     */
    function do_process()
    {
    	if (empty($_REQUEST['txtQuery']))
    	{
    		$this->errorRedirectTo('searchResults', _kt('Please reattempt the query. The query is missing.'));
    	}
    	$query = $_REQUEST['txtQuery'];

    	// Strip out returns - they cause a js error [unterminated string literal]
    	$query = str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), $query);
    	$query = strip_tags($query);

    	$_SESSION['search2_quick'] = 0;
    	$_SESSION['search2_general'] = 0;
    	if (isset($_REQUEST['cbQuickQuery']) && $_REQUEST['cbQuickQuery'] +0 == 1)
    	{
    		$_SESSION['search2_quick'] = 1;
    		if (stripos($query, 'generaltext') !== false || stripos($query, 'metadata') !== false)
    		{
    			preg_match('/["][^"]*["]/', $query, $out);
    			$_SESSION['search2_quickQuery'] = substr($out[0],1,-1);
    		}
    	}
    	else
    	{
			$_SESSION['search2_quickQuery'] = '';
    	}
    	if (isset($_REQUEST['cbQuickGeneral']) && $_REQUEST['cbQuickGeneral'] +0 == 1)
    	{
    		$_SESSION['search2_general'] = 1;
    	}

		session_unregister('search2_savedid');

    	$this->processQuery($query);
    }

    /**
     * Returns the saved query is resolved from HTTP GET fSavedSearchId field.
     *
     * @return mixed False if error, else string.
     */
    private function getSavedExpression()
    {
    	if (is_null($this->savedSearchId))
		{
			$this->errorRedirectToParent(_kt('The saved search id was not passed correctly.'));
		}
		$_SESSION['search2_savedid'] = $this->savedSearchId;

		$sql = "SELECT name, expression FROM search_saved WHERE type='S' AND id=$this->savedSearchId";
		if (!$this->sysAdmin)
		{
			$sql .= "  and ( user_id=$this->curUserId OR shared=1 ) ";
		}

		$query = DBUtil::getOneResult($sql);
		if (PEAR::isError($query))
		{
			$this->errorRedirectToParent(_kt('The saved search could not be resolved.'));
		}

		$_SESSION['search2_savedname'] = $query['name'];
		return array($query['name'],$query['expression']);
    }

    /**
     * Processes a saved query HTTP GET fSavedSearchId
     *
     */
    function do_processSaved()
    {
    	list($name, $expr) = $this->getSavedExpression();

		$this->processQuery($expr);
    }

	function do_oldSearchResults()
	{
        // call the results sorting function in case of sort options selected
        search2QuerySort(stripslashes($_GET['sort_on']), stripslashes($_GET['sort_order']));

        $this->oPage->setBreadcrumbDetails(_kt("Search Results"));
        $this->oPage->title = _kt("Search Results");

        $collection = new AdvancedCollection;
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = $oColumnRegistry->getColumnsForView('ktcore.views.search');
        $collection->addColumns($aColumns);

        // set a view option
        $aTitleOptions = array(
            'documenturl' => $GLOBALS['KTRootUrl'] . '/view.php',
            'direct_folder' => true,
        );
        $collection->setColumnOptions('ktcore.columns.title', $aTitleOptions);

        // set the selection options
        $collection->setColumnOptions('ktcore.columns.selection', array(
            'rangename' => 'selection',
            'show_folders' => true,
            'show_documents' => true,
        ));


        $aOptions = $collection->getEnvironOptions(); // extract data from the environment

        $aOptions['empty_message'] = _kt("No documents or folders match this query.");
        $aOptions['is_browse'] = true;
		$aOptions['return_url'] = KTUtil::addQueryStringSelf("action=oldSearchResults");


        $collection->setOptions($aOptions);
        $collection->setQueryObject(new Search2Query());

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
            "context" => $this,
            "collection" => $collection,
            'isEditable' => true,
            'bulkactions' => KTBulkActionUtil::getAllBulkActions(),
            'browseutil' => new KTBrowseUtil(),
            'returnaction' => 'search2',
        );
        return $oTemplate->render($aTemplateData);
        }



    /**
     * Renders the search results.
     *
     * @return string
     */
    function do_searchResults()
    {
        if (array_key_exists('format', $_GET))
        {
            switch ($_GET['format']){
                case 'searchengine':
                    $_SESSION['search2resultFormat'] = 'searchengine';
                    break;
                case 'browseview':
                    $_SESSION['search2resultFormat'] = 'browseview';
                    break;
            }
        }
        else
        {
            if(!array_key_exists('search2resultFormat', $_SESSION)){
                global $default;
                $_SESSION['search2resultFormat'] = $default->resultsDisplayFormat;
            }
        }

        if ($_SESSION['search2resultFormat'] == 'browseview')
        {
        	$this->redirectTo('oldSearchResults');

        }

        $this->oPage->setBreadcrumbDetails(_kt("Search Results"));
        $this->oPage->title = _kt("Search Results");

    	$oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/search2/search_results");

       KTEntityUtil::_proxyCreate('KTDocumentContentVersion','KTDocumentContentVersionProxy');
       KTEntityUtil::_proxyCreate('KTDocumentCore','KTDocumentCoreProxy');
       KTEntityUtil::_proxyCreate('KTDocumentMetadataVersion','KTDocumentMetadataVersionProxy');

        $results = unserialize($_SESSION['search2_results']);

        // NOTE: sorting results (when it is implemented) might have to be done per section, as it is done with the browse view

        // Get the order of display - folders first / documents first

		// Get the display order of the results - documents / folders first
		$_SESSION['display_order'] = isset($_POST['display_order']) ? $_POST['display_order'] : $_SESSION['display_order'];

        $display_order = $_SESSION['display_order'];
        $selected_order = array('f' => '', 'd' => '', 's' => '');

        switch ($display_order){
            case 's':
                $selected_order['s'] = 'selected';
                $resultArray = $results['shortfolders'];
                foreach($results['shortdocs'] as $f) $resultArray[] = $f;
                foreach($results['folders'] as $d) $resultArray[] = $d;
                foreach($results['docs'] as $f) $resultArray[] = $f;
                break;

            case 'd':
                $selected_order['d'] = 'selected';
                $resultArray = $results['docs'];
                foreach($results['folders'] as $f) $resultArray[] = $f;
                foreach($results['shortdocs'] as $d) $resultArray[] = $d;
                foreach($results['shortfolders'] as $f) $resultArray[] = $f;
                break;

            case 'f':
            default:
                $selected_order['f'] = 'selected';
                $resultArray = $results['folders'];
                foreach($results['docs'] as $f) $resultArray[] = $f;
                foreach($results['shortfolders'] as $d) $resultArray[] = $d;
                foreach($results['shortdocs'] as $f) $resultArray[] = $f;
        }

        $results = $resultArray;

        if (!is_array($results)  || count($results) == 0)
        {
        	$results=array();
        }

        $numRecs = count($results);
        $showall = $_GET['showAll'];
		if (is_numeric($showall))
		{
			$showall = ($showall+0) > 0;
		}
		else
		{
			$showall = ($showall == 'true');
		}
		$config = KTConfig::getSingleton();
		$resultsPerPage = ($showall)?$numRecs:$config->get('search/resultsPerPage', SearchDispatcher::RESULTS_PER_PAGE);

        $maxPageMove = SearchDispatcher::MAX_PAGE_MOVEMENT;

        $pageOffset = 1;
        if (isset($_GET['pageOffset']))
        {
        	$pageOffset = $_GET['pageOffset'];
        }

        $maxPages = ceil($numRecs / $resultsPerPage) ;
        if ($pageOffset <= 0 || $pageOffset > $maxPages)
        {
        	$pageOffset = 1;
        }

         $firstRec = ($pageOffset-1) * $resultsPerPage;
         $lastRec = $firstRec + $resultsPerPage;
         if ($lastRec > $numRecs)
         {
         	$lastRec = $numRecs;
         }

        $display = array_slice($results,$firstRec ,$resultsPerPage);

        $startOffset = $pageOffset - $maxPageMove;
        if ($startOffset < 1)
        {
        	$startOffset = 1;
        }
        $endOffset = $pageOffset + $maxPageMove;
        if ($endOffset > $maxPages)
        {
        	$endOffset = $maxPages;
        }

		$pageMovement = array();
		for($i=$startOffset;$i<=$endOffset;$i++)
		{
			$pageMovement[] = $i;
		}

		 $aBulkActions = KTBulkActionUtil::getAllBulkActions();

        $aTemplateData = array(
              "context" => $this,
              'selected_order' => $selected_order,
              'bulkactions'=>$aBulkActions,
              'firstRec'=>$firstRec,
              'lastRec'=>$lastRec,
              'showAll'=>$showall,
              'numResults' => count($results),
              'pageOffset' => $pageOffset,
              'resultsPerPage'=>$resultsPerPage,
              'maxPages' => $maxPages,
              'results' => $display,
              'pageMovement'=>$pageMovement,
              'startMovement'=>$startOffset,
              'endMovement'=>$endOffset,
              'txtQuery' => $_SESSION['search2_query'],
              'iSavedID' => $_SESSION['search2_savedid'],
              'txtSavedName' => $_SESSION['search2_savedname']
        );

        return $oTemplate->render($aTemplateData);
    }

	function do_manage()
	{
		$this->oPage->setBreadcrumbDetails(_kt("Manage Saved Searches"));
        $this->oPage->title = _kt("Manage Saved Searches");

		$sql = "SELECT ss.id, ss.name, u.name as username, user_id is not null as editable, shared
				FROM search_saved ss
				LEFT OUTER JOIN users u on ss.user_id = u.id
				WHERE ss.type='S' ";

		if (!$this->sysAdmin)
		{
			$sql .= " AND (ss.user_id=$this->curUserId OR ss.shared=1)";
		}

		$saved = DBUtil::getResultArray($sql);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/search2/manage_saved_search");
        $aTemplateData = array(
              "context" => $this,
              'saved'=>$saved,
              'sysadmin'=>$this->sysAdmin
        );

        return $oTemplate->render($aTemplateData);
	}

	function do_share()
	{
		if (is_null($this->savedSearchId))
		{
			$this->errorRedirectTo('manage', _kt('The saved search id was not passed correctly.'));
		}
		if (!array_key_exists('share',$_GET))
		{
			$this->errorRedirectTo('manage', _kt('The sharing option was not passed correctly.'));
		}

		if ($_GET['share']=='no')
		{
			$share=0;
			$msg = _kt("The saved search can only be seen by you.");
		}
		else
		{
			$share=1;
			$msg = _kt("The saved search is now visible to all users.");
		}


		$sql = "UPDATE search_saved SET shared=$share WHERE type='S' AND id=$this->savedSearchId";
		if (!$this->sysAdmin)
		{
			$sql .= " AND ss.user_id=$this->curUserId";
		}

		DBUtil::runQuery($sql);
		$this->successRedirectTo('manage', $msg);

	}

	function do_delete()
	{
		if (is_null($this->savedSearchId))
		{
			$this->errorRedirectTo('manage', _kt('The saved search id was not passed correctly.'));
		}

		$sql = "DELETE FROM search_saved WHERE type='S' AND id=$this->savedSearchId";
		if (!$this->sysAdmin)
		{
			$sql .= " AND user_id=$this->curUserId ";
		}

		$res = DBUtil::runQuery($sql);

        if (DBUtil::affectedRows( ) == 0)
        {
            $message = '';
            // in case of database error, supply actual error as message
            if (PEAR::isError($res))
            {
                $message = $res->getMessage();
            }

            if (!$this->sysAdmin)
            {
                if ($message == '') // generic failure message
                {
                    $message = 'You do not have permission to delete this search.';
                }
            }
            else
            {
                if ($message == '') // generic failure message
                {
                    $message = 'The saved search could not be deleted.';
                }
            }

            $this->errorRedirectTo('manage', sprintf(_kt('%s' , $message)));
        }

        $this->successRedirectTo('manage', _kt('The saved search was deleted successfully.'));

	}

	function do_guiBuilder()
	{
		$this->oPage->setBreadcrumbDetails(_kt("Advanced Search"));
        $this->oPage->title = _kt("Advanced Search");

		$result = array();

		// TODO: need to escape the parameters

		$result['fieldsets'] = SearchHelper::getFieldsets();
		$result['fieldset_str'] = SearchHelper::getJSfieldsetStruct($result['fieldsets']);

		$result['workflows'] = SearchHelper::getWorkflows();
        $result['workflow_str'] = SearchHelper::getJSworkflowStruct($result['workflows']);

		$result['fields'] = SearchHelper::getSearchFields();
		$result['fields_str'] = SearchHelper::getJSfieldsStruct($result['fields']);

		$result['users_str'] = SearchHelper::getJSusersStruct();
		$result['mimetypes_str'] = SearchHelper::getJSmimeTypesStruct();
		$result['documenttypes_str'] = SearchHelper::getJSdocumentTypesStruct();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/search2/adv_query_builder");
        $aTemplateData = array(
              "context" => $this,
              'metainfo'=> $result
        );

        return $oTemplate->render($aTemplateData);
	}

	function do_queryBuilder()
	{
		$this->oPage->setBreadcrumbDetails(_kt("Query Editor"));
        $this->oPage->title = _kt("Query Editor");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/search2/adv_query_search");


        $registry = ExprFieldRegistry::getRegistry();
        $aliases = $registry->getAliasNames();
        sort($aliases);

        $edit = is_numeric($this->savedSearchId);
        $name = '';
        if ($edit)
        {
			list($name, $expr) = $this->getSavedExpression();
        }
        else
        {
        	$expr = $_SESSION['search2_query'];
        }

        $aTemplateData = array(
              "context" => $this,
              'aliases' => $aliases,
              'bSave'=>$edit,
              'edtSaveQueryName'=>$name,
              'txtQuery'=>$expr,
              'iSavedSearchId'=>$this->savedSearchId

        );
        return $oTemplate->render($aTemplateData);
	}
}

$oDispatcher = new SearchDispatcher();
$oDispatcher->dispatch();

?>

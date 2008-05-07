<?php

session_start();
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");
require_once(KT_LIB_DIR . "/actions/bulkaction.php");
require_once(KT_DIR . '/search2/search/search.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');

function search2queryCompare($a, $b)
{
	global $search2queryColumn, $search2queryOrder;


	if ($a->$search2queryColumn == $b->$search2queryColumn)
	{
		return 0;
	}

	$result = ($a->$search2queryColumn < $b->$search2queryColumn)?-1:1;

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

	usort($results, 'search2queryCompare');

	$_SESSION['search2_results'] = serialize($results);

}

/**
 * Search2Query is used to provide allow the old browse search to work
 *
 */
class Search2Query extends PartialQuery
{
    function getFolderCount() { return 0; }
    function getDocumentCount()
    {
        $results = $_SESSION['search2_results'];
        if(isset($results) && !empty($results)){
            return count(unserialize($results));
        }
    	return 0;
    }

    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null)
  	{
  		return array();
  	}

    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null)
    {
    	search2QuerySort($_GET['sort_on'], $_GET['sort_order']);
    	$results = unserialize($_SESSION['search2_results']);

    	$batch = array();

    	$no_results = count($results);
    	for($i=0;$i<$no_results;$i++)
    	{
			if ($i < $iBatchStart) continue;
			if ($i > $iBatchStart + $iBatchSize) continue;

			$batch[] = array('id'=>$results[$i]->DocumentID);
    	}

    	return $batch;
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

    		$result = $expr->evaluate();
    		usort($result, 'rank_compare');

    		$_SESSION['search2_results'] = serialize($result);
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
    	session_unregister('LuceneStats');
    	redirect(KTUtil::kt_url().'/dashboard.php');
    }

    function do_refreshDashboardStatus()
    {
    	session_unregister('ExternalResourceStatus');
    	session_unregister('IndexingStatus');
    	redirect(KTUtil::kt_url().'/dashboard.php');
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


		DBUtil::runQuery($sql);
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
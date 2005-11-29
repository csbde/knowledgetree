<?php

// boilerplate includes
require_once("../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/visualpatterns/PatternBrowsableSearchResults.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');

class BooleanSearchDispatcher extends KTStandardDispatcher {
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
    );
    var $sSection = "browse";

   function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => "Boolean search");
        $this->oPage->setBreadcrumbDetails('defining search');
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "aCriteria" => $aCriteria,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_performSearch() {
        // TODO first extract environ vars
        // TODO second create criterion objects (see getAdvancedSearchResults for this.
        // TODO third get each one to generate the SQL snippet. (ENSURE that they are wrapped in '('..')' )
        // TODO fourth array().join(' AND ') where appropriate
        // TODO finally return via PatternBrowseableSearchResults (urgh.)
        
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        $boolean_search_id = KTUtil::arrayGet($_REQUEST, 'boolean_search_id');
        if ($boolean_search_id) {
            $datavars = $_SESSION['boolean_search'][$boolean_search_id];
        }
        $iSavedSearchId = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        if (!empty($iSavedSearchId)) {
            $oSearch = KTSavedSearch::get($iSavedSearchId);
            $datavars = $oSearch->getSearch();
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain('You need to have at least 1 condition.');
        }

        $res = $this->handleCriteriaSet($datavars, KTUtil::arrayGet($_REQUEST, 'fStartIndex', 1));
        
        return $res;
    }
    
    function handleCriteriaSet($aCriteriaSet, $iStartIndex) {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => "Boolean search");
        $this->oPage->setBreadcrumbDetails('searching');
        $collection = new DocumentCollection;
        $this->browseType = "Folder";

        $collection->addColumn(new SelectionColumn("Browse Selection","selection"));
        $t =& new TitleColumn("Test 1 (title)","title");
        $t->setOptions(array('documenturl' => '../documentmanagement/view.php'));
        $collection->addColumn($t);
        $collection->addColumn(new DateColumn("Created","created", "getCreatedDateTime"));
        $collection->addColumn(new DateColumn("Last Modified","modified", "getLastModifiedDate"));
        $collection->addColumn(new DateColumn("Last Modified","modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn('Creator','creator_id','getCreatorID'));

        $searchable_text = KTUtil::arrayGet($_REQUEST, "fSearchableText");

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $sSearch = md5(serialize($aCriteriaSet));
        $_SESSION['boolean_search'][$sSearch] = $aCriteriaSet;
        $resultURL = "?action=performSearch&boolean_search_id=" . urlencode($sSearch);
        $collection->setBatching($resultURL, $batchPage, $batchSize);


        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        // add in the query object.
        $qObj = new BooleanSearchQuery($aCriteriaSet);
        $collection->setQueryObject($qObj);

        $collection->getResults();
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
              "context" => $this,
              "collection" => $collection,
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oDispatcher = new BooleanSearchDispatcher();
$oDispatcher->dispatch();

?>

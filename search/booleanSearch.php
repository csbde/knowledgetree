<?php

// boilerplate includes
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");
require_once(KT_LIB_DIR . "/search/searchutil.inc.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

class BooleanSearchDispatcher extends KTStandardDispatcher {
    var $sSection = "browse";

    function BooleanSearchDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
        );
        return parent::KTStandardDispatcher();
    }

   function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _("Advanced Search"));
        $this->oPage->setBreadcrumbDetails(_('defining search'));
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "context" => &$this,
            "aCriteria" => $aCriteria,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_performSearch() {
        $title = null;
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
            $title = $oSearch->getName();
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_('You need to have at least 1 condition.'));
        }
        
        $res = $this->handleCriteriaSet($datavars, KTUtil::arrayGet($_REQUEST, 'fStartIndex', 1), $title);
        
        return $res;
    }
    
    function handleCriteriaSet($aCriteriaSet, $iStartIndex, $sTitle=null) {
        
        if ($sTitle == null) {
            $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Advanced Search'));
            $sTitle =  _('Search Results');
        } else {
           $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Saved Search'));
            $this->oPage->setTitle(_('Saved Search: ') . $sTitle);
        }
        $this->oPage->setBreadcrumbDetails($sTitle);
        
        $collection = new DocumentCollection;
        $this->browseType = "Folder";

        $collection->addColumn(new SelectionColumn("Browse Selection","selection"));
        $t =& new TitleColumn("Test 1 (title)","title");
        $t->setOptions(array('documenturl' => $GLOBALS['KTRootUrl'] . '/view.php'));
        $collection->addColumn($t);
        $collection->addColumn(new DateColumn(_("Created"),"created", "getCreatedDateTime"));
        $collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn(_('Creator'),'creator_id','getCreatorID'));

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
              "custom_title" => $sTitle,
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oDispatcher = new BooleanSearchDispatcher();
$oDispatcher->dispatch();

?>

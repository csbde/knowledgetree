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

// specific includes

$sectionName = "General";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class BooleanSearchDispatcher extends KTStandardDispatcher {
   function do_main() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "aCriteria" => $aCriteria,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
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
        $aQuery = KTSearchUtil::criteriaToQuery($aCriteriaSet, $_SESSION['userID'], 'ktcore.permissions.read');
		$aColumns = array("folder_name", "file_name", "document_name", "doc_count", "view");
		$aColumnTypes = array(3,3,3,1,3);
		$aColumnHeaders = array("<font color=\"ffffff\"><img src=$default->graphicsUrl/widgets/dfolder.gif>" . _("Folder") . "</font>", "<font color=\"ffffff\">" . _("Name") . "</font>", "<font color=\"ffffff\">" . _("Title") . "</font>", "<font color=\"ffffff\">" . _("Matches") . "</font>", "<font color=\"ffffff\">" . _("View") . "</font>");
		$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument", "$default->rootUrl/control.php?action=viewDocument", null, "$default->rootUrl/control.php?action=downloadDocument");
		$aDBQueryStringColumns = array("document_id","folder_id");
		$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
		$oPatternBrowse = & new PatternBrowseableSearchResults($aQuery, 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
		$oPatternBrowse->setStartIndex($iStartIndex);
		$oPatternBrowse->setSearchText("");
        $sFormStart = '<form method="POST" name="MainForm">';

		$sFormEnd = '<input type="hidden" name="boolean_search" value="'. htmlentities(serialize($aCriteriaSet)) . '" />';
		$sFormEnd .= '<input type="hidden" name="action" value="performSearch" />';

		return renderHeading(_("Advanced Search")) . $sFormStart . $oPatternBrowse->render() . $sFormEnd . $sRefreshMessage;
    }
}

$oDispatcher = new BooleanSearchDispatcher();
$oDispatcher->dispatch();

?>
